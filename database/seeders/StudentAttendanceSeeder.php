<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentAttendanceSeeder extends Seeder
{
    /**
     * Seed realistic attendance data for the example students from the import template.
     *
     * Covers the current month (February 2026) – all weekdays up to today.
     * Status distribution per student: ±80% hadir, 5% terlambat, 5% sakit, 5% izin, 5% alpa.
     * Source is varied to demonstrate the "Metode Input" display.
     */
    public function run(): void
    {
        // ── 1. Use students that already exist in the database ──────
        $students = Student::with('schoolClass')->orderBy('id')->limit(20)->get();

        if ($students->isEmpty()) {
            $this->command->warn('Tidak ada siswa di database. Import data siswa terlebih dahulu.');
            return;
        }

        // ── 2. Build school days: weekdays in Feb 2026 up to today ──
        $start = Carbon::create(2026, 2, 1);
        $end   = Carbon::now()->min(Carbon::create(2026, 2, 28));

        $schoolDays = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekday()) {
                $schoolDays[] = $d->copy();
            }
        }

        if (empty($schoolDays)) {
            $this->command->warn('Tidak ada hari sekolah dalam range tanggal.');
            return;
        }

        // ── 3. Ensure school_calendar entries exist ────────────────
        $calendars = [];
        foreach ($schoolDays as $day) {
            $calendars[$day->toDateString()] = SchoolCalendar::firstOrCreate(
                ['date' => $day->toDateString()],
                [
                    'year'   => $day->year,
                    'month'  => $day->month,
                    'day'    => $day->day,
                    'status' => 'aktif',
                ]
            );
        }

        $this->command->info("Memproses " . count($schoolDays) . " hari sekolah untuk {$students->count()} siswa…");

        // ── 4. Status distribution (deterministic pseudo-random) ──────
        // ≈ 80% hadir, 5% terlambat, 7% sakit, 5% izin, 3% alpa
        $statusPool = array_merge(
            array_fill(0, 16, 'hadir'),
            array_fill(0, 1, 'terlambat'),
            array_fill(0, 1, 'sakit'),
            array_fill(0, 1, 'izin'),
            array_fill(0, 1, 'alpa'),
        );

        $sourcePool = ['manual', 'manual', 'manual', 'digital_wifi', 'wali_kelas'];

        $count = 0;
        foreach ($students as $idx => $student) {
            $classId = $student->class_id;

            foreach ($schoolDays as $dayIdx => $day) {
                $calendar = $calendars[$day->toDateString()];

                $poolIdx = ($idx * 7 + $dayIdx * 3) % count($statusPool);
                $status  = $statusPool[$poolIdx];

                $srcIdx = ($idx + $dayIdx) % count($sourcePool);
                $source = $sourcePool[$srcIdx];

                $checkIn  = null;
                $checkOut = null;
                if (in_array($status, ['hadir', 'terlambat'])) {
                    $minuteBase = $status === 'terlambat' ? 450 : 405; // 07:30 or 06:45
                    $checkIn    = $day->copy()->startOfDay()->addMinutes($minuteBase + (($idx + $dayIdx) % 15));
                    $checkOut   = $day->copy()->startOfDay()->addMinutes(870 + ($dayIdx % 20));  // ~14:30
                }

                StudentAttendance::updateOrCreate(
                    [
                        'student_id'  => $student->id,
                        'calendar_id' => $calendar->id,
                    ],
                    [
                        'class_id'       => $classId,
                        'status'         => $status,
                        'check_in_time'  => $checkIn,
                        'check_out_time' => $checkOut,
                        'source'         => $source,
                    ]
                );

                $count++;
            }
        }

        $this->command->info("Selesai. {$count} record absensi berhasil di-seed.");
    }
}
