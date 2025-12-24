<?php

namespace Database\Seeders;

use App\Models\SchoolCalendar;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Grei\TanggalMerah;

class SchoolCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $startDate = Carbon::create($year, 1, 1);
        $endDate = Carbon::create($year, 12, 31);

        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $status = $this->determineStatus($currentDate);

            SchoolCalendar::updateOrCreate(
                ['date' => $currentDate->format('Y-m-d')],
                [
                    'year' => $currentDate->year,
                    'month' => $currentDate->month,
                    'day' => $currentDate->day,
                    'status' => $status,
                ]
            );

            $currentDate->addDay();
        }
    }

    /**
     * Determine if a date is a school day (aktif) or holiday (libur)
     * Menggunakan library grei/tanggalmerah untuk cek hari libur nasional Indonesia
     */
    private function determineStatus(Carbon $date): string
    {
        // Check if weekend (Sabtu/Minggu)
        if ($date->isSaturday() || $date->isSunday()) {
            return 'libur';
        }

        // Check if national holiday using grei/tanggalmerah
        try {
            $tm = new TanggalMerah();
            $tm->set_date($date->format('Y-m-d'));
            if ($tm->check()) {
                return 'libur';
            }
        } catch (\Exception $e) {
            // Jika ada error, anggap hari aktif
        }

        return 'aktif';
    }
}