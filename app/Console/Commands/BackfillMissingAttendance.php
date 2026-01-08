<?php

namespace App\Console\Commands;

use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BackfillMissingAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:backfill {--from= : Start date (YYYY-MM-DD)} {--to= : End date (YYYY-MM-DD)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Backfill missing attendance records (alpa) for date range or all historical aktif days without attendance data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fromDate = $this->option('from');
        $toDate = $this->option('to');

        // Determine date range
        if ($fromDate && $toDate) {
            try {
                $from = Carbon::parse($fromDate)->startOfDay();
                $to = Carbon::parse($toDate)->endOfDay();
            } catch (\Exception $e) {
                $this->error('Invalid date format. Use YYYY-MM-DD.');
                return 1;
            }
        } else {
            // Default: backfill all historical aktif days without any attendance records
            $from = Carbon::now()->subMonths(6)->startOfDay(); // Last 6 months
            $to = Carbon::now()->subDay()->endOfDay(); // Until yesterday
        }

        $this->info("Backfilling attendance for range: {$from->toDateString()} to {$to->toDateString()}");

        // Get all school calendars that are aktif in date range
        $calendars = SchoolCalendar::where('status', 'aktif')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();

        if ($calendars->isEmpty()) {
            $this->warn('No aktif school days found in date range.');
            return 0;
        }

        $totalStudents = 0;
        $totalTeachers = 0;

        foreach ($calendars as $calendar) {
            $studentCount = $this->markStudentsAbsent($calendar->date, $calendar->id);
            $teacherCount = $this->markTeachersAbsent($calendar->date, $calendar->id);

            if ($studentCount > 0 || $teacherCount > 0) {
                $summary = "{$calendar->date}: {$studentCount} students, {$teacherCount} teachers marked as alpa";
                $this->line("  " . $summary);
                $totalStudents += $studentCount;
                $totalTeachers += $teacherCount;
            }
        }

        $finalSummary = "Backfill complete: {$totalStudents} total students, {$totalTeachers} total teachers marked as alpa";
        $this->info($finalSummary);
        Log::info('[Attendance Backfill] ' . $finalSummary);

        return 0;
    }

    /**
     * Mark students without attendance records as alpa
     */
    private function markStudentsAbsent(string $date, int $calendarId): int
    {
        // Get all active students
        $allStudents = Student::whereHas('class')->get();
        
        // Get students who already have attendance records for this date
        $attendedStudents = StudentAttendance::where('calendar_id', $calendarId)
            ->pluck('student_id')
            ->toArray();

        // Find students without attendance records
        $absentStudents = $allStudents->whereNotIn('id', $attendedStudents);

        $count = 0;
        foreach ($absentStudents as $student) {
            // Check if record already exists (to avoid duplicates)
            $exists = StudentAttendance::where('calendar_id', $calendarId)
                ->where('student_id', $student->id)
                ->exists();

            if (!$exists) {
                StudentAttendance::create([
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'calendar_id' => $calendarId,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => 'alpa',
                    'source' => null,
                ]);

                $count++;
            }
        }
        return $count;
    }

    /**
     * Mark teachers without attendance records as alpa
     */
    private function markTeachersAbsent(string $date, int $calendarId): int
    {
        // Get all active teachers (not deleted)
        $allTeachers = Teacher::get();
        
        // Get teachers who already have attendance records for this date
        $attendedTeachers = TeacherAttendance::where('calendar_id', $calendarId)
            ->pluck('teacher_id')
            ->toArray();

        // Find teachers without attendance records
        $absentTeachers = $allTeachers->whereNotIn('id', $attendedTeachers);

        $count = 0;
        foreach ($absentTeachers as $teacher) {
            // Check if record already exists (to avoid duplicates)
            $exists = TeacherAttendance::where('calendar_id', $calendarId)
                ->where('teacher_id', $teacher->id)
                ->exists();

            if (!$exists) {
                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'calendar_id' => $calendarId,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => 'alpa',
                    'source' => null,
                ]);

                $count++;
            }
        }
        return $count;
    }
}
