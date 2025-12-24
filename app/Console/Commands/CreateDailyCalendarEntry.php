<?php

namespace App\Console\Commands;

use App\Models\SchoolCalendar;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Grei\TanggalMerah;

class CreateDailyCalendarEntry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:create-daily';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically create calendar entry for tomorrow at midnight WIB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = now()->addDay()->toDateString();

        // Check if entry already exists
        if (SchoolCalendar::where('date', $tomorrow)->exists()) {
            $this->info("Calendar entry for {$tomorrow} already exists.");
            return 0;
        }

        $date = Carbon::parse($tomorrow);
        $status = $this->determineStatus($date);

        SchoolCalendar::create([
            'date' => $tomorrow,
            'year' => $date->year,
            'month' => $date->month,
            'day' => $date->day,
            'status' => $status,
        ]);

        $this->info("Calendar entry created for {$tomorrow} with status: {$status}");
        return 0;
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
