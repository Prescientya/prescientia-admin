<?php

namespace App\Observers;

use App\Models\StudentAttendance;
use App\Models\StudentAttendanceSummary;

class StudentAttendanceObserver
{
    /**
     * Handle the StudentAttendance "created" event.
     */
    public function created(StudentAttendance $attendance): void
    {
        $this->updateSummary($attendance->student_id);
    }

    /**
     * Handle the StudentAttendance "updated" event.
     */
    public function updated(StudentAttendance $attendance): void
    {
        $this->updateSummary($attendance->student_id);
    }

    /**
     * Handle the StudentAttendance "deleted" event.
     */
    public function deleted(StudentAttendance $attendance): void
    {
        $this->updateSummary($attendance->student_id);
    }

    /**
     * Update attendance summary for student.
     */
    private function updateSummary(int $studentId): void
    {
        $summary = StudentAttendanceSummary::firstOrCreate(
            ['student_id' => $studentId],
            [
                'total_hadir' => 0,
                'total_izin' => 0,
                'total_sakit' => 0,
                'total_alpha' => 0,
            ]
        );

        $attendances = StudentAttendance::where('student_id', $studentId)->get();

        $summary->update([
            'total_hadir' => $attendances->whereIn('status', ['hadir', 'terlambat'])->count(),
            'total_izin' => $attendances->where('status', 'izin')->count(),
            'total_sakit' => $attendances->where('status', 'sakit')->count(),
            'total_alpha' => $attendances->where('status', 'alpa')->count(),
        ]);
    }
}
