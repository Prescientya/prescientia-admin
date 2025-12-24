<?php

namespace App\Console\Commands;

use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use Illuminate\Console\Command;
use Carbon\Carbon;

class MarkAbsentForMissingAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically mark students and teachers as alpa (absent) if they have no attendance record for yesterday';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = now()->subDay()->toDateString();
        
        // Check if yesterday was a school day (aktif)
        $calendar = SchoolCalendar::where('date', $yesterday)->first();
        
        if (!$calendar || $calendar->status === 'libur') {
            $this->info("Yesterday ({$yesterday}) was not a school day. No action taken.");
            return 0;
        }

        // Mark students as alpa
        $this->markStudentsAbsent($yesterday, $calendar->id);
        
        // Mark teachers as alpa
        $this->markTeachersAbsent($yesterday, $calendar->id);

        $this->info("Attendance records updated for {$yesterday}");
        return 0;
    }

    /**
     * Mark students without attendance records as alpa
     */
    private function markStudentsAbsent(string $date, int $calendarId): void
    {
        // Get all active students
        $allStudents = Student::whereHas('class')->get();
        
        // Get students who already have attendance records for this date
        $attendedStudents = StudentAttendance::where('calendar_id', $calendarId)
            ->pluck('student_id')
            ->toArray();

        // Find students without attendance records
        $absentStudents = $allStudents->whereNotIn('id', $attendedStudents);

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
                    'source' => 'wali_kelas',
                ]);

                $this->line("  ✓ Student {$student->name} (NIS: {$student->nis}) marked as alpa");
            }
        }
    }

    /**
     * Mark teachers without attendance records as alpa
     */
    private function markTeachersAbsent(string $date, int $calendarId): void
    {
        // Get all active teachers (not deleted)
        $allTeachers = Teacher::get();
        
        // Get teachers who already have attendance records for this date
        $attendedTeachers = TeacherAttendance::where('calendar_id', $calendarId)
            ->pluck('teacher_id')
            ->toArray();

        // Find teachers without attendance records
        $absentTeachers = $allTeachers->whereNotIn('id', $attendedTeachers);

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
                    'source' => 'manual',
                ]);

                $this->line("  ✓ Teacher {$teacher->name} (NIP: {$teacher->nip}) marked as alpa");
            }
        }
    }
}
