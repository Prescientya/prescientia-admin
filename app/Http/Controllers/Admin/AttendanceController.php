<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendance.
     */
    public function index()
    {
        // Get today's calendar
        $today = Carbon::today();
        $calendar = SchoolCalendar::whereDate('date', $today)->first();
        
        // Get student attendances for today
        $studentAttendances = StudentAttendance::with(['student', 'class', 'calendar'])
            ->when($calendar, function($query) use ($calendar) {
                return $query->where('calendar_id', $calendar->id);
            })
            ->latest()
            ->paginate(15, ['*'], 'students_page');
        
        // Get teacher attendances for today
        $teacherAttendances = TeacherAttendance::with(['teacher', 'calendar'])
            ->when($calendar, function($query) use ($calendar) {
                return $query->where('calendar_id', $calendar->id);
            })
            ->latest()
            ->paginate(15, ['*'], 'teachers_page');
        
        // Statistics
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        
        $studentStats = [
            'hadir' => StudentAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'hadir')->count(),
            'sakit' => StudentAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'sakit')->count(),
            'izin' => StudentAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'izin')->count(),
            'alpa' => StudentAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'alpa')->count(),
        ];
        
        $teacherStats = [
            'hadir' => TeacherAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'hadir')->count(),
            'sakit' => TeacherAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'sakit')->count(),
            'izin' => TeacherAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'izin')->count(),
            'dinas' => TeacherAttendance::when($calendar, function($q) use ($calendar) {
                return $q->where('calendar_id', $calendar->id);
            })->where('status', 'dinas')->count(),
        ];
        
        return view('admin.attendances.index', compact(
            'studentAttendances',
            'teacherAttendances',
            'calendar',
            'totalStudents',
            'totalTeachers',
            'studentStats',
            'teacherStats'
        ));
    }

    /**
     * Display attendance for students.
     */
    public function students()
    {
        return view('admin.attendances.students');
    }

    /**
     * Display attendance for teachers.
     */
    public function teachers()
    {
        return view('admin.attendances.teachers');
    }

    /**
     * Record attendance.
     */
    public function record(Request $request)
    {
        // Logic untuk record attendance akan ditambahkan nanti
        return redirect()->route('admin.attendances.index')
            ->with('success', 'Absensi berhasil direkam');
    }
}
