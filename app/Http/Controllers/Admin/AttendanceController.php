<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Exports\StudentAttendanceExport;
use App\Exports\TeacherAttendanceExport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance for students (filtered, paginated).
     */
    public function students(Request $request)
    {
        // Get filter parameters
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'status' => $request->get('status'),
            'class_number' => $request->get('class_number'),
            'major' => $request->get('major'),
            'keyword' => $request->get('keyword'),
        ];

        // Build query with relationships
        $query = StudentAttendance::with(['student', 'class', 'calendar']);

        // Apply filters
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Filter by class number (10, 11, 12) and/or major
        if ($filters['class_number']) {
            $query->whereHas('class', function ($q) use ($filters) {
                $q->where('class', $filters['class_number']);
                if ($filters['major']) {
                    $q->where('major', $filters['major']);
                }
            });
        } elseif ($filters['major']) {
            // Filter by major only (all classes with this major)
            $query->whereHas('class', function ($q) use ($filters) {
                $q->where('major', $filters['major']);
            });
        }

        if ($filters['date_from'] || $filters['date_to']) {
            $query->whereHas('calendar', function ($q) use ($filters) {
                if ($filters['date_from']) {
                    $q->whereDate('date', '>=', $filters['date_from']);
                }
                if ($filters['date_to']) {
                    $q->whereDate('date', '<=', $filters['date_to']);
                }
            });
        }

        if ($filters['keyword']) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['keyword']}%")
                  ->orWhere('nis', 'like', "%{$filters['keyword']}%");
            });
        }

        // Paginate results (10 per page) and transform data
        $records = $query->latest()
            ->paginate(10)
            ->through(function ($attendance) {
                return (object) [
                    'id' => $attendance->id,
                    'role' => 'students',
                    'name' => $attendance->student->name ?? '-',
                    'class' => optional($attendance->class)->class,
                    'status' => $attendance->status,
                    'source' => $attendance->source,
                    'date' => optional($attendance->calendar)->date,
                ];
            });

        // Get unique class numbers (10, 11, 12) and majors for filter dropdowns
        $classNumbers = ClassModel::select('class')->distinct()->orderBy('class')->pluck('class');
        $majors = ClassModel::select('major')->distinct()->whereNotNull('major')->orderBy('major')->get();

        return view('admin.attendances.students', compact('records', 'classNumbers', 'majors', 'filters'));
    }

    /**
     * Export student attendance data to Excel based on current filters.
     */
    public function exportStudents(Request $request)
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'status' => $request->get('status'),
            'class_number' => $request->get('class_number'),
            'major' => $request->get('major'),
            'keyword' => $request->get('keyword'),
        ];

        $export = new StudentAttendanceExport($filters);
        return $export->export();
    }

    /**
     * Display attendance for teachers (filtered, paginated).
     */
    public function teachers(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $status = $request->get('status');
        $subjectId = $request->get('subject_id');
        $keyword = $request->get('keyword');

        $query = TeacherAttendance::with(['teacher', 'calendar']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom || $dateTo) {
            $query->whereHas('calendar', function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo) $q->whereDate('date', '<=', $dateTo);
            });
        }

        if ($subjectId) {
            $query->whereHas('teacher', function ($q) use ($subjectId) {
                $q->whereHas('subjects', function ($s) use ($subjectId) {
                    $s->where('subjects.id', $subjectId);
                });
            });
        }

        if ($keyword) {
            $query->whereHas('teacher', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")->orWhere('nip', 'like', "%{$keyword}%");
            });
        }

        $items = $query->latest()->get()->map(function ($a) {
            return (object) [
                'id' => $a->id,
                'role' => 'teachers',
                'name' => $a->teacher->name ?? '-',
                'class' => null,
                'status' => $a->status,
                'source' => $a->source,
                'date' => optional($a->calendar)->date,
                'model' => $a,
            ];
        });

        $perPage = 10;
        $page = $request->get('page', 1);
        $total = $items->count();
        $pagerItems = $items->forPage($page, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($pagerItems, $total, $perPage, $page, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        $subjects = \App\Models\Subject::orderBy('name')->get();

        return view('admin.attendances.teachers', compact('paginator', 'subjects', 'dateFrom', 'dateTo', 'status', 'subjectId', 'keyword'));
    }

    // (No duplicate no-argument wrappers — route methods accept Request directly.)

    /**
     * Record attendance.
     */
    public function record(Request $request)
    {
        // Logic untuk record attendance akan ditambahkan nanti
        return back()->with('success', 'Absensi berhasil direkam');
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

        return back()->with('success', 'Status absensi berhasil diperbarui.');
    }

    /**
     * Export teacher attendance data to Excel based on current filters.
     */
    public function exportTeachers(Request $request)
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'status' => $request->get('status'),
            'subject_id' => $request->get('subject_id'),
            'keyword' => $request->get('keyword'),
        ];

        $export = new TeacherAttendanceExport($filters);
        return $export->export();
    }
}
