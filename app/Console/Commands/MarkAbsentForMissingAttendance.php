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

class MarkAbsentForMissingAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent
                            {--date= : (optional) simulate today date in YYYY-MM-DD format}
                            {--force : Bypass the flag check and force re-processing}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically mark students and teachers as alpa (absent) if they have no attendance record for yesterday. Runs hourly and uses a flag file to prevent duplicate processing.';

    /**
     * Get the path for a date\'s flag file.
     */
    private function getFlagPath(string $date): string
    {
        return storage_path("app/attendance_auto_alpa_{$date}.flag");
    }

    /**
     * Write a flag file indicating the given date has been processed.
     */
    private function writeFlag(string $date): void
    {
        file_put_contents($this->getFlagPath($date), now()->toDateTimeString());
    }

    /**
     * Delete flag files older than 7 days.
     */
    private function cleanupOldFlags(): void
    {
        $pattern = storage_path('app/attendance_auto_alpa_*.flag');
        foreach (glob($pattern) as $file) {
            if (filemtime($file) < strtotime('-7 days')) {
                @unlink($file);
            }
        }
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('date')) {
            try {
                $yesterday = \Carbon\Carbon::parse($this->option('date'))->subDay()->toDateString();
            } catch (\Exception $e) {
                $this->error('Invalid --date value. Use YYYY-MM-DD.');
                return 1;
            }
        } else {
            $yesterday = now()->subDay()->toDateString();
        }

        // Check flag file — skip if already processed for this date (unless --force)
        $flagPath = $this->getFlagPath($yesterday);
        if (!$this->option('force') && file_exists($flagPath)) {
            $processedAt = trim(file_get_contents($flagPath));
            $message = "Attendance auto-alpa for {$yesterday} already processed at {$processedAt}. Skipping.";
            $this->info($message);
            Log::info('[Attendance Automation] ' . $message);
            return 0;
        }

        // Check if yesterday was a school day (aktif)
        $calendar = SchoolCalendar::where('date', $yesterday)->first();
        
        if (!$calendar || $calendar->status === 'libur') {
            $message = "Yesterday ({$yesterday}) was not a school day. No action taken.";
            $this->info($message);
            Log::info('[Attendance Automation] ' . $message);
            // Still write the flag so we don't check again today for a holiday
            $this->writeFlag($yesterday);
            return 0;
        }

        // Mark students as alpa
        $studentCount = $this->markStudentsAbsent($yesterday, $calendar->id);
        
        // Mark teachers as alpa
        $teacherCount = $this->markTeachersAbsent($yesterday, $calendar->id);

        // Write flag so this date won't be processed again
        $this->writeFlag($yesterday);

        // Cleanup flag files older than 7 days
        $this->cleanupOldFlags();

        $summary = "Attendance records updated for {$yesterday}: {$studentCount} students, {$teacherCount} teachers marked as alpa";
        $this->info($summary);
        Log::info('[Attendance Automation] ' . $summary);
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
                $this->line("  ✓ Student {$student->name} (NIS: {$student->nis}) marked as alpa");
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
                $this->line("  ✓ Teacher {$teacher->name} (NIP: {$teacher->nip}) marked as alpa");
            }
        }
        return $count;
    }
}
