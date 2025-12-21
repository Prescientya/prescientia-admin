<?php

namespace Database\Seeders;

use App\Models\SchoolCalendar;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SchoolCalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate calendar for current year
        $year = date('Y');
        $startDate = Carbon::create($year, 1, 1);
        $endDate = Carbon::create($year, 12, 31);

        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            // Default to active, you can customize this based on your school's schedule
            $status = ($currentDate->dayOfWeek === Carbon::SATURDAY || 
                      $currentDate->dayOfWeek === Carbon::SUNDAY) 
                      ? 'libur' : 'aktif';

            SchoolCalendar::create([
                'date' => $currentDate->format('Y-m-d'),
                'year' => $currentDate->year,
                'month' => $currentDate->month,
                'day' => $currentDate->day,
                'status' => $status,
            ]);

            $currentDate->addDay();
        }
    }
}
