<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Auto-fill alpha attendance for students and teachers who have no
| attendance record from the previous active school day.
| Runs daily at 00:05 WIB (Asia/Jakarta timezone).
|
*/
Schedule::command('attendance:mark-absent')
    ->dailyAt('00:05')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/attendance-auto-alpa.log'));
