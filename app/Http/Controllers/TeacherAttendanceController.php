<?php

namespace App\Http\Controllers;

use App\Exports\TeacherAttendanceExport;
use App\Models\SchoolCalendar;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TeacherAttendanceController extends Controller
{
    /* ───────────────────────────────────────────────────────────
     | GET /attendance/guru
     |──────────────────────────────────────────────────────────*/
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', today()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   today()->toDateString());
        $status   = $request->get('status');
        $search   = $request->get('search');

        $calendarIds = SchoolCalendar::whereBetween('date', [$dateFrom, $dateTo])
            ->pluck('id');

        $query = TeacherAttendance::query()
            ->with(['teacher', 'calendar'])
            ->join('school_calendar', 'school_calendar.id', '=', 'teacher_attendances.calendar_id')
            ->join('teachers', 'teachers.id', '=', 'teacher_attendances.teacher_id')
            ->whereIn('teacher_attendances.calendar_id', $calendarIds)
            ->when($status, fn($q) => $q->where('teacher_attendances.status', $status))
            ->when($search, fn($q) => $q->where('teachers.name', 'like', "%{$search}%"))
            ->orderByDesc('school_calendar.date')
            ->orderBy('teachers.name')
            ->select('teacher_attendances.*');

        $attendances = $query->paginate(25)->withQueryString();

        // Summary counts — single query with groupBy instead of 6+1 separate queries
        $rawSummary = TeacherAttendance::whereIn('calendar_id', $calendarIds)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [];
        $summaryTotal = 0;
        foreach (['hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat'] as $s) {
            $summary[$s] = (int) ($rawSummary[$s] ?? 0);
            $summaryTotal += $summary[$s];
        }
        $summary['total'] = $summaryTotal;

        $defaultFrom = today()->startOfMonth()->toDateString();
        $defaultTo   = today()->toDateString();
        $hasFilters  = $search || $status
                    || $dateFrom !== $defaultFrom
                    || $dateTo   !== $defaultTo;

        return view('Kehadiran_Guru.index', compact(
            'attendances', 'summary',
            'dateFrom', 'dateTo', 'status', 'search', 'hasFilters'
        ));
    }

    /* ───────────────────────────────────────────────────────────
     | GET /attendance/guru/export
     |──────────────────────────────────────────────────────────*/
    public function export(Request $request)
    {
        $dateFrom = $request->get('date_from', today()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   today()->toDateString());
        $status   = $request->get('status') ?: null;

        $from = Carbon::parse($dateFrom)->format('d-m-Y');
        $to   = Carbon::parse($dateTo)->format('d-m-Y');
        $filename = "rekap-kehadiran-guru_{$from}_sd_{$to}.xlsx";

        return Excel::download(
            new TeacherAttendanceExport($dateFrom, $dateTo, $status),
            $filename
        );
    }

    /* ───────────────────────────────────────────────────────────
     | POST /attendance/guru  (manual input)
     |──────────────────────────────────────────────────────────*/
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'     => 'required|exists:teachers,id',
            'date'           => 'required|date',
            'status'         => 'required|in:hadir,sakit,izin,dinas,alpa,terlambat',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);

        $calendar = SchoolCalendar::forDate($validated['date']);
        $teacher  = Teacher::findOrFail($validated['teacher_id']);

        $checkIn  = $validated['check_in_time']
            ? Carbon::parse($validated['date'] . ' ' . $validated['check_in_time'])
            : null;
        $checkOut = $validated['check_out_time']
            ? Carbon::parse($validated['date'] . ' ' . $validated['check_out_time'])
            : null;

        TeacherAttendance::updateOrCreate(
            ['teacher_id' => $teacher->id, 'calendar_id' => $calendar->id],
            [
                'status'         => $validated['status'],
                'check_in_time'  => $checkIn,
                'check_out_time' => $checkOut,
                'source'         => 'manual',
            ]
        );

        return redirect()
            ->route('attendance.teacher.index', ['date_from' => $validated['date'], 'date_to' => $validated['date']])
            ->with('success', "Absensi {$teacher->name} berhasil disimpan.");
    }

    /* ───────────────────────────────────────────────────────────
     | PATCH /attendance/guru/{id}
     |──────────────────────────────────────────────────────────*/
    public function update(Request $request, int $id)
    {
        $request->validate([
            'status'         => 'nullable|in:hadir,sakit,izin,dinas,alpa,terlambat',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);

        $att  = TeacherAttendance::with('calendar')->findOrFail($id);
        $date = $att->calendar->date->toDateString();

        $data = ['source' => 'manual'];

        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }

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
     | DELETE /attendance/guru/{id}
     |──────────────────────────────────────────────────────────*/
    public function destroy(Request $request, int $id)
    {
        TeacherAttendance::findOrFail($id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Data absensi dihapus.');
    }

    /* ───────────────────────────────────────────────────────────
     | GET /attendance/guru/search?q=...  (AJAX — autocomplete)
     |──────────────────────────────────────────────────────────*/
    public function searchTeachers(Request $request)
    {
        $q = trim($request->get('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $teachers = Teacher::where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'name'       => $t->name,
                'nip'        => $t->nip,
                'department' => is_array($t->department) ? implode(', ', $t->department) : ($t->department ?? '–'),
            ]);

        return response()->json($teachers);
    }

    /* ───────────────────────────────────────────────────────────
     | DELETE /attendance/guru/delete-range
     |──────────────────────────────────────────────────────────*/
    public function deleteRange(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        $calendarIds = SchoolCalendar::whereBetween('date', [
            $request->date_from,
            $request->date_to,
        ])->pluck('id');

        $deleted = TeacherAttendance::whereIn('calendar_id', $calendarIds)->delete();

        return redirect()->route('attendance.teacher.index')
            ->with('success', "Berhasil menghapus {$deleted} data absensi guru.");
    }
}
