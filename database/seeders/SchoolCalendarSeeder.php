<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchoolCalendarSeeder extends Seeder
{
    /**
     * Generate the school calendar for a given year.
     * Called from SchoolCalendarController or via artisan db:seed.
     * Usage from artisan: php artisan db:seed --class=SchoolCalendarSeeder --year=2026
     */
    public function run(int $year = 0): void
    {
        // Allow year to be passed as an option when called directly
        if ($year === 0) {
            $year = (int) ($this->command?->option('year') ?? date('Y'));
        }

        // ── Indonesian National Holidays ────────────────────────────────
        // Format: 'YYYY-MM-DD' => 'Nama Hari Libur'
        $nationalHolidays = self::getIndonesianHolidays($year);

        // ── Cleanup: remove existing entries for this year ──────────────
        DB::table('school_calendar')->where('year', $year)->delete();

        // ── Generate all days of the year ───────────────────────────────
        $start  = Carbon::create($year, 1, 1);
        $end    = Carbon::create($year, 12, 31);
        $chunk  = [];
        $now    = now()->toDateTimeString();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr    = $d->format('Y-m-d');
            $dayOfWeek  = $d->dayOfWeek; // 0 = Sunday, 6 = Saturday
            $isWeekend  = in_array($dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
            $isHoliday  = $isWeekend || isset($nationalHolidays[$dateStr]);

            $notes = null;
            if ($isWeekend) {
                $notes = $dayOfWeek === Carbon::SUNDAY ? 'Minggu' : 'Sabtu';
            }
            if (isset($nationalHolidays[$dateStr])) {
                $notes = $nationalHolidays[$dateStr];
            }

            $chunk[] = [
                'date'       => $dateStr,
                'year'       => $year,
                'month'      => $d->month,
                'day'        => $d->day,
                'status'     => $isHoliday ? 'libur' : 'aktif',
                'notes'      => $notes,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert in batches of 100 for performance
            if (count($chunk) >= 100) {
                DB::table('school_calendar')->insert($chunk);
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            DB::table('school_calendar')->insert($chunk);
        }
    }

    /* ── ────────────────────────────────────────────────────────────── --
     |  Indonesian Public Holidays + Cuti Bersama
     |  Sources: PP Keputusan Presiden & SKB 3 Menteri
     -- ────────────────────────────────────────────────────────────── */
    public static function getIndonesianHolidays(int $year): array
    {
        $holidays = [
            // ===================== 2025 =====================
            2025 => [
                '2025-01-01' => 'Tahun Baru Masehi 2025',
                '2025-01-27' => 'Isra Mikraj Nabi Muhammad SAW 1446 H',
                '2025-01-28' => 'Cuti Bersama Tahun Baru Imlek',
                '2025-01-29' => 'Tahun Baru Imlek 2576 Kongzili',
                '2025-03-28' => 'Cuti Bersama Hari Suci Nyepi',
                '2025-03-29' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)',
                '2025-03-31' => 'Idul Fitri 1446 H',
                '2025-04-01' => 'Idul Fitri 1446 H Hari Ke-2',
                '2025-04-02' => 'Cuti Bersama Idul Fitri',
                '2025-04-03' => 'Cuti Bersama Idul Fitri',
                '2025-04-04' => 'Cuti Bersama Idul Fitri',
                '2025-04-07' => 'Cuti Bersama Idul Fitri',
                '2025-04-18' => 'Wafat Yesus Kristus',
                '2025-05-01' => 'Hari Buruh Internasional',
                '2025-05-12' => 'Hari Raya Waisak 2569 BE',
                '2025-05-13' => 'Cuti Bersama Hari Raya Waisak',
                '2025-05-29' => 'Kenaikan Yesus Kristus',
                '2025-05-30' => 'Cuti Bersama Kenaikan Yesus Kristus',
                '2025-06-01' => 'Hari Lahir Pancasila',
                '2025-06-06' => 'Idul Adha 1446 H',
                '2025-06-27' => 'Tahun Baru Islam 1447 H',
                '2025-08-17' => 'Hari Kemerdekaan Republik Indonesia',
                '2025-09-05' => 'Maulid Nabi Muhammad SAW 1447 H',
                '2025-12-25' => 'Hari Raya Natal',
                '2025-12-26' => 'Cuti Bersama Hari Raya Natal',
            ],

            // ===================== 2026 =====================
            2026 => [
                '2026-01-01' => 'Tahun Baru Masehi 2026',
                '2026-01-17' => 'Isra Mikraj Nabi Muhammad SAW 1447 H',
                '2026-01-28' => 'Tahun Baru Imlek 2577 Kongzili',
                '2026-01-29' => 'Cuti Bersama Tahun Baru Imlek',
                '2026-03-19' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)',
                '2026-03-20' => 'Cuti Bersama Hari Suci Nyepi',
                '2026-04-02' => 'Wafat Yesus Kristus',
                '2026-04-04' => 'Hari Raya Paskah',
                '2026-04-20' => 'Idul Fitri 1447 H',
                '2026-04-21' => 'Idul Fitri 1447 H Hari Ke-2',
                '2026-04-22' => 'Cuti Bersama Idul Fitri',
                '2026-04-23' => 'Cuti Bersama Idul Fitri',
                '2026-04-24' => 'Cuti Bersama Idul Fitri',
                '2026-05-01' => 'Hari Buruh Internasional',
                '2026-05-14' => 'Kenaikan Yesus Kristus',
                '2026-05-15' => 'Cuti Bersama Kenaikan Yesus Kristus',
                '2026-06-01' => 'Hari Lahir Pancasila',
                '2026-06-26' => 'Idul Adha 1447 H',
                '2026-06-16' => 'Hari Raya Waisak 2570 BE',
                '2026-07-16' => 'Tahun Baru Islam 1448 H',
                '2026-08-17' => 'Hari Kemerdekaan Republik Indonesia',
                '2026-10-05' => 'Maulid Nabi Muhammad SAW 1448 H',
                '2026-12-25' => 'Hari Raya Natal',
                '2026-12-26' => 'Cuti Bersama Hari Raya Natal',
            ],

            // ===================== 2027 =====================
            2027 => [
                '2027-01-01' => 'Tahun Baru Masehi 2027',
                '2027-01-06' => 'Isra Mikraj Nabi Muhammad SAW 1448 H',
                '2027-01-16' => 'Tahun Baru Imlek 2578 Kongzili',
                '2027-01-17' => 'Cuti Bersama Tahun Baru Imlek',
                '2027-03-08' => 'Hari Suci Nyepi (Tahun Baru Saka 1949)',
                '2027-03-26' => 'Wafat Yesus Kristus',
                '2027-04-09' => 'Idul Fitri 1448 H',
                '2027-04-10' => 'Idul Fitri 1448 H Hari Ke-2',
                '2027-04-11' => 'Cuti Bersama Idul Fitri',
                '2027-04-12' => 'Cuti Bersama Idul Fitri',
                '2027-04-13' => 'Cuti Bersama Idul Fitri',
                '2027-04-14' => 'Cuti Bersama Idul Fitri',
                '2027-05-01' => 'Hari Buruh Internasional',
                '2027-06-01' => 'Hari Lahir Pancasila',
                '2027-06-03' => 'Kenaikan Yesus Kristus',
                '2027-06-06' => 'Idul Adha 1448 H',
                '2027-06-07' => 'Hari Raya Waisak 2571 BE',
                '2027-07-06' => 'Tahun Baru Islam 1449 H',
                '2027-08-17' => 'Hari Kemerdekaan Republik Indonesia',
                '2027-09-14' => 'Maulid Nabi Muhammad SAW 1449 H',
                '2027-12-25' => 'Hari Raya Natal',
                '2027-12-26' => 'Cuti Bersama Hari Raya Natal',
            ],
        ];

        return $holidays[$year] ?? [];
    }
}
