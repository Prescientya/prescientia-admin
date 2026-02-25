<?php

namespace App\Http\Controllers;

use App\Models\ClassPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassPeriodController extends Controller
{
    /* ── INDEX ─────────────────────────────────────────── */

    public function index()
    {
        $days = ClassPeriod::DAYS;
        $grouped = [];
        foreach ($days as $day) {
            $grouped[$day] = ClassPeriod::forDay($day)->get();
        }
        return view('Jam_Pelajaran.index', compact('grouped'));
    }

    /* ── STORE ─────────────────────────────────────────── */

    public function store(Request $request)
    {
        $data = $this->validatePeriod($request);

        // Auto-calculate duration
        $data['duration_minutes'] = $this->calcDuration($data['start_time'], $data['end_time']);

        // Shift sequences down to make room for insertion
        $this->shiftSequences($data['day'], $data['sequence'], 1);

        ClassPeriod::create($data);

        if ($request->expectsJson()) {
            return $this->jsonDayResponse($data['day']);
        }
        return back()->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }

    /* ── UPDATE ─────────────────────────────────────────── */

    public function update(Request $request, ClassPeriod $jamPelajaran)
    {
        $data = $this->validatePeriod($request, $jamPelajaran->id);
        $data['duration_minutes'] = $this->calcDuration($data['start_time'], $data['end_time']);

        $jamPelajaran->update($data);

        if ($request->expectsJson()) {
            return $this->jsonDayResponse($data['day']);
        }
        return back()->with('success', 'Jam pelajaran berhasil diperbarui.');
    }

    /* ── DESTROY ────────────────────────────────────────── */

    public function destroy(Request $request, ClassPeriod $jamPelajaran)
    {
        $day = $jamPelajaran->day;
        $jamPelajaran->delete();

        // Re-sequence remaining rows
        $this->resequence($day);

        if ($request->expectsJson()) {
            return $this->jsonDayResponse($day);
        }
        return back()->with('success', 'Jam pelajaran berhasil dihapus.');
    }

    /* ── RESET (re-seed satu hari) ──────────────────────── */

    public function reset(Request $request)
    {
        $request->validate(['day' => ['required', Rule::in(ClassPeriod::DAYS)]]);
        $day = $request->day;

        DB::transaction(function () use ($day) {
            ClassPeriod::where('day', $day)->delete();
            $seeder = new \Database\Seeders\ClassPeriodSeeder;
            // Re-insert only one day using the seeder map
            $rows = $seeder->rowsForDay($day);
            ClassPeriod::insert($rows);
        });

        if ($request->expectsJson()) {
            return $this->jsonDayResponse($day);
        }
        return back()->with('success', 'Jadwal hari ' . ClassPeriod::DAY_LABELS[$day] . ' berhasil direset.');
    }

    /* ── DAY TABLE (AJAX partial HTML) ─────────────────── */

    public function dayTable(Request $request, string $day)
    {
        if (!in_array($day, ClassPeriod::DAYS)) {
            return response()->json(['message' => 'Hari tidak valid'], 422);
        }

        $periods = ClassPeriod::forDay($day)->get();
        $html = view('Jam_Pelajaran._table', compact('periods', 'day'))->render();
        $lessonCount = $periods->where('activity_type', 'lesson')->count();

        return response()->json(['html' => $html, 'day' => $day, 'lesson_count' => $lessonCount]);
    }

    /* ── HELPERS ────────────────────────────────────────── */

    private function validatePeriod(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'day'           => ['required', Rule::in(ClassPeriod::DAYS)],
            'sequence'      => [
                'required', 'integer', 'min:0', 'max:30',
                Rule::unique('class_periods', 'sequence')
                    ->where('day', $request->day)
                    ->ignore($ignoreId),
            ],
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'activity_type' => ['required', Rule::in(array_keys(ClassPeriod::ACTIVITY_TYPES))],
            'note'          => 'nullable|string|max:200',
        ], [
            'sequence.unique'   => 'Urutan jam ini sudah ada di hari tersebut.',
            'end_time.after'    => 'Waktu selesai harus setelah waktu mulai.',
        ]);
    }

    private function calcDuration(string $start, string $end): int
    {
        [$sh, $sm] = explode(':', $start);
        [$eh, $em] = explode(':', $end);
        return ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
    }

    private function shiftSequences(string $day, int $fromSeq, int $offset): void
    {
        ClassPeriod::where('day', $day)
            ->where('sequence', '>=', $fromSeq)
            ->orderBy('sequence', 'desc')
            ->each(fn($p) => $p->update(['sequence' => $p->sequence + $offset]));
    }

    private function resequence(string $day): void
    {
        ClassPeriod::where('day', $day)->orderBy('sequence')->get()
            ->each(fn($p, $i) => $p->sequence !== $i ? $p->update(['sequence' => $i]) : null);
    }

    private function jsonDayResponse(string $day): \Illuminate\Http\JsonResponse
    {
        $periods = ClassPeriod::forDay($day)->get()->map(fn($p) => [
            'id'             => $p->id,
            'sequence'       => $p->sequence,
            'start_time'     => substr($p->start_time, 0, 5),
            'end_time'       => substr($p->end_time, 0, 5),
            'duration_minutes' => $p->duration_minutes,
            'activity_type'  => $p->activity_type,
            'activity_label' => $p->activity_label,
            'note'           => $p->note,
            'is_lesson'      => $p->is_lesson,
        ]);

        $lessonCount = $periods->where('is_lesson', true)->count();

        return response()->json([
            'success' => true,
            'day'     => $day,
            'periods' => $periods,
            'lesson_count' => $lessonCount,
        ]);
    }
}
