<?php

namespace App\Services;

use App\Models\ClassPeriod;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * ClassPeriodImportService
 * 
 * Service untuk import jadwal pelajaran dari file Excel/CSV
 * Menggunakan PhpSpreadsheet untuk parsing file
 */
class ClassPeriodImportService
{
    /**
     * Parse file Excel/CSV dan convert ke array ClassPeriod
     * 
     * Format file yang diharapkan:
     * | HARI | JAM_KE | WAKTU_MULAI | WAKTU_SELESAI | KETERANGAN |
     * | senin | 0 | 06:30 | 07:15 | Upacara |
     * 
     * @param string $filePath - Path ke file upload
     * @return array - Array of [ day, sequence, start_time, end_time, activity_type, note ]
     * @throws \Exception
     */
    public function parseFile(string $filePath): array
    {
        try {
            // Load file dengan PhpSpreadsheet langsung (more reliable)
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $result = [];
            $validDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
            
            // Get highest row number
            $highestRow = $worksheet->getHighestRow();
            
            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $hari = trim(strtolower($worksheet->getCell('A' . $row)->getCalculatedValue()));
                $jamKe = trim($worksheet->getCell('B' . $row)->getCalculatedValue());
                $waktuMulai = trim($worksheet->getCell('C' . $row)->getCalculatedValue());
                $waktuSelesai = trim($worksheet->getCell('D' . $row)->getCalculatedValue());
                $keterangan = trim($worksheet->getCell('E' . $row)->getCalculatedValue());
                
                // Skip empty rows
                if (empty($hari)) {
                    continue;
                }

                // Validasi hari
                if (!in_array($hari, $validDays)) {
                    throw new \Exception("Baris {$row}: Hari '{$hari}' tidak valid. Gunakan: senin, selasa, rabu, kamis, jumat");
                }

                // Validasi jam ke (harus angka)
                if (!is_numeric($jamKe)) {
                    throw new \Exception("Baris {$row}: JAM_KE '{$jamKe}' harus berupa angka");
                }

                // Validasi dan parse waktu mulai dan selesai
                $startTime = $this->parseTime($waktuMulai);
                $endTime = $this->parseTime($waktuSelesai);

                if (!$startTime) {
                    throw new \Exception("Baris {$row}: Format WAKTU_MULAI '{$waktuMulai}' tidak valid. Gunakan format HH:MM");
                }

                if (!$endTime) {
                    throw new \Exception("Baris {$row}: Format WAKTU_SELESAI '{$waktuSelesai}' tidak valid. Gunakan format HH:MM");
                }

                // Tentukan activity type berdasarkan keterangan
                $activityType = $this->determineActivityType($keterangan);

                // Hitung durasi
                $startObj = Carbon::createFromFormat('H:i', $startTime);
                $endObj = Carbon::createFromFormat('H:i', $endTime);
                $duration = $endObj->diffInMinutes($startObj);

                if ($duration <= 0) {
                    throw new \Exception("Baris {$row}: Waktu selesai harus lebih besar dari waktu mulai");
                }

                $result[] = [
                    'day' => $hari,
                    'sequence' => (int) $jamKe,
                    'start_time' => $startTime . ':00',
                    'end_time' => $endTime . ':00',
                    'duration_minutes' => $duration,
                    'activity_type' => $activityType,
                    'note' => $keterangan ?: null,
                ];
            }

            if (empty($result)) {
                throw new \Exception('Tidak ada data yang valid ditemukan dalam file');
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Exception('Error parsing file: ' . $e->getMessage());
        }
    }

    /**
     * Parse waktu dari format "HH:MM"
     */
    private function parseTime(string $time): ?string
    {
        // Remove any extra spaces
        $time = trim($time);
        
        // Try to match HH:MM format
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            
            // Validate time
            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return sprintf('%02d:%02d', $hour, $minute);
            }
        }
        
        return null;
    }

    /**
     * Tentukan activity type berdasarkan keterangan dan hari
     */
    private function determineActivityType(string $keterangan): string
    {
        $keterangan = strtolower($keterangan);

        if (empty($keterangan)) {
            return 'lesson';
        }

            if (str_contains($keterangan, 'upacara')) {
                return 'ceremony';
            } elseif (str_contains($keterangan, 'istirahat') || str_contains($keterangan, 'mbg')) {
                return 'break';
            } elseif (str_contains($keterangan, 'shalat') || str_contains($keterangan, 'jum\'at') || str_contains($keterangan, 'jumat')) {
                return 'prayer';
            } elseif (str_contains($keterangan, 'tadarus') || str_contains($keterangan, 'kebersihan')) {
                return 'cleaning';
            } elseif (str_contains($keterangan, 'kerohanian') || str_contains($keterangan, 'olahraga')) {
                return 'other';
            }
        
            return 'lesson';
    }

    /**
     * Validasi data sebelum insert ke database
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Group by day untuk cek duplikat
        $byDay = collect($data)->groupBy('day');

        foreach ($byDay as $day => $periods) {
            // Cek duplikat sequence
            $sequences = $periods->pluck('sequence');
            if ($sequences->count() !== $sequences->unique()->count()) {
                $errors[] = "Hari {$day}: Ada duplikat JAM KE";
            }

            // Cek overlap waktu
            $sorted = $periods->sortBy('start_time');
            $prevEnd = null;
            foreach ($sorted as $period) {
                if ($prevEnd && $period['start_time'] < $prevEnd) {
                    $errors[] = "Hari {$day}: Ada overlap waktu antara jam pelajaran";
                    break;
                }
                $prevEnd = $period['end_time'];
            }
        }

        return $errors;
    }

    /**
     * Insert data ke database (hapus data lama dulu)
     */
    public function import(array $data): int
    {
        // Delete semua data lama
        ClassPeriod::query()->delete();

        // Insert data baru
        $count = 0;
        foreach ($data as $period) {
            ClassPeriod::create($period);
            $count++;
        }

        return $count;
    }

    /**
     * Check apakah sudah ada data di table
     */
    public function hasExistingData(): bool
    {
        return ClassPeriod::exists();
    }
}
