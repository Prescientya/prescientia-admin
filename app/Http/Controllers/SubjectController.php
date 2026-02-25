<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // ─── index ───────────────────────────────────────────────────────────────

    public function index()
    {
        $subjects = Subject::with('classes')
            ->orderBy('name')
            ->get();

        // group all classes for the modal selectors
        $allClasses = ClassModel::orderBy('class')
            ->orderBy('major')
            ->get(['id', 'class', 'major']);

        $grouped = $allClasses->groupBy('class');          // [10 => [...], 11 => [...], 12 => [...]]
        $majors  = $allClasses->pluck('major')
            ->map(fn($m) => preg_replace('/\s+\d+$/', '', $m))  // strip trailing number → base jurusan
            ->unique()
            ->sort()
            ->values();

        return view('Mapel.index', compact('subjects', 'allClasses', 'grouped', 'majors'));
    }

    // ─── store ────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:subjects,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
            'rule_type'   => 'nullable|string|in:semua,tingkat,jurusan,tingkat_jurusan,manual',
            'rule_params' => 'nullable|array',
            'class_ids'   => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $subject = Subject::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        $classIds = $this->resolveClasses(
            $data['rule_type'] ?? 'manual',
            $data['rule_params'] ?? [],
            $data['class_ids'] ?? []
        );

        $subject->classes()->sync($classIds);

        return response()->json([
            'ok'      => true,
            'message' => 'Mata pelajaran berhasil ditambahkan.',
            'subject' => $subject->load('classes'),
        ]);
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Subject $subject): JsonResponse
    {
        $data = $request->validate([
            'name'        => "required|string|max:100|unique:subjects,name,{$subject->id}",
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
            'rule_type'   => 'nullable|string|in:semua,tingkat,jurusan,tingkat_jurusan,manual',
            'rule_params' => 'nullable|array',
            'class_ids'   => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $subject->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? $subject->is_active,
        ]);

        $classIds = $this->resolveClasses(
            $data['rule_type'] ?? 'manual',
            $data['rule_params'] ?? [],
            $data['class_ids'] ?? []
        );

        $subject->classes()->sync($classIds);

        return response()->json([
            'ok'      => true,
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'subject' => $subject->fresh()->load('classes'),
        ]);
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->classes()->detach();
        $subject->delete();

        return response()->json(['ok' => true, 'message' => 'Mata pelajaran berhasil dihapus.']);
    }

    // ─── classOptions (for edit modal pre-load) ───────────────────────────────

    public function classOptions(Subject $subject): JsonResponse
    {
        return response()->json([
            'ok'       => true,
            'classIds' => $subject->classes()->pluck('classes.id'),
        ]);
    }

    // ─── resolveClasses ───────────────────────────────────────────────────────

    /**
     * Returns an array of class IDs based on the chosen rule type.
     *
     * rule_type values:
     *   semua            → all classes
     *   tingkat          → rule_params['grades'] = [10,11,12]  (any subset)
     *   jurusan          → rule_params['majors'] = ['AKL','DKV',...]
     *   tingkat_jurusan  → rule_params['grades'] + rule_params['majors']
     *   manual           → class_ids (explicit)
     */
    private function resolveClasses(string $type, array $params, array $manualIds): array
    {
        $query = ClassModel::query();

        switch ($type) {
            case 'semua':
                return $query->pluck('id')->all();

            case 'tingkat':
                $grades = array_map('intval', $params['grades'] ?? []);
                if (empty($grades)) return [];
                return $query->whereIn('class', $grades)->pluck('id')->all();

            case 'jurusan':
                $majors = $params['majors'] ?? [];
                if (empty($majors)) return [];
                // "AKL" should match "AKL 1", "AKL 2", etc.
                $query->where(function ($q) use ($majors) {
                    foreach ($majors as $m) {
                        $q->orWhere('major', 'like', $m . '%');
                    }
                });
                return $query->pluck('id')->all();

            case 'tingkat_jurusan':
                $grades = array_map('intval', $params['grades'] ?? []);
                $majors = $params['majors'] ?? [];
                if (empty($grades) || empty($majors)) return [];
                $query->whereIn('class', $grades);
                $query->where(function ($q) use ($majors) {
                    foreach ($majors as $m) {
                        $q->orWhere('major', 'like', $m . '%');
                    }
                });
                return $query->pluck('id')->all();

            case 'manual':
            default:
                return array_map('intval', $manualIds);
        }
    }
}
