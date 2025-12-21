<?php

namespace App\Helpers;

use App\Models\SchoolCalendar;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use Carbon\Carbon;

class AttendanceHelper
{
    /**
     * Check if today is a school day.
     */
    public static function isTodaySchoolDay(): bool
    {
        $today = SchoolCalendar::where('date', today())->first();
        return $today && $today->status === 'aktif';
    }

    /**
     * Get active school days count for a period.
     */
    public static function getActiveDaysCount(Carbon $startDate, Carbon $endDate): int
    {
        return SchoolCalendar::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'aktif')
            ->count();
    }

    /**
     * Get attendance statistics for a class on a specific date.
     */
    public static function getClassStatistics(int $classId, string $date): array
    {
        $calendar = SchoolCalendar::where('date', $date)->first();
        
        if (!$calendar) {
            return [
                'total' => 0,
                'present' => 0,
                'sick' => 0,
                'permission' => 0,
                'absent' => 0,
                'late' => 0,
            ];
        }

        $attendances = StudentAttendance::where('class_id', $classId)
            ->where('calendar_id', $calendar->id)
            ->get();

        return [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'hadir')->count(),
            'sick' => $attendances->where('status', 'sakit')->count(),
            'permission' => $attendances->where('status', 'izin')->count(),
            'absent' => $attendances->where('status', 'alpa')->count(),
            'late' => $attendances->where('status', 'terlambat')->count(),
        ];
    }

    /**
     * Get students who are absent today.
     */
    public static function getTodayAbsentStudents(int $classId)
    {
        $calendar = SchoolCalendar::where('date', today())->first();
        
        if (!$calendar) {
            return collect([]);
        }

        return StudentAttendance::with(['student', 'detail'])
            ->where('class_id', $classId)
            ->where('calendar_id', $calendar->id)
            ->whereIn('status', ['sakit', 'izin', 'alpa'])
            ->get();
    }

    /**
     * Get teacher attendance rate for a period.
     */
    public static function getTeacherAttendanceRate(int $teacherId, Carbon $startDate, Carbon $endDate): float
    {
        $activeDays = self::getActiveDaysCount($startDate, $endDate);
        
        if ($activeDays === 0) {
            return 0;
        }

        $calendars = SchoolCalendar::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'aktif')
            ->pluck('id');

        $presentDays = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereIn('calendar_id', $calendars)
            ->whereIn('status', ['hadir'])
            ->count();

        return round(($presentDays / $activeDays) * 100, 2);
    }

    /**
     * Check if student can check-in (not already checked in today).
     */
    public static function canCheckIn(int $studentId): bool
    {
        if (!self::isTodaySchoolDay()) {
            return false;
        }

        $calendar = SchoolCalendar::where('date', today())->first();
        
        return !StudentAttendance::where('student_id', $studentId)
            ->where('calendar_id', $calendar->id)
            ->exists();
    }

    /**
     * Get attendance percentage by status.
     */
    public static function getAttendancePercentageByStatus(int $studentId): array
    {
        $attendances = StudentAttendance::where('student_id', $studentId)->get();
        $total = $attendances->count();

        if ($total === 0) {
            return [
                'present' => 0,
                'sick' => 0,
                'permission' => 0,
                'absent' => 0,
                'late' => 0,
            ];
        }

        return [
            'present' => round(($attendances->where('status', 'hadir')->count() / $total) * 100, 2),
            'sick' => round(($attendances->where('status', 'sakit')->count() / $total) * 100, 2),
            'permission' => round(($attendances->where('status', 'izin')->count() / $total) * 100, 2),
            'absent' => round(($attendances->where('status', 'alpa')->count() / $total) * 100, 2),
            'late' => round(($attendances->where('status', 'terlambat')->count() / $total) * 100, 2),
        ];
    }

    /**
     * Get monthly attendance summary.
     */
    public static function getMonthlyAttendanceSummary(int $classId, int $year, int $month): array
    {
        $calendars = SchoolCalendar::where('year', $year)
            ->where('month', $month)
            ->where('status', 'aktif')
            ->pluck('id');

        $attendances = StudentAttendance::whereIn('calendar_id', $calendars)
            ->where('class_id', $classId)
            ->get();

        $activeDays = $calendars->count();
        $totalStudents = \App\Models\Student::where('class_id', $classId)->count();
        $expectedAttendances = $activeDays * $totalStudents;

        return [
            'active_days' => $activeDays,
            'total_students' => $totalStudents,
            'expected_attendances' => $expectedAttendances,
            'actual_attendances' => $attendances->count(),
            'present' => $attendances->whereIn('status', ['hadir', 'terlambat'])->count(),
            'sick' => $attendances->where('status', 'sakit')->count(),
            'permission' => $attendances->where('status', 'izin')->count(),
            'absent' => $attendances->where('status', 'alpa')->count(),
            'attendance_rate' => $expectedAttendances > 0 
                ? round(($attendances->whereIn('status', ['hadir', 'terlambat'])->count() / $expectedAttendances) * 100, 2)
                : 0,
        ];
    }
}
