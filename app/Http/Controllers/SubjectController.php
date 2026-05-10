<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubjectController extends Controller
{
    // ─── index ───────────────────────────────────────────────────────────────

    public function index()
    {
        $subjects = Subject::with('classes')
            ->orderBy('name')
            ->get();

        // Bulk-load jumlah_kelas in 2 queries instead of 2N
        Subject::loadJumlahKelas($subjects);

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
        ], [
            'name.required'   => 'Nama mata pelajaran wajib diisi.',
            'name.unique'     => 'Nama mata pelajaran sudah terdaftar.',
            'class_ids.*.exists' => 'Ada kelas yang dipilih tetapi tidak ditemukan di sistem.',
        ]);

        try {
            $classIds = $this->resolveClasses(
                $data['rule_type'] ?? 'manual',
                $data['rule_params'] ?? [],
                $data['class_ids'] ?? []
            );
            $this->ensureAtLeastOneClassAssigned($classIds);

            $subject = DB::transaction(function () use ($data, $classIds) {
                $subject = Subject::create([
                    'name'        => $data['name'],
                    'description' => $data['description'] ?? null,
                    'is_active'   => $data['is_active'] ?? true,
                ]);

                $subject->classes()->sync($classIds);
                return $subject;
            });

            return response()->json([
                'ok'      => true,
                'message' => 'Mata pelajaran berhasil ditambahkan.',
                'subject' => $subject->load('classes'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Mata pelajaran gagal ditambahkan. Minimal 1 kelas harus ditugaskan.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            [$message, $status] = $this->resolveSubjectFailureReason($e, 'menambahkan');
            return response()->json(['ok' => false, 'message' => $message], $status);
        }
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
        ], [
            'name.required'   => 'Nama mata pelajaran wajib diisi.',
            'name.unique'     => 'Nama mata pelajaran sudah terdaftar.',
            'class_ids.*.exists' => 'Ada kelas yang dipilih tetapi tidak ditemukan di sistem.',
        ]);

        try {
            $classIds = $this->resolveClasses(
                $data['rule_type'] ?? 'manual',
                $data['rule_params'] ?? [],
                $data['class_ids'] ?? []
            );
            $this->ensureAtLeastOneClassAssigned($classIds);

            DB::transaction(function () use ($data, $subject, $classIds) {
                $subject->update([
                    'name'        => $data['name'],
                    'description' => $data['description'] ?? null,
                    'is_active'   => $data['is_active'] ?? $subject->is_active,
                ]);

                $subject->classes()->sync($classIds);
            });

            return response()->json([
                'ok'      => true,
                'message' => 'Mata pelajaran berhasil diperbarui.',
                'subject' => $subject->fresh()->load('classes'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Mata pelajaran gagal diperbarui. Minimal 1 kelas harus ditugaskan.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            [$message, $status] = $this->resolveSubjectFailureReason($e, 'memperbarui');
            return response()->json(['ok' => false, 'message' => $message], $status);
        }
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function destroy(Subject $subject): JsonResponse
    {
        try {
            DB::transaction(function () use ($subject) {
                $subject->classes()->detach();
                $subject->delete();
            });

            return response()->json(['ok' => true, 'message' => 'Mata pelajaran berhasil dihapus.']);
        } catch (Throwable $e) {
            [$message, $status] = $this->resolveSubjectFailureReason($e, 'menghapus');
            return response()->json(['ok' => false, 'message' => $message], $status);
        }
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

    private function ensureAtLeastOneClassAssigned(array $classIds): void
    {
        $normalized = array_values(array_unique(array_filter(array_map('intval', $classIds), fn ($id) => $id > 0)));

        if (count($normalized) < 1) {
            throw ValidationException::withMessages([
                'class_ids' => 'Minimal 1 kelas harus ditugaskan pada mata pelajaran.',
            ]);
        }
    }

    /**
     * Convert low-level errors into clear CRUD messages for mapel operations.
     *
     * @return array{0:string,1:int}
     */
    private function resolveSubjectFailureReason(Throwable $e, string $action): array
    {
        if ($e instanceof QueryException) {
            $sqlState   = (string) ($e->errorInfo[0] ?? $e->getCode());
            $rawMessage = strtolower($e->getMessage());

            if ($sqlState === '23000') {
                if (str_contains($rawMessage, 'subjects_name_unique') || str_contains($rawMessage, 'subjects.name')) {
                    return ['Gagal ' . $action . ' mata pelajaran: nama mapel sudah terdaftar.', 422];
                }

                if (str_contains($rawMessage, 'foreign key') || str_contains($rawMessage, 'constraint')) {
                    return ['Gagal ' . $action . ' mata pelajaran: data ini masih dipakai pada relasi lain (mis. jadwal mengajar). Lepaskan relasi terlebih dahulu.', 422];
                }

                return ['Gagal ' . $action . ' mata pelajaran: terjadi konflik data.', 422];
            }

            return ['Gagal ' . $action . ' mata pelajaran: database sedang bermasalah, silakan coba lagi.', 500];
        }

        return ['Gagal ' . $action . ' mata pelajaran: terjadi gangguan sistem.', 500];
    }
}
