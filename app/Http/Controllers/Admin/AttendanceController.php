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
    public function index(Request $request)
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
     * History / filtered listing of attendance (students or teachers)
     */
    public function history(Request $request)
    {
        // prepare today's data so the main tabs still render
        $today = Carbon::today();
        $calendar = SchoolCalendar::whereDate('date', $today)->first();
        $studentAttendances = StudentAttendance::with(['student', 'class', 'calendar'])
            ->when($calendar, function($query) use ($calendar) {
                return $query->where('calendar_id', $calendar->id);
            })
            ->latest()
            ->paginate(15, ['*'], 'students_page');
        $teacherAttendances = TeacherAttendance::with(['teacher', 'calendar'])
            ->when($calendar, function($query) use ($calendar) {
                return $query->where('calendar_id', $calendar->id);
            })
            ->latest()
            ->paginate(15, ['*'], 'teachers_page');
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

        $role = $request->get('role', 'students'); // 'students' or 'teachers'
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $status = $request->get('status');
        $classId = $request->get('class_id');
        $keyword = $request->get('keyword');

        if ($role === 'teachers') {
            $query = TeacherAttendance::with(['teacher', 'calendar']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom || $dateTo) {
                $query->whereHas('calendar', function($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('date', '<=', $dateTo);
                });
            }

            if ($keyword) {
                $query->whereHas('teacher', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            }

            $results = $query->latest()->paginate(25, ['*'], 'history_page');
        } else {
            $query = StudentAttendance::with(['student', 'class', 'calendar']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($classId) {
                $query->where('class_id', $classId);
            }

            if ($dateFrom || $dateTo) {
                $query->whereHas('calendar', function($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('date', '<=', $dateTo);
                });
            }

            if ($keyword) {
                $query->whereHas('student', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            }

            $results = $query->latest()->paginate(25, ['*'], 'history_page');
        }

        return view('admin.attendances.index', compact(
            'studentAttendances',
            'teacherAttendances',
            'calendar',
            'totalStudents',
            'totalTeachers',
            'studentStats',
            'teacherStats',
            'results',
            'role',
            'dateFrom',
            'dateTo',
            'status',
            'classId',
            'keyword'
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

    /**
     * Show detail for a specific attendance record (student or teacher)
     */
    public function show(Request $request, $role, $id)
    {
        if ($role === 'teachers') {
            $attendance = TeacherAttendance::with(['teacher', 'calendar'])->findOrFail($id);
        } else {
            $attendance = StudentAttendance::with(['student', 'class', 'calendar'])->findOrFail($id);
        }

        return view('admin.attendances.show', compact('attendance', 'role'));
    }

    /**
     * Update status of an attendance record
     */
    public function update(Request $request, $role, $id)
    {
        if ($role === 'teachers') {
            $attendance = TeacherAttendance::findOrFail($id);
            $allowed = ['hadir','sakit','izin','dinas','alpa'];
        } else {
            $attendance = StudentAttendance::findOrFail($id);
            $allowed = ['hadir','sakit','izin','alpa'];
        }

        $data = $request->validate([
            'status' => ['required'],
        ]);

        if (!in_array($data['status'], $allowed)) {
            return back()->withErrors(['status' => 'Status tidak valid untuk tipe ini.']);
        }

        $attendance->status = $data['status'];
        // Set source to 'manual' when admin updates the status
        $attendance->source = 'manual';
        $attendance->save();

        return redirect()->route('admin.attendances.index')->with('success', 'Status absensi berhasil diperbarui.');
    }
}
