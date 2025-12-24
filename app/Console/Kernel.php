<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Create calendar entry for next day at midnight WIB (00:00)
        // WIB is UTC+7, so 5 PM UTC = midnight WIB
        $schedule->command('calendar:create-daily')
            ->dailyAt('00:00')
            ->timezone('Asia/Jakarta');
        
        // Mark students and teachers as alpa if they have no attendance record for yesterday
        // Run at 00:05 AM WIB (after midnight) so calendar entry is created first
        $schedule->command('attendance:mark-absent')
            ->dailyAt('00:05')
            ->timezone('Asia/Jakarta');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
