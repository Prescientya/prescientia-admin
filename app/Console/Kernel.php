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
        
        // Mark students and teachers as alpa if they have no attendance record for yesterday.
        // Runs every hour so missed executions (e.g. server downtime) are caught automatically.
        // A flag file in storage/app/ prevents duplicate processing for the same date.
        $schedule->command('attendance:mark-absent')
            ->hourly()
            ->timezone('Asia/Jakarta');

        // Promote all students to the next class level every July 19 (configurable in the command).
        // Class 10 → 11, Class 11 → 12, Class 12 → graduated (class_id set to null).
        // A flag file in storage/app/ prevents duplicate processing for the same year.
        $schedule->command('students:promote-class')
            ->dailyAt('00:10')
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
