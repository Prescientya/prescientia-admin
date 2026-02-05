<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSchedule;
use Illuminate\Database\Seeder;

class TeacherScheduleSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates sample teacher schedules for testing the admin features.
     * Run with: php artisan db:seed --class=TeacherScheduleSampleSeeder
     */
    public function run(): void
    {
        // Check if we have necessary data
        $teachers = Teacher::all();
        $classes = ClassModel::all();
        $subjects = Subject::all();
        $periods = ClassPeriod::where('activity_type', 'belajar')->get();

        if ($teachers->isEmpty() || $classes->isEmpty() || $subjects->isEmpty() || $periods->isEmpty()) {
            $this->command->error('Please ensure you have teachers, classes, subjects, and class_periods before running this seeder.');
            return;
        }

        $this->command->info('Creating sample teacher schedules...');

        // Get sample data
        $teacher1 = $teachers->first();
        $teacher2 = $teachers->skip(1)->first() ?? $teachers->first();
        $class1 = $classes->first();
        $class2 = $classes->skip(1)->first() ?? $classes->first();
        $subject1 = $subjects->first();
        $subject2 = $subjects->skip(1)->first() ?? $subjects->first();

        // Get periods for Monday
        $mondayPeriods = ClassPeriod::where('day', 'senin')
            ->where('activity_type', 'belajar')
            ->orderBy('sequence')
            ->take(3)
            ->get();

        // Get periods for Tuesday
        $tuesdayPeriods = ClassPeriod::where('day', 'selasa')
            ->where('activity_type', 'belajar')
            ->orderBy('sequence')
            ->take(2)
            ->get();

        $created = 0;
        $skipped = 0;

        // Teacher 1 teaches Subject 1 in Class 1 on Monday, periods 1-3
        foreach ($mondayPeriods as $period) {
            try {
                TeacherClassSchedule::create([
                    'teacher_id' => $teacher1->id,
                    'class_id' => $class1->id,
                    'subject_id' => $subject1->id,
                    'period_id' => $period->id,
                    'day' => 'senin',
                    'semester' => 1,
                ]);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        // Teacher 2 teaches Subject 2 in Class 2 on Tuesday, periods 1-2
        foreach ($tuesdayPeriods as $period) {
            try {
                TeacherClassSchedule::create([
                    'teacher_id' => $teacher2->id,
                    'class_id' => $class2->id,
                    'subject_id' => $subject2->id,
                    'period_id' => $period->id,
                    'day' => 'selasa',
                    'semester' => 1,
                ]);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        // Teacher 1 teaches Subject 2 in Class 2 on Wednesday, period 1
        $wednesdayPeriod = ClassPeriod::where('day', 'rabu')
            ->where('activity_type', 'belajar')
            ->orderBy('sequence')
            ->first();

        if ($wednesdayPeriod) {
            try {
                TeacherClassSchedule::create([
                    'teacher_id' => $teacher1->id,
                    'class_id' => $class2->id,
                    'subject_id' => $subject2->id,
                    'period_id' => $wednesdayPeriod->id,
                    'day' => 'rabu',
                    'semester' => 1,
                ]);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        $this->command->info("Sample schedules created successfully!");
        $this->command->info("Created: {$created}");
        $this->command->info("Skipped (duplicates): {$skipped}");
        $this->command->info("\nYou can now:");
        $this->command->info("1. View schedules at: /admin/teacher-schedules");
        $this->command->info("2. View attendance recap at: /admin/attendance/recap");
    }
}
