<?php

namespace Database\Seeders;

use App\Models\ClassPeriod;
use Illuminate\Database\Seeder;

class ClassPeriodSeeder extends Seeder
{
    /**
     * Jadwal jam pelajaran berdasarkan dokumen sekolah.
     *
     * Senin         : Jam 0 = Upacara (06:30–07:15), 10 JP, selesai 15:00
     * Selasa–Kamis  : Jam 0 = Tadarus & Kebersihan (06:10–06:30), 11 JP, selesai 15:10
     * Jumat         : Jam 0 = Kerohanian/Olahraga/Kebersihan (06:30–07:30), 8 JP (35 mnt),
     *                 Shalat Jum'at/Keputrian, Ekstra Kurikuler
     */
    public function run(): void
    {
        ClassPeriod::query()->delete();
        $rows = [];
        foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $day) {
            foreach ($this->rowsForDay($day) as $row) {
                $rows[] = $row;
            }
        }
        ClassPeriod::insert($rows);
        $this->command->info('ClassPeriod seeded: ' . count($rows) . ' rows (5 hari).');
    }

    /** Return array of DB rows for one specific day (used by reset endpoint). */
    public function rowsForDay(string $day): array
    {
        $now = now();
        $rows = [];
        foreach ($this->scheduleMap()[$day] as [$seq, $start, $end, $dur, $type, $note]) {
            $rows[] = [
                'day'              => $day,
                'sequence'         => $seq,
                'start_time'       => $start . ':00',
                'end_time'         => $end   . ':00',
                'duration_minutes' => $dur,
                'activity_type'    => $type,
                'note'             => $note,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }
        return $rows;
    }

    /** Full schedule map for all days. */
    private function scheduleMap(): array
    {
        // ── Senin (dari gambar: Upacara jam 0) ─────────────
        $senin = [
            [0,  '06:30', '07:15', 45, 'ceremony', 'Upacara Bendera'],
            [1,  '07:15', '07:55', 40, 'lesson',   null],
            [2,  '07:55', '08:35', 40, 'lesson',   null],
            [3,  '08:35', '09:15', 40, 'lesson',   null],
            [4,  '09:15', '09:55', 40, 'lesson',   null],
            [5,  '09:55', '10:25', 30, 'break',    'Istirahat / MBG'],
            [6,  '10:25', '11:05', 40, 'lesson',   null],
            [7,  '11:05', '11:45', 40, 'lesson',   null],
            [8,  '11:45', '12:20', 35, 'break',    'Istirahat'],
            [9,  '12:20', '13:00', 40, 'lesson',   null],
            [10, '13:00', '13:40', 40, 'lesson',   null],
            [11, '13:40', '14:20', 40, 'lesson',   null],
            [12, '14:20', '15:00', 40, 'lesson',   null],
        ];

        // ── Selasa – Kamis (Tadarus 06:10, 11 JP, selesai 15:10) ─
        $selasaKamis = [
            [0,  '06:10', '06:30', 20, 'cleaning', 'Tadarus & Kebersihan'],
            [1,  '06:30', '07:10', 40, 'lesson',   null],
            [2,  '07:10', '07:50', 40, 'lesson',   null],
            [3,  '07:50', '08:30', 40, 'lesson',   null],
            [4,  '08:30', '09:10', 40, 'lesson',   null],
            [5,  '09:10', '09:50', 40, 'lesson',   null],
            [6,  '09:50', '10:20', 30, 'break',    'Istirahat / MBG'],
            [7,  '10:20', '11:00', 40, 'lesson',   null],
            [8,  '11:00', '11:40', 40, 'lesson',   null],
            [9,  '11:40', '12:30', 50, 'break',    'Istirahat'],
            [10, '12:30', '13:10', 40, 'lesson',   null],
            [11, '13:10', '13:50', 40, 'lesson',   null],
            [12, '13:50', '14:30', 40, 'lesson',   null],
            [13, '14:30', '15:10', 40, 'lesson',   null],
        ];

        // ── Jumat (Kerohanian 60 mnt, JP 35 mnt, Shalat Jumat/Keputrian) ──
        $jumat = [
            [0,  '06:30', '07:30', 60, 'ceremony', 'Kerohanian / Olahraga / Kebersihan'],
            [1,  '07:30', '08:05', 35, 'lesson',   null],
            [2,  '08:05', '08:40', 35, 'lesson',   null],
            [3,  '08:40', '09:15', 35, 'lesson',   null],
            [4,  '09:15', '09:50', 35, 'lesson',   null],
            [5,  '09:50', '10:20', 30, 'break',    'Istirahat / MBG'],
            [6,  '10:20', '10:55', 35, 'lesson',   null],
            [7,  '10:55', '11:30', 35, 'lesson',   null],
            [8,  '11:30', '12:30', 60, 'prayer',   "Shalat Jum'at / Keputrian"],
            [9,  '12:30', '13:10', 40, 'lesson',   null],
            [10, '13:10', '13:50', 40, 'lesson',   null],
            [11, '13:50', '15:00', 70, 'other',    'Kegiatan Ekstra Kurikuler'],
        ];

        return [
            'senin'  => $senin,
            'selasa' => $selasaKamis,
            'rabu'   => $selasaKamis,
            'kamis'  => $selasaKamis,
            'jumat'  => $jumat,
        ];
    }
}
