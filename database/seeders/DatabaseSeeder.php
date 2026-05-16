<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // AdminSeeder::class,
            // ClassSeeder::class,
            // StudentAttendanceSeeder::class,
            // ClassPeriodSeeder::class,
            GuidelineSeeder::class,
        ]);
    }
}
