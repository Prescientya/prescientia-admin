<?php

namespace App\Http\Controllers;

use App\Exports\StudentAttendanceExport;
use App\Models\ClassModel;
use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StudentAttendanceController extends Controller
{
    /* ───────────────────────────────────────────────────────────
     | GET /attendance/siswa
     |──────────────────────────────────────────────────────────*/
    public function index(Request $request)
    {
        // Date range — default: first day of current month → today
        $dateFrom = $request->get('date_from', today()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   today()->toDateString());
        $classId  = $request->get('class_id');
        $status   = $request->get('status');
        $search   = $request->get('search');

        // Get all calendar IDs in range
        $calendarIds = SchoolCalendar::whereBetween('date', [$dateFrom, $dateTo])
            ->pluck('id');

        $query = StudentAttendance::query()
            ->with(['student.schoolClass', 'kelas', 'calendar'])
            ->whereIn('calendar_id', $calendarIds)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->whereHas(
                'student', fn($sq) => $sq->where('name', 'ilike', "%{$search}%")
            ))
            ->orderByDesc(
                DB::table('school_calendar')
                    ->select('date')
                    ->whereColumn('school_calendar.id', 'student_attendances.calendar_id')
                    ->limit(1)
            )
            ->orderBy(
                Student::select('name')->whereColumn('students.id', 'student_attendances.student_id')
            );

        $attendances = $query->paginate(25)->withQueryString();

        // Summary counts for the date range
        $baseQ = StudentAttendance::whereIn('calendar_id', $calendarIds)
            ->when($classId, fn($q) => $q->where('class_id', $classId));

        $summary = [];
        foreach (['hadir', 'sakit', 'izin', 'alpa', 'terlambat'] as $s) {
            $summary[$s] = (clone $baseQ)->where('status', $s)->count();
        }
        $summary['total'] = (clone $baseQ)->count();

        $classes = ClassModel::orderBy('class')->orderBy('major')->get();

        // Check whether any range defaults changed (for reset button)
        $defaultFrom = today()->startOfMonth()->toDateString();
        $defaultTo   = today()->toDateString();
        $hasFilters  = $search || $status || $classId
                    || $dateFrom !== $defaultFrom
                    || $dateTo   !== $defaultTo;

        return view('Kehadiran_Siswa.index', compact(
            'attendances', 'summary', 'classes',
            'dateFrom', 'dateTo', 'classId', 'status', 'search', 'hasFilters'
        ));
    }

    /* ───────────────────────────────────────────────────────────
     | GET /attendance/siswa/export
     |──────────────────────────────────────────────────────────*/
    public function export(Request $request)
    {
        $dateFrom = $request->get('date_from', today()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   today()->toDateString());
        $classId  = $request->get('class_id') ?: null;
        $status   = $request->get('status')   ?: null;

        $from = Carbon::parse($dateFrom)->format('d-m-Y');
        $to   = Carbon::parse($dateTo)->format('d-m-Y');
        $filename = "rekap-kehadiran-siswa_{$from}_sd_{$to}.xlsx";

        return Excel::download(
            new StudentAttendanceExport($dateFrom, $dateTo, $classId ? (int) $classId : null, $status),
            $filename
        );
    }

    /* ───────────────────────────────────────────────────────────
     | POST /attendance/siswa  (manual input)
     |──────────────────────────────────────────────────────────*/
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'date'           => 'required|date',
            'status'         => 'required|in:hadir,sakit,izin,alpa,terlambat',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);

        $calendar = SchoolCalendar::forDate($validated['date']);
        $student  = Student::findOrFail($validated['student_id']);

        // Parse times with the chosen date
        $checkIn  = $validated['check_in_time']
            ? Carbon::parse($validated['date'] . ' ' . $validated['check_in_time'])
            : null;
        $checkOut = $validated['check_out_time']
            ? Carbon::parse($validated['date'] . ' ' . $validated['check_out_time'])
            : null;

        StudentAttendance::updateOrCreate(
            ['student_id' => $student->id, 'calendar_id' => $calendar->id],
            [
                'class_id'       => $student->class_id,
                'status'         => $validated['status'],
                'check_in_time'  => $checkIn,
                'check_out_time' => $checkOut,
                'source'         => 'manual',
            ]
        );

        return redirect()
            ->route('attendance.student.index', ['date_from' => $validated['date'], 'date_to' => $validated['date']])
            ->with('success', "Absensi {$student->name} berhasil disimpan.");
    }

    /* ───────────────────────────────────────────────────────────
     | PATCH /attendance/siswa/{id}  (unified: status + time)
     |──────────────────────────────────────────────────────────*/
    public function update(Request $request, int $id)
    {
        $request->validate([
            'status'         => 'nullable|in:hadir,sakit,izin,alpa,terlambat',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);

        $att  = StudentAttendance::with('calendar')->findOrFail($id);
        $date = $att->calendar->date->toDateString();

        $data = ['source' => 'manual'];

        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }

        // check_in_time: key present means update (null = clear)
        if ($request->has('check_in_time')) {
            $data['check_in_time'] = $request->check_in_time
                ? Carbon::parse("{$date} {$request->check_in_time}") : null;
        }

        if ($request->has('check_out_time')) {
            $data['check_out_time'] = $request->check_out_time
                ? Carbon::parse("{$date} {$request->check_out_time}") : null;
        }

        $att->update($data);
        $att->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'ok'        => true,
                'status'    => $att->status,
                'check_in'  => $att->check_in_time?->format('H:i'),
                'check_out' => $att->check_out_time?->format('H:i'),
            ]);
        }

        return back()->with('success', 'Absensi diperbarui.');
    }

    /* ───────────────────────────────────────────────────────────
     | DELETE /attendance/siswa/{id}
     |──────────────────────────────────────────────────────────*/
    public function destroy(Request $request, int $id)
    {
        StudentAttendance::findOrFail($id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Data absensi dihapus.');
    }

    /* ───────────────────────────────────────────────────────────
     | Kept for backward-compat (wifi/device source still uses these)
     |──────────────────────────────────────────────────────────*/
    public function updateStatus(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:hadir,sakit,izin,alpa,terlambat']);
        $att = StudentAttendance::findOrFail($id);
        $att->update(['status' => $request->status]);
        return $request->expectsJson()
            ? response()->json(['ok' => true, 'status' => $att->status])
            : back()->with('success', 'Status diperbarui.');
    }

    public function updateTime(Request $request, int $id)
    {
        $request->validate([
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);
        $att  = StudentAttendance::with('calendar')->findOrFail($id);
        $date = $att->calendar->date->toDateString();
        $att->update([
            'check_in_time'  => $request->check_in_time ? Carbon::parse("{$date} {$request->check_in_time}") : null,
            'check_out_time' => $request->check_out_time ? Carbon::parse("{$date} {$request->check_out_time}") : null,
        ]);
        return $request->expectsJson()
            ? response()->json(['ok' => true, 'check_in' => $att->check_in_time?->format('H:i'), 'check_out' => $att->check_out_time?->format('H:i')])
            : back()->with('success', 'Jam absensi diperbarui.');
    }

    /* ───────────────────────────────────────────────────────────
     | GET /attendance/siswa/search?q=...  (AJAX — autocomplete)
     |──────────────────────────────────────────────────────────*/
    public function searchStudents(Request $request)
    {
        $q = trim($request->get('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $students = Student::with('schoolClass')
            ->where('name', 'ilike', "%{$q}%")
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'nis'   => $s->nis,
                'kelas' => $s->schoolClass
                    ? ($s->schoolClass->class . ($s->schoolClass->major ? ' - ' . $s->schoolClass->major : ''))
                    : '-',
                'class_id' => $s->class_id,
            ]);

        return response()->json($students);
    }
}
