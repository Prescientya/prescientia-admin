<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceSummary;
use App\Models\SchoolCalendar;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Record student attendance.
     */
    public function recordStudentAttendance(
        int $studentId,
        int $classId,
        string $date,
        string $status,
        string $source,
        ?array $details = null
    ): StudentAttendance {
        $calendar = SchoolCalendar::where('date', $date)->firstOrFail();

        $attendance = StudentAttendance::updateOrCreate(
            [
                'student_id' => $studentId,
                'calendar_id' => $calendar->id,
            ],
            [
                'class_id' => $classId,
                'check_in_time' => now(),
                'status' => $status,
                'source' => $source,
            ]
        );

        // Create detail if provided
        if ($details && in_array($status, ['sakit', 'izin', 'alpa', 'terlambat'])) {
            $attendance->detail()->updateOrCreate(
                ['attendance_id' => $attendance->id],
                [
                    'reason' => $status,
                    'description' => $details['description'] ?? null,
                    'evidence_url' => $details['evidence_url'] ?? null,
                ]
            );
        }

        // Update summary
        $this->updateAttendanceSummary($studentId);

        return $attendance->fresh(['detail']);
    }

    /**
     * Update student attendance summary.
     */
    public function updateAttendanceSummary(int $studentId): void
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
            'total_hadir' => $attendances->where('status', 'hadir')->count() +
                           $attendances->where('status', 'terlambat')->count(),
            'total_izin' => $attendances->where('status', 'izin')->count(),
            'total_sakit' => $attendances->where('status', 'sakit')->count(),
            'total_alpha' => $attendances->where('status', 'alpa')->count(),
        ]);
    }

    /**
     * Get class attendance for specific date.
     */
    public function getClassAttendance(int $classId, string $date)
    {
        $calendar = SchoolCalendar::where('date', $date)->first();
        
        if (!$calendar) {
            return null;
        }

        return StudentAttendance::with(['student', 'detail'])
            ->where('class_id', $classId)
            ->where('calendar_id', $calendar->id)
            ->get()
            ->groupBy('status');
    }

    /**
     * Get student attendance report for period.
     */
    public function getStudentAttendanceReport(int $studentId, Carbon $startDate, Carbon $endDate)
    {
        $calendars = SchoolCalendar::whereBetween('date', [$startDate, $endDate])
            ->active()
            ->pluck('id');

        $attendances = StudentAttendance::with(['calendar', 'detail'])
            ->where('student_id', $studentId)
            ->whereIn('calendar_id', $calendars)
            ->get();

        return [
            'total_days' => $calendars->count(),
            'attended' => $attendances->count(),
            'present' => $attendances->whereIn('status', ['hadir', 'terlambat'])->count(),
            'sick' => $attendances->where('status', 'sakit')->count(),
            'permission' => $attendances->where('status', 'izin')->count(),
            'absent' => $attendances->where('status', 'alpa')->count(),
            'late' => $attendances->where('status', 'terlambat')->count(),
            'percentage' => $calendars->count() > 0 
                ? round(($attendances->whereIn('status', ['hadir', 'terlambat'])->count() / $calendars->count()) * 100, 2)
                : 0,
            'details' => $attendances,
        ];
    }

    /**
     * Auto check-in from WiFi detection.
     */
    public function autoCheckInFromWifi(int $userId, int $wifiId): ?StudentAttendance
    {
        $user = \App\Models\User::with('student.class')->find($userId);
        
        if (!$user || !$user->student) {
            return null;
        }

        $today = SchoolCalendar::where('date', today())->first();
        
        if (!$today || $today->status !== 'aktif') {
            return null;
        }

        // Check if already checked in today
        $existingAttendance = StudentAttendance::where('student_id', $user->student->id)
            ->where('calendar_id', $today->id)
            ->first();

        if ($existingAttendance) {
            return $existingAttendance;
        }

        // Create new attendance
        return $this->recordStudentAttendance(
            $user->student->id,
            $user->student->class_id,
            today()->format('Y-m-d'),
            'hadir',
            'digital_wifi'
        );
    }
}
