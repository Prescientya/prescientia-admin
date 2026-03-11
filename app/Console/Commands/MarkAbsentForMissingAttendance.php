<?php

namespace App\Console\Commands;

use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkAbsentForMissingAttendance extends Command
{
    protected $signature = 'attendance:mark-absent
                            {--date= : Simulate today\'s date in YYYY-MM-DD format (uses yesterday of this date)}
                            {--force : Bypass the flag file check and force re-processing}';

    protected $description = 'Mark students and teachers as alpa if they have no attendance record for yesterday. Runs hourly; a flag file prevents duplicate processing for the same date.';

    private function getFlagPath(string $date): string
    {
        return storage_path("app/attendance_auto_alpa_{$date}.flag");
    }

    private function writeFlag(string $date): void
    {
        file_put_contents($this->getFlagPath($date), now()->toDateTimeString());
    }

    private function cleanupOldFlags(): void
    {
        $pattern = storage_path('app/attendance_auto_alpa_*.flag');
        foreach (glob($pattern) as $file) {
            if (filemtime($file) < strtotime('-7 days')) {
                @unlink($file);
            }
        }
    }

    public function handle(): int
    {
        $today     = $this->option('date') ? Carbon::parse($this->option('date')) : now()->timezone('Asia/Jakarta');
        $yesterday = $today->copy()->subDay()->toDateString();

        $flagPath = $this->getFlagPath($yesterday);

        if (! $this->option('force') && file_exists($flagPath)) {
            $this->info("Already processed for {$yesterday}. Skipping.");
            return 0;
        }

        /** @var SchoolCalendar|null $calendar */
        $calendar = SchoolCalendar::where('date', $yesterday)->first();

        if (! $calendar || $calendar->status === 'libur') {
            $this->info("Yesterday ({$yesterday}) was not a school day. No action taken.");
            Log::info("[Attendance Auto] {$yesterday} was not a school day — skipped.");
            $this->writeFlag($yesterday);
            return 0;
        }

        $studentCount = $this->markStudentsAbsent($calendar->id);
        $teacherCount = $this->markTeachersAbsent($calendar->id);

        $this->writeFlag($yesterday);
        $this->cleanupOldFlags();

        $summary = "Auto-alpa for {$yesterday}: {$studentCount} student(s), {$teacherCount} teacher(s) marked alpa.";
        $this->info($summary);
        Log::info("[Attendance Auto] {$summary}");

        return 0;
    }

    private function markStudentsAbsent(int $calendarId): int
    {
        $attended = StudentAttendance::where('calendar_id', $calendarId)
            ->pluck('student_id')
            ->all();

        $missing = Student::whereNotNull('class_id')
            ->whereNotIn('id', $attended)
            ->get();

        foreach ($missing as $student) {
            StudentAttendance::create([
                'student_id'  => $student->id,
                'class_id'    => $student->class_id,
                'calendar_id' => $calendarId,
                'status'      => 'alpa',
                'source'      => null,
            ]);
        }

        return $missing->count();
    }

    private function markTeachersAbsent(int $calendarId): int
    {
        $attended = TeacherAttendance::where('calendar_id', $calendarId)
            ->pluck('teacher_id')
            ->all();

        $missing = Teacher::whereNotIn('id', $attended)->get();

        foreach ($missing as $teacher) {
            TeacherAttendance::create([
                'teacher_id'  => $teacher->id,
                'calendar_id' => $calendarId,
                'status'      => 'alpa',
                'source'      => null,
            ]);
        }

        return $missing->count();
    }
}
