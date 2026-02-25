<?php

namespace App\Http\Controllers;

use App\Exports\TeacherScheduleTemplateExport;
use App\Imports\TeacherScheduleImport;
use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class TeacherScheduleController extends Controller
{
    /* ── INDEX ──────────────────────────────────────────────── */

    public function index()
    {
        $teachers = Teacher::orderBy('name')->get(['id', 'name', 'department']);
        $classes  = ClassModel::orderBy('class')->orderBy('major')->get(['id', 'class', 'major']);
        $subjects = Subject::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $periods  = ClassPeriod::where('activity_type', 'lesson')
                        ->orderBy('day')->orderBy('sequence')
                        ->get(['id', 'day', 'sequence', 'start_time', 'end_time']);

        // Pre-grouped periods by day for the view
        $periodsByDay = $periods->groupBy('day');

        return view('Jadwal_Mengajar.index', compact(
            'teachers', 'classes', 'subjects', 'periodsByDay'
        ));
    }

    /* ── FETCH SCHEDULE (AJAX) ──────────────────────────────── */

    /**
     * GET /jadwal-mengajar/schedule?mode=teacher|class&id=X
     * Returns rendered HTML for the schedule grid.
     */
    public function fetchSchedule(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:teacher,class',
            'id'   => 'required|integer',
        ]);

        $mode = $request->mode;
        $id   = $request->id;

        $schedules = TeacherSchedule::with(['teacher', 'subject', 'schoolClass', 'classPeriod'])
            ->when($mode === 'teacher', fn($q) => $q->where('teacher_id', $id))
            ->when($mode === 'class',   fn($q) => $q->where('class_id',   $id))
            ->get();

        // All lesson periods grouped by day
        $periodsByDay = ClassPeriod::where('activity_type', 'lesson')
            ->orderBy('day')->orderBy('sequence')
            ->get()
            ->groupBy('day');

        // All class_periods (lesson+break) for display
        $allPeriodsByDay = ClassPeriod::orderBy('day')->orderBy('sequence')
            ->get()->groupBy('day');

        // Map: class_period_id → schedule
        $byPeriod = $schedules->keyBy('class_period_id');

        $html = view('Jadwal_Mengajar._grid', compact(
            'mode', 'id', 'schedules', 'allPeriodsByDay', 'byPeriod'
        ))->render();

        return response()->json(['html' => $html]);
    }

    /* ── STORE ──────────────────────────────────────────────── */

    public function store(Request $request)
    {
        $data = $this->validateSchedule($request);

        $conflict = $this->checkConflict($data['teacher_id'], $data['class_id'], $data['class_period_id']);
        if ($conflict) {
            return response()->json(['message' => $conflict], 422);
        }

        $schedule = TeacherSchedule::create($data);
        $schedule->load(['teacher', 'subject', 'schoolClass', 'classPeriod']);

        return response()->json([
            'success'  => true,
            'schedule' => $this->formatSchedule($schedule),
        ], 201);
    }

    /* ── UPDATE ─────────────────────────────────────────────── */

    public function update(Request $request, TeacherSchedule $jadwalMengajar)
    {
        $data = $this->validateSchedule($request, $jadwalMengajar->id);

        $conflict = $this->checkConflict(
            $data['teacher_id'], $data['class_id'], $data['class_period_id'],
            $jadwalMengajar->id
        );
        if ($conflict) {
            return response()->json(['message' => $conflict], 422);
        }

        $jadwalMengajar->update($data);
        $jadwalMengajar->load(['teacher', 'subject', 'schoolClass', 'classPeriod']);

        return response()->json([
            'success'  => true,
            'schedule' => $this->formatSchedule($jadwalMengajar),
        ]);
    }

    /* ── DESTROY ────────────────────────────────────────────── */

    public function destroy(TeacherSchedule $jadwalMengajar)
    {
        $jadwalMengajar->delete();
        return response()->json(['success' => true]);
    }

    /* ── AVAILABLE PERIODS (AJAX) ───────────────────────────── */

    /**
     * GET /jadwal-mengajar/available-periods?teacher_id=X&class_id=Y[&ignore_id=Z]
     * Returns lesson periods not yet taken by this teacher or class.
     */
    public function availablePeriods(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer',
            'class_id'   => 'required|integer',
            'ignore_id'  => 'nullable|integer',
        ]);

        $ignoreId = $request->ignore_id;

        // Periods already occupied by this teacher (on any class)
        $teacherBusy = TeacherSchedule::where('teacher_id', $request->teacher_id)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->pluck('class_period_id');

        // Periods already occupied by this class (any teacher)
        $classBusy = TeacherSchedule::where('class_id', $request->class_id)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->pluck('class_period_id');

        $busyIds = $teacherBusy->merge($classBusy)->unique();

        $available = ClassPeriod::where('activity_type', 'lesson')
            ->whereNotIn('id', $busyIds)
            ->orderBy('day')->orderBy('sequence')
            ->get(['id', 'day', 'sequence', 'start_time', 'end_time'])
            ->groupBy('day')
            ->map(fn($g) => $g->map(fn($p) => [
                'id'         => $p->id,
                'sequence'   => $p->sequence,
                'start_time' => substr($p->start_time, 0, 5),
                'end_time'   => substr($p->end_time, 0, 5),
                'label'      => "Jam {$p->sequence} · " . substr($p->start_time, 0, 5) . '–' . substr($p->end_time, 0, 5),
            ])->values());

        return response()->json(['days' => $available, 'days_order' => ClassPeriod::DAYS]);
    }

    /* ── HELPERS ────────────────────────────────────────────── */

    private function validateSchedule(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'teacher_id'      => 'required|exists:teachers,id',
            'subject_id'      => 'required|exists:subjects,id',
            'class_id'        => 'required|exists:classes,id',
            'class_period_id' => [
                'required', 'exists:class_periods,id',
                Rule::unique('teacher_schedules', 'class_period_id')
                    ->where('class_id', $request->class_id)
                    ->ignore($ignoreId),
            ],
        ], [
            'class_period_id.unique' => 'Kelas ini sudah memiliki pelajaran di slot jam tersebut.',
        ]);
    }

    private function checkConflict(int $teacherId, int $classId, int $periodId, ?int $ignoreId = null): ?string
    {
        $teacherConflict = TeacherSchedule::where('teacher_id', $teacherId)
            ->where('class_period_id', $periodId)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->with(['schoolClass', 'subject'])
            ->first();

        if ($teacherConflict) {
            $kelas = $teacherConflict->schoolClass->full_name ?? '?';
            $mapel = $teacherConflict->subject->name ?? '?';
            return "Guru ini sudah mengajar {$mapel} di {$kelas} pada slot jam yang sama.";
        }

        return null;
    }

    private function formatSchedule(TeacherSchedule $s): array
    {
        $p = $s->classPeriod;
        return [
            'id'           => $s->id,
            'teacher_id'   => $s->teacher_id,
            'teacher_name' => $s->teacher->name,
            'subject_id'   => $s->subject_id,
            'subject_name' => $s->subject->name,
            'class_id'     => $s->class_id,
            'class_name'   => $s->schoolClass->full_name,
            'class_short'  => $s->schoolClass->short_name,
            'period_id'    => $s->class_period_id,
            'day'          => $p->day,
            'day_label'    => $p->day_label,
            'sequence'     => $p->sequence,
            'start_time'   => substr($p->start_time, 0, 5),
            'end_time'     => substr($p->end_time, 0, 5),
        ];
    }

    /* ── IMPORT EXCEL ───────────────────────────────────────── */

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $import = new TeacherScheduleImport;
            Excel::import($import, $request->file('file'));
            $count  = $import->getImportedCount();
            $failed = $import->getFailedRows();

            if (count($failed) > 0) {
                return redirect()->route('jadwal-mengajar.index')
                    ->with('import_failed', $failed)
                    ->with('import_success_count', $count)
                    ->with('import_type', 'jadwal')
                    ->with('success', $count > 0 ? "Berhasil mengimpor {$count} jadwal mengajar." : null);
            }

            if ($count === 0) {
                return redirect()->route('jadwal-mengajar.index')
                    ->with('error', 'Tidak ada jadwal baru yang berhasil diimpor. Pastikan format file sesuai template.');
            }

            return redirect()->route('jadwal-mengajar.index')
                ->with('success', "Berhasil mengimpor {$count} jadwal mengajar.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /* ── DOWNLOAD TEMPLATE ──────────────────────────────────── */

    public function downloadTemplate()
    {
        return Excel::download(
            new TeacherScheduleTemplateExport,
            'template_jadwal_mengajar.xlsx'
        );
    }
}
