<?php

namespace App\Jobs;

use App\Imports\StudentsImport;
use App\Models\ClassModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Memproses file import siswa di latar belakang.
 *
 * Dipisah dari request HTTP karena hashing bcrypt (BCRYPT_ROUNDS=12) per baris
 * sangat lambat (~200-300ms/baris). Untuk ratusan siswa, request sinkron akan
 * menembus timeout nginx (504). Job ini menjalankannya di worker sehingga
 * tidak ada batas timeout HTTP.
 *
 * PENTING (race/idempotency):
 *  - tries = 1: import sebagian + retry = laporan "NIS sudah terdaftar" yang
 *    membingungkan, jadi kita tidak mengulang otomatis.
 *  - WithoutOverlapping(importId): cegah job yang sama diproses dua worker
 *    sekaligus bila durasi melewati `retry_after` queue.
 *  - Pastikan `REDIS_QUEUE_RETRY_AFTER` (.env) > $timeout, jika tidak job
 *    panjang akan dianggap basi dan dipungut ulang → insert dobel.
 */
class ProcessStudentImport implements ShouldQueue
{
    use Queueable;

    /** Jangan retry: import sebagian lalu diulang menghasilkan laporan duplikat. */
    public int $tries = 1;

    /** Batas waktu eksekusi job (detik). ~1200s / 0.25s per bcrypt ≈ 4800 baris. */
    public int $timeout = 1200;

    public function __construct(
        public string $importId,
        public string $storedPath,        // path relatif terhadap disk 'local'
        public bool $autoCreate,
        public array $classesToCreate,    // ['10|RPL', '11|TKJ', ...]
        public ?int $adminId = null,      // pemilik import (object-level authz saat polling)
    ) {}

    public function middleware(): array
    {
        // Kunci per-importId; jika job kembar muncul (retry_after lebih kecil dari
        // durasi), yang kedua langsung dibuang alih-alih diproses ulang.
        return [(new WithoutOverlapping($this->importId))->dontRelease()];
    }

    private function cacheKey(): string
    {
        return "student_import:{$this->importId}";
    }

    private function putStatus(array $patch): void
    {
        $existing = Cache::get($this->cacheKey(), []);
        Cache::put($this->cacheKey(), array_merge($existing, $patch), now()->addHour());
    }

    public function handle(): void
    {
        $this->putStatus(['status' => 'processing', 'started_at' => now()->toIso8601String()]);
        
        $absPath = $this->storedPath; // Sudah path absolut penuh dari controller

        try {
            // Buat kelas yang dicentang user secara eksplisit (idempoten via firstOrCreate)
            $preCreatedLabels = [];
            foreach ($this->classesToCreate as $key) {
                $parts   = explode('|', (string) $key, 2);
                $tingkat = (int) ($parts[0] ?? 0);
                $jurusan = strtoupper(trim($parts[1] ?? ''));
                if ($tingkat) {
                    $cls = ClassModel::firstOrCreate(['class' => $tingkat, 'major' => $jurusan ?: null]);
                    if ($cls->wasRecentlyCreated) {
                        $label = "{$tingkat}" . ($jurusan ? " - {$jurusan}" : '');
                        $preCreatedLabels[$label] = $cls->id;
                    }
                }
            }

            $import = new StudentsImport($this->autoCreate);
            foreach ($this->loadSheetsAsCollections($absPath) as $sheet) {
                $import->importSheet($sheet['rows'], $sheet['name']);
            }

            $count          = $import->getImportedCount();
            $failed         = array_values($import->getFailedRows());
            $createdClasses = array_merge($preCreatedLabels, $import->getCreatedClasses());

            if ($count > 0) {
                Cache::forget('dashboard.total_siswa');
            }

            $this->putStatus([
                'status'          => 'completed',
                'count'           => $count,
                'failed'          => $failed,
                'created_classes' => $createdClasses,
                'finished_at'     => now()->toIso8601String(),
            ]);

            Log::info('[StudentImport] selesai', [
                'import_id' => $this->importId,
                'imported'  => $count,
                'failed'    => count($failed),
            ]);
        } catch (\Throwable $e) {
            // Catat detail untuk ops; pesan ke user tetap generik (tanpa internal).
            Log::error('[StudentImport] gagal: ' . $e->getMessage(), [
                'import_id' => $this->importId,
            ]);
            $this->putStatus([
                'status'      => 'failed',
                'message'     => 'Gagal memproses file. Pastikan format file sesuai template, lalu coba lagi.',
                'finished_at' => now()->toIso8601String(),
            ]);
            throw $e; // biarkan tercatat di failed_jobs untuk observability
        } finally {
            // Hapus file upload apa pun hasilnya (sukses/gagal) — jangan tinggalkan PII di disk.
            @unlink($this->storedPath);
        }
    }

    /**
     * Baca semua sheet dari file Excel menggunakan PhpSpreadsheet.
     * Mengembalikan array of ['name' => string, 'rows' => Collection].
     * Setiap baris adalah Collection dengan kunci nama kolom (lowercase).
     */
    private function loadSheetsAsCollections(string $filePath): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $result      = [];

        foreach ($spreadsheet->getAllSheets() as $worksheet) {
            $data = $worksheet->toArray(null, false, false, false);
            if (empty($data)) {
                continue;
            }

            $headerIndex = -1;
            $headers = [];

            for ($i = 0; $i < min(25, count($data)); $i++) {
                $row = array_map(fn($h) => strtolower(trim((string) ($h ?? ''))), $data[$i]);
                if (in_array('nis', $row) && in_array('nama', $row)) {
                    $headerIndex = $i;
                    $headers = $row;
                    break;
                }
            }

            if ($headerIndex === -1) {
                $headerIndex = 0;
                $headers = array_map(fn($h) => strtolower(trim((string) ($h ?? ''))), $data[0]);
            }

            if (array_filter($headers) === []) {
                continue;
            }

            $rows = collect(array_slice($data, $headerIndex + 1))->map(function ($rowData) use ($headers) {
                $padded = array_pad((array) $rowData, count($headers), null);
                return collect(array_combine($headers, array_slice($padded, 0, count($headers))));
            });

            $result[] = ['name' => $worksheet->getTitle(), 'rows' => $rows];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $result;
    }
}
