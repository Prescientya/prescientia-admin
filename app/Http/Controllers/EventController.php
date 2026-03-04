<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTarget;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = Event::withCount('targets');

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                // PostgreSQL: gunakan 'ilike' untuk case-insensitive
                $q->where('title', 'ilike', "%{$s}%")
                  ->orWhere('description', 'ilike', "%{$s}%");
            });
        }

        // Filter by audience
        if ($request->filled('audience')) {
            $query->where('target_audience', $request->audience);
        }

        // Filter by status
        if ($request->filled('status')) {
            $today = now()->toDateString();
            switch ($request->status) {
                case 'aktif':
                    $query->where('release_date', '<=', $today)
                          ->where('end_date', '>=', $today);
                    break;
                case 'terjadwal':
                    $query->where('release_date', '>', $today);
                    break;
                case 'selesai':
                    $query->where('end_date', '<', $today);
                    break;
            }
        }

        $events = $query->orderByDesc('release_date')
                        ->orderByDesc('created_at')
                        ->paginate(10)
                        ->withQueryString();

        return view('Events.index', compact('events'));
    }

    /* ── CREATE (separate page) ────────────────────── */

    public function create()
    {
        $classes = ClassModel::orderBy('class')->orderBy('major')->get();

        // Distinct grades & majors for filter
        $grades = ClassModel::select('class')->distinct()->orderBy('class')->pluck('class');
        $majors = ClassModel::select('major')->distinct()->whereNotNull('major')->orderBy('major')->pluck('major');

        return view('Events.create', compact('classes', 'grades', 'majors'));
    }

    /* ── STORE ──────────────────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'nullable|string',
            'link'            => 'nullable|url|max:500',
            'release_date'    => 'required|date',
            'end_date'        => 'required|date|after_or_equal:release_date',
            'target_audience' => 'required|in:semua,guru,siswa,kelas',
            'target_classes'  => 'nullable|array',
            'target_classes.*'=> 'integer|exists:classes,id',
            'target_grades'   => 'nullable|array',
            'target_grades.*' => 'integer|in:10,11,12',
            'target_majors'   => 'nullable|array',
            'target_majors.*' => 'string|max:100',
        ], [
            'title.required'        => 'Judul event wajib diisi.',
            'release_date.required' => 'Tanggal rilis wajib diisi.',
            'end_date.required'     => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal rilis.',
            'link.url'              => 'Format link tidak valid.',
            'target_audience.required' => 'Target audiens wajib dipilih.',
        ]);

        try {
            DB::beginTransaction();

            $event = Event::create([
                'title'           => $request->title,
                'description'     => $request->description,
                'link'            => $request->link,
                'release_date'    => $request->release_date,
                'end_date'        => $request->end_date,
                'target_audience' => $request->target_audience,
            ]);

            // Save targets if audience is 'kelas'
            if ($request->target_audience === 'kelas') {
                $this->saveTargets($event, $request);
            }

            DB::commit();

            return redirect()->route('events.index')
                ->with('success', "Event \"{$event->title}\" berhasil ditambahkan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menyimpan event: ' . $e->getMessage());
        }
    }

    /* ── SHOW (JSON for detail modal) ──────────────── */

    public function show(Event $event)
    {
        $event->load('targets.targetClass');

        return response()->json([
            'id'              => $event->id,
            'title'           => $event->title,
            'description'     => $event->description,
            'link'            => $event->link,
            'release_date'    => $event->release_date->format('d M Y'),
            'end_date'        => $event->end_date->format('d M Y'),
            'target_audience' => $event->audience_label,
            'status'          => $event->status_label,
            'targets'         => $event->targets->map(fn($t) => $t->target_label)->toArray(),
            'created_at'      => $event->created_at->format('d M Y H:i'),
        ]);
    }

    /* ── EDIT (separate page) ──────────────────────── */

    public function edit(Event $event)
    {
        $event->load('targets');

        $classes = ClassModel::orderBy('class')->orderBy('major')->get();
        $grades  = ClassModel::select('class')->distinct()->orderBy('class')->pluck('class');
        $majors  = ClassModel::select('major')->distinct()->whereNotNull('major')->orderBy('major')->pluck('major');

        // Get existing target IDs for pre-selection
        $selectedClasses = $event->targets->whereNotNull('class_id')->pluck('class_id')->toArray();
        $selectedGrades  = $event->targets->whereNotNull('grade')->pluck('grade')->unique()->toArray();
        $selectedMajors  = $event->targets->whereNotNull('major')->pluck('major')->unique()->toArray();

        return view('Events.edit', compact(
            'event', 'classes', 'grades', 'majors',
            'selectedClasses', 'selectedGrades', 'selectedMajors'
        ));
    }

    /* ── UPDATE ─────────────────────────────────────── */

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'nullable|string',
            'link'            => 'nullable|url|max:500',
            'release_date'    => 'required|date',
            'end_date'        => 'required|date|after_or_equal:release_date',
            'target_audience' => 'required|in:semua,guru,siswa,kelas',
            'target_classes'  => 'nullable|array',
            'target_classes.*'=> 'integer|exists:classes,id',
            'target_grades'   => 'nullable|array',
            'target_grades.*' => 'integer|in:10,11,12',
            'target_majors'   => 'nullable|array',
            'target_majors.*' => 'string|max:100',
        ], [
            'title.required'        => 'Judul event wajib diisi.',
            'release_date.required' => 'Tanggal rilis wajib diisi.',
            'end_date.required'     => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal rilis.',
            'link.url'              => 'Format link tidak valid.',
        ]);

        try {
            DB::beginTransaction();

            $event->update([
                'title'           => $request->title,
                'description'     => $request->description,
                'link'            => $request->link,
                'release_date'    => $request->release_date,
                'end_date'        => $request->end_date,
                'target_audience' => $request->target_audience,
            ]);

            // Remove old targets
            $event->targets()->delete();

            // Re-save if audience is 'kelas'
            if ($request->target_audience === 'kelas') {
                $this->saveTargets($event, $request);
            }

            DB::commit();

            return redirect()->route('events.index')
                ->with('success', "Event \"{$event->title}\" berhasil diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui event: ' . $e->getMessage());
        }
    }

    /* ── DESTROY ────────────────────────────────────── */

    public function destroy(Event $event)
    {
        try {
            DB::beginTransaction();

            $title = $event->title;
            $event->targets()->delete();
            $event->delete();

            DB::commit();

            return back()->with('success', "Event \"{$title}\" berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus event: ' . $e->getMessage());
        }
    }

    /* ── PRIVATE: Save event targets ───────────────── */

    private function saveTargets(Event $event, Request $request): void
    {
        // Specific classes
        if ($request->filled('target_classes')) {
            foreach ($request->target_classes as $classId) {
                EventTarget::create([
                    'event_id' => $event->id,
                    'class_id' => $classId,
                ]);
            }
        }

        // Grade-level targets (e.g., all of grade 10)
        if ($request->filled('target_grades')) {
            foreach ($request->target_grades as $grade) {
                EventTarget::create([
                    'event_id' => $event->id,
                    'grade'    => $grade,
                ]);
            }
        }

        // Major-level targets (e.g., all RPL classes)
        if ($request->filled('target_majors')) {
            foreach ($request->target_majors as $major) {
                EventTarget::create([
                    'event_id' => $event->id,
                    'major'    => $major,
                ]);
            }
        }
    }
}
