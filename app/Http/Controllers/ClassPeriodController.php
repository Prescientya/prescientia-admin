<?php

namespace App\Http\Controllers;

use App\Exports\ClassPeriodTemplateExport;
use App\Models\ClassPeriod;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ClassPeriodController extends Controller
{
    /* ── INDEX ─────────────────────────────────────────── */

    public function index()
    {
        $days = ClassPeriod::DAYS;
        $grouped = [];
        foreach ($days as $day) {
            $grouped[$day] = ClassPeriod::forDay($day)->get();
        }
        return view('Jam_Pelajaran.index', compact('grouped'));
    }

    /* ── STORE ─────────────────────────────────────────── */

    public function store(Request $request)
    {
        $data = $this->validatePeriod($request);

        try {
            // Auto-calculate duration
            $data['duration_minutes'] = $this->calcDuration($data['start_time'], $data['end_time']);

            // Shift sequences down to make room for insertion
            $this->shiftSequences($data['day'], $data['sequence'], 1);

            ClassPeriod::create($data);

            $successMessage = 'Jam pelajaran berhasil ditambahkan.';
            if ($request->expectsJson()) {
                return $this->jsonDayResponse($data['day'], $successMessage);
            }
            return back()->with('success', $successMessage);
        } catch (Throwable $e) {
            [$message, $status] = $this->resolvePeriodFailureReason($e, 'menambahkan');
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], $status);
            }
            return back()->withInput()->with('error', $message);
        }
    }

    /* ── UPDATE ─────────────────────────────────────────── */

    public function update(Request $request, ClassPeriod $jamPelajaran)
    {
        $data = $this->validatePeriod($request, $jamPelajaran->id);

        try {
            $data['duration_minutes'] = $this->calcDuration($data['start_time'], $data['end_time']);

            $jamPelajaran->update($data);

            $successMessage = 'Jam pelajaran berhasil diperbarui.';
            if ($request->expectsJson()) {
                return $this->jsonDayResponse($data['day'], $successMessage);
            }
            return back()->with('success', $successMessage);
        } catch (Throwable $e) {
            [$message, $status] = $this->resolvePeriodFailureReason($e, 'memperbarui');
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], $status);
            }
            return back()->withInput()->with('error', $message);
        }
    }

    /* ── DESTROY ────────────────────────────────────────── */

    public function destroy(Request $request, ClassPeriod $jamPelajaran)
    {
        try {
            $day = $jamPelajaran->day;
            $jamPelajaran->delete();

            // Re-sequence remaining rows
            $this->resequence($day);

            $successMessage = 'Jam pelajaran berhasil dihapus.';
            if ($request->expectsJson()) {
                return $this->jsonDayResponse($day, $successMessage);
            }
            return back()->with('success', $successMessage);
        } catch (Throwable $e) {
            [$message, $status] = $this->resolvePeriodFailureReason($e, 'menghapus');
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], $status);
            }
            return back()->with('error', $message);
        }
    }

    /* ── DOWNLOAD TEMPLATE ──────────────────────────────── */

    public function downloadTemplate()
    {
        return Excel::download(new ClassPeriodTemplateExport, 'template_jam_pelajaran.xlsx');
    }

    /* ── IMPORT EXCEL ────────────────────────────────────── */

    public function importExcel(Request $request)
    {
        set_time_limit(0);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            // Parse the uploaded file with heading row support
            $sheets = Excel::toCollection(
                new class implements WithHeadingRow {},
                $request->file('file')
            );
            $rows = $sheets->first();

            $validDays   = ClassPeriod::DAYS;
            $validTypes  = array_keys(ClassPeriod::ACTIVITY_TYPES);
            // Map lowercase Indonesian label → type key
            $labelToType = array_flip(array_map('strtolower', ClassPeriod::ACTIVITY_TYPES));

            $grouped   = [];  // day => [ DB rows ]
            $errors    = [];
            $rowNumber = 2;   // spreadsheet row (row 1 = heading)

            foreach ($rows as $row) {
                $row = $row->toArray();

                // Skip empty / info comment rows
                $hari = strtolower(trim((string)($row['hari'] ?? '')));
                if (empty($hari) || str_starts_with($hari, '[info]')) {
                    $rowNumber++;
                    continue;
                }

                if (!in_array($hari, $validDays)) {
                    $errors[] = "Baris {$rowNumber}: hari \u2018{$hari}\u2019 tidak valid.";
                    $rowNumber++;
                    continue;
                }

                $jamKeRaw = $row['jam_ke'] ?? '';
                // Allow empty/null jam_ke → treat as 0 (for ceremony / break / etc.)
                if ($jamKeRaw === '' || $jamKeRaw === null) {
                    $jamKe = 0;
                } elseif (is_numeric($jamKeRaw) && (int)$jamKeRaw >= 0 && (int)$jamKeRaw <= 30) {
                    $jamKe = (int)$jamKeRaw;
                } else {
                    $errors[] = "Baris {$rowNumber}: jam_ke \u2018{$jamKeRaw}\u2019 tidak valid (angka 0\u201330 atau kosong).";
                    $rowNumber++;
                    continue;
                }

                $waktuMulai   = trim((string)($row['waktu_mulai']   ?? ''));
                $waktuSelesai = trim((string)($row['waktu_selesai'] ?? ''));

                // Excel may store times as floats (fraction of day) — convert if needed
                if (is_numeric($waktuMulai)) {
                    $waktuMulai = $this->excelTimeToHHMM((float)$waktuMulai);
                }
                if (is_numeric($waktuSelesai)) {
                    $waktuSelesai = $this->excelTimeToHHMM((float)$waktuSelesai);
                }

                // Also handle HH:MM:SS format from Maatwebsite
                if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $waktuMulai)) {
                    $waktuMulai = substr($waktuMulai, 0, 5);
                }
                if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $waktuSelesai)) {
                    $waktuSelesai = substr($waktuSelesai, 0, 5);
                }

                if (!preg_match('/^\d{2}:\d{2}$/', $waktuMulai)) {
                    $errors[] = "Baris {$rowNumber}: waktu_mulai \u2018{$waktuMulai}\u2019 tidak valid (format HH:MM).";
                    $rowNumber++;
                    continue;
                }
                if (!preg_match('/^\d{2}:\d{2}$/', $waktuSelesai)) {
                    $errors[] = "Baris {$rowNumber}: waktu_selesai \u2018{$waktuSelesai}\u2019 tidak valid (format HH:MM).";
                    $rowNumber++;
                    continue;
                }

                // Resolve jenis → activity_type
                $jenisRaw = trim((string)($row['jenis'] ?? 'lesson'));
                $type     = in_array($jenisRaw, $validTypes)
                    ? $jenisRaw
                    : ($labelToType[strtolower($jenisRaw)] ?? null);

                if (!$type) {
                    $errors[] = "Baris {$rowNumber}: jenis \u2018{$jenisRaw}\u2019 tidak dikenal. Gunakan: " . implode(', ', $validTypes) . ".";
                    $rowNumber++;
                    continue;
                }

                // Calculate duration
                [$sh, $sm] = explode(':', $waktuMulai);
                [$eh, $em] = explode(':', $waktuSelesai);
                $duration  = ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);

                if ($duration <= 0) {
                    $errors[] = "Baris {$rowNumber}: waktu selesai harus setelah waktu mulai.";
                    $rowNumber++;
                    continue;
                }

                $grouped[$hari][] = [
                    'day'              => $hari,
                    'sequence'         => (int)$jamKe,
                    'start_time'       => $waktuMulai   . ':00',
                    'end_time'         => $waktuSelesai . ':00',
                    'duration_minutes' => $duration,
                    'activity_type'    => $type,
                    'note'             => !empty($row['keterangan']) ? (string)$row['keterangan'] : null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                $rowNumber++;
            }

            if (empty($grouped)) {
                $errMsg = !empty($errors)
                    ? 'Import gagal. Temuan: ' . implode(' | ', array_slice($errors, 0, 5))
                    : 'Tidak ada data valid dalam file. Pastikan format sesuai template.';
                return back()->with('error', $errMsg);
            }

            // Replace rows for each affected day
            $totalInserted = 0;
            DB::transaction(function () use ($grouped, &$totalInserted) {
                foreach ($grouped as $day => $dayRows) {
                    ClassPeriod::where('day', $day)->delete();
                    ClassPeriod::insert($dayRows);
                    $totalInserted += count($dayRows);
                }
            });

            $dayLabels = collect(array_keys($grouped))
                ->map(fn($d) => ClassPeriod::DAY_LABELS[$d] ?? $d)
                ->join(', ');

            $msg = "Berhasil mengimpor {$totalInserted} slot jam untuk hari: {$dayLabels}.";
            if (!empty($errors)) {
                $msg .= ' (' . count($errors) . ' baris dilewati karena tidak valid)';
            }

            return redirect()->route('jam-pelajaran.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    /* ── RESET (re-seed satu hari) ──────────────────────── */

    public function reset(Request $request)
    {
        $request->validate(['day' => ['required', Rule::in(ClassPeriod::DAYS)]]);
        $day = $request->day;

        try {
            DB::transaction(function () use ($day) {
                ClassPeriod::where('day', $day)->delete();
                $seeder = new \Database\Seeders\ClassPeriodSeeder;
                // Re-insert only one day using the seeder map
                $rows = $seeder->rowsForDay($day);
                ClassPeriod::insert($rows);
            });

            $successMessage = 'Jadwal hari ' . ClassPeriod::DAY_LABELS[$day] . ' berhasil direset.';
            if ($request->expectsJson()) {
                return $this->jsonDayResponse($day, $successMessage);
            }
            return back()->with('success', $successMessage);
        } catch (Throwable $e) {
            [$message, $status] = $this->resolvePeriodFailureReason($e, 'mereset');
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], $status);
            }
            return back()->with('error', $message);
        }
    }

    /* ── DAY TABLE (AJAX partial HTML) ─────────────────── */

    public function dayTable(Request $request, string $day)
    {
        if (!in_array($day, ClassPeriod::DAYS)) {
            return response()->json(['message' => 'Hari tidak valid'], 422);
        }

        $periods = ClassPeriod::forDay($day)->get();
        $html = view('Jam_Pelajaran._table', compact('periods', 'day'))->render();
        $lessonCount = $periods->where('activity_type', 'lesson')->count();

        return response()->json(['html' => $html, 'day' => $day, 'lesson_count' => $lessonCount]);
    }

    /* ── HELPERS ────────────────────────────────────────── */

    private function validatePeriod(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'day'           => ['required', Rule::in(ClassPeriod::DAYS)],
            'sequence'      => [
                'required', 'integer', 'min:0', 'max:30',
                Rule::unique('class_periods', 'sequence')
                    ->where('day', $request->day)
                    ->ignore($ignoreId),
            ],
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'activity_type' => ['required', Rule::in(array_keys(ClassPeriod::ACTIVITY_TYPES))],
            'note'          => 'nullable|string|max:200',
        ], [
            'sequence.unique'   => 'Urutan jam ini sudah ada di hari tersebut.',
            'end_time.after'    => 'Waktu selesai harus setelah waktu mulai.',
        ]);
    }

    /**
     * Convert an Excel fractional day value (0–1) to "HH:MM" string.
     * e.g. 0.25 -> "06:00"
     */
    private function excelTimeToHHMM(float $fraction): string
    {
        $totalMinutes = (int) round($fraction * 1440);
        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;
        return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
    }

    private function calcDuration(string $start, string $end): int
    {
        [$sh, $sm] = explode(':', $start);
        [$eh, $em] = explode(':', $end);
        return ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
    }

    private function shiftSequences(string $day, int $fromSeq, int $offset): void
    {
        ClassPeriod::where('day', $day)
            ->where('sequence', '>=', $fromSeq)
            ->orderBy('sequence', 'desc')
            ->each(fn($p) => $p->update(['sequence' => $p->sequence + $offset]));
    }

    private function resequence(string $day): void
    {
        ClassPeriod::where('day', $day)->orderBy('sequence')->get()
            ->each(fn($p, $i) => $p->sequence !== $i ? $p->update(['sequence' => $i]) : null);
    }

    private function jsonDayResponse(string $day, string $message = 'Operasi jam pelajaran berhasil.'): \Illuminate\Http\JsonResponse
    {
        $periods = ClassPeriod::forDay($day)->get()->map(fn($p) => [
            'id'             => $p->id,
            'sequence'       => $p->sequence,
            'start_time'     => substr($p->start_time, 0, 5),
            'end_time'       => substr($p->end_time, 0, 5),
            'duration_minutes' => $p->duration_minutes,
            'activity_type'  => $p->activity_type,
            'activity_label' => $p->activity_label,
            'note'           => $p->note,
            'is_lesson'      => $p->is_lesson,
        ]);

        $lessonCount = $periods->where('is_lesson', true)->count();

        return response()->json([
            'success' => true,
            'message' => $message,
            'day'     => $day,
            'periods' => $periods,
            'lesson_count' => $lessonCount,
        ]);
    }

    /**
     * Convert low-level DB/system errors into clear user-facing messages.
     *
     * @return array{0:string,1:int}
     */
    private function resolvePeriodFailureReason(Throwable $e, string $action): array
    {
        if ($e instanceof QueryException) {
            $sqlState   = (string) ($e->errorInfo[0] ?? $e->getCode());
            $rawMessage = strtolower($e->getMessage());

            if ($sqlState === '23000') {
                if (str_contains($rawMessage, 'class_periods_day_sequence_unique') || str_contains($rawMessage, 'unique')) {
                    return ['Gagal ' . $action . ' jam pelajaran: urutan jam pada hari tersebut sudah dipakai.', 422];
                }

                if (str_contains($rawMessage, 'foreign key') || str_contains($rawMessage, 'teacher_schedules')) {
                    return ['Gagal ' . $action . ' jam pelajaran: slot ini masih dipakai pada jadwal mengajar.', 422];
                }

                return ['Gagal ' . $action . ' jam pelajaran: terjadi konflik data.', 422];
            }

            return ['Gagal ' . $action . ' jam pelajaran: database sedang bermasalah, silakan coba lagi.', 500];
        }

        return ['Gagal ' . $action . ' jam pelajaran karena gangguan sistem.', 500];
    }
}
