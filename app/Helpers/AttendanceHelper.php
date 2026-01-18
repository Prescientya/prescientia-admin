<?php

namespace App\Helpers;

use App\Models\SchoolCalendar;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use Carbon\Carbon;

class AttendanceHelper
{
    /**
     * Get current semester based on current month.
     * Januari - Juni: Semester 2 (Genap)
     * Juli - Desember: Semester 1 (Ganjil)
     * 
     * @return int 1 or 2
     */
    public static function getCurrentSemester(): int
    {
        $currentMonth = now()->month;
        
        // Juli (7) - Desember (12) = Semester 1 (Ganjil)
        // Januari (1) - Juni (6) = Semester 2 (Genap)
        return ($currentMonth >= 7 && $currentMonth <= 12) ? 1 : 2;
    }

    /**
     * Get semester name (Ganjil/Genap) based on semester number.
     * 
     * @param int $semester
     * @return string
     */
    public static function getSemesterName(int $semester): string
    {
        return $semester === 1 ? 'Ganjil' : 'Genap';
    }

    /**
     * Get current academic year based on current date.
     * Example: "2025/2026"
     * 
     * @return string
     */
    public static function getCurrentAcademicYear(): string
    {
        $year = now()->year;
        $month = now()->month;
        
        // If in semester 1 (July-Dec), academic year is current/next
        // If in semester 2 (Jan-June), academic year is previous/current
        if ($month >= 7) {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }

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
