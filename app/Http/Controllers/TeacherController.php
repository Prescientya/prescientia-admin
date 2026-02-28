<?php

namespace App\Http\Controllers;

use App\Exports\TeacherTemplateExport;
use App\Imports\TeachersImport;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'subjects']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('teachers.name', 'ilike', "%{$s}%")
                  ->orWhere('teachers.nip', 'ilike', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'ilike', "%{$s}%"));
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('subjects', fn ($q) => $q->where('subjects.id', $request->subject_id));
        }

        $teachers = $query->orderBy('teachers.name')->paginate(10)->withQueryString();
        $subjects  = Subject::orderBy('name')->get();

        return view('Data_Guru.index', compact('teachers', 'subjects'));
    }

    /* ── STORE ──────────────────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'nip'           => 'required|string|max:20|unique:teachers,nip',
            'email'         => 'required|email|max:100|unique:users,email',
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mapel_text'    => 'nullable|string|max:500',
        ], [
            'nip.unique'   => 'NIP sudah terdaftar.',
            'email.unique' => 'Email sudah digunakan.',
        ]);

        // Validate mapel names against subjects table BEFORE touching the DB
        $resolved = $this->resolveSubjectIds($request->mapel_text ?? '');
        if (!empty($resolved['notFound'])) {
            $list = collect($resolved['notFound'])->map(fn($n) => "\"$n\"")->implode(', ');
            return back()->withInput()->withErrors([
                'mapel_text' => "Mapel {$list} tidak ditemukan di sistem sekolah ini.",
            ]);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'email'     => $request->email,
                'password'  => Hash::make($request->nip), // default password = NIP
                'role'      => 'teacher',
                'is_active' => true,
            ]);

            $photoPath = null;
            if ($request->hasFile('photo_profile')) {
                $photoPath = $request->file('photo_profile')->store('teachers', 'public');
            }

            $teacher = Teacher::create([
                'user_id'       => $user->id,
                'nip'           => $request->nip,
                'name'          => $request->name,
                'gender'        => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number'  => $request->phone_number,
                'address'       => $request->address,
                'photo_profile' => $photoPath,
            ]);

            // Sync subjects from mapel_text
            if (!empty($resolved['ids'])) {
                $teacher->subjects()->sync($resolved['ids']);
            }

            DB::commit();
            Cache::forget('dashboard.total_guru');
            return redirect()->route('guru.index')
                ->with('success', "Guru {$request->name} berhasil ditambahkan.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Gagal menambahkan guru: ' . $e->getMessage());
        }
    }

    /* ── SHOW (JSON for detail modal) ───────────────── */

    public function show(Teacher $guru)
    {
        $guru->load(['user', 'subjects']);

        return response()->json([
            'id'            => $guru->id,
            'name'          => $guru->name,
            'nip'           => $guru->nip,
            'email'         => optional($guru->user)->email,
            'gender'        => $guru->gender,
            'gender_label'  => $guru->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'date_of_birth' => $guru->date_of_birth?->format('d-m-Y'),
            'phone_number'  => $guru->phone_number,
            'address'       => $guru->address,
            'subjects'      => $guru->subjects->pluck('name')->values(),
            'photo_url'     => $guru->photo_profile
                                    ? Storage::url($guru->photo_profile)
                                    : null,
            'is_active'     => (bool) optional($guru->user)->is_active,
            'created_at'    => $guru->created_at?->format('d-m-Y'),
        ]);
    }

    /* ── EDIT ───────────────────────────────────────── */

    public function edit(Teacher $guru)
    {
        $guru->load(['user', 'subjects']);
        $subjects    = Subject::orderBy('name')->get();
        $assignedIds = $guru->subjects->pluck('id')->toArray();
        return view('Data_Guru.edit', compact('guru', 'subjects', 'assignedIds'));
    }

    /* ── UPDATE ─────────────────────────────────────── */

    public function update(Request $request, Teacher $guru)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'nip'           => 'required|string|max:20|unique:teachers,nip,' . $guru->id,
            'email'         => 'required|email|max:100|unique:users,email,' . $guru->user_id,
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'password'      => 'nullable|string|min:8',
            'is_active'     => 'nullable|boolean',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mapel_text'    => 'nullable|string|max:500',
        ], [
            'nip.unique'   => 'NIP sudah digunakan guru lain.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        // Validate mapel names against subjects table BEFORE touching the DB
        $resolved = $this->resolveSubjectIds($request->mapel_text ?? '');
        if (!empty($resolved['notFound'])) {
            $list = collect($resolved['notFound'])->map(fn($n) => "\"$n\"")->implode(', ');
            return back()->withInput()->withErrors([
                'mapel_text' => "Mapel {$list} tidak ditemukan di sistem sekolah ini.",
            ]);
        }

        DB::beginTransaction();
        try {
            $userUpdate = [
                'email'     => $request->email,
                'is_active' => $request->boolean('is_active', true),
            ];
            if ($request->filled('password')) {
                $userUpdate['password'] = Hash::make($request->password);
            }
            $guru->user->update($userUpdate);

            $photoPath = $guru->photo_profile;
            if ($request->hasFile('photo_profile')) {
                if ($photoPath) Storage::disk('public')->delete($photoPath);
                $photoPath = $request->file('photo_profile')->store('teachers', 'public');
            }

            $guru->update([
                'nip'           => $request->nip,
                'name'          => $request->name,
                'gender'        => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number'  => $request->phone_number,
                'address'       => $request->address,
                'photo_profile' => $photoPath,
            ]);

            // Sync subjects (empty = detach all)
            $guru->subjects()->sync($resolved['ids']);

            DB::commit();
            return redirect()->route('guru.index')
                ->with('success', "Data guru {$guru->name} berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data guru: ' . $e->getMessage());
        }
    }

    /* ── DESTROY ────────────────────────────────────── */

    public function destroy(Teacher $guru)
    {
        DB::beginTransaction();
        try {
            $name = $guru->name;
            $user = $guru->user;
            $guru->delete();               // hard delete (teacher_subject cascades)
            optional($user)->delete();     // hard delete user account

            DB::commit();
            Cache::forget('dashboard.total_guru');
            return redirect()->route('guru.index')
                ->with('success', "Data guru {$name} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }

    /* ── DOWNLOAD TEMPLATE ──────────────────────────── */

    public function downloadTemplate()
    {
        return Excel::download(new TeacherTemplateExport, 'template_import_guru.xlsx');
    }

    /* ── IMPORT EXCEL ───────────────────────────────── */

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
            $import = new TeachersImport;
            Excel::import($import, $request->file('file'));
            $count  = $import->getImportedCount();
            $failed = $import->getFailedRows();

            if (count($failed) > 0) {
                return redirect()->route('guru.index')
                    ->with('import_failed', $failed)
                    ->with('import_success_count', $count)
                    ->with('import_type', 'guru')
                    ->with('success', $count > 0 ? "Berhasil mengimpor {$count} data guru." : null);
            }

            if ($count === 0) {
                return redirect()->route('guru.index')
                    ->with('error', 'Tidak ada data guru baru yang berhasil diimpor. Pastikan format file sesuai template.');
            }

            return redirect()->route('guru.index')
                ->with('success', "Berhasil mengimpor {$count} data guru.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /* ── PRIVATE HELPER ─────────────────────────────── */

    /**
     * Lookup subjects by name (case-insensitive). Does NOT create new subjects.
     * Returns ['ids' => [...], 'notFound' => [...names not in DB]]
     */
    private function resolveSubjectIds(string $mapelText): array
    {
        if (empty(trim($mapelText))) return ['ids' => [], 'notFound' => []];

        $names    = array_filter(array_map('trim', explode(',', $mapelText)));
        $ids      = [];
        $notFound = [];

        foreach ($names as $name) {
            if (empty($name)) continue;
            $subject = Subject::whereRaw('LOWER(name) = LOWER(?)', [$name])->first();
            if ($subject) {
                $ids[] = $subject->id;
            } else {
                $notFound[] = $name;
            }
        }

        return ['ids' => $ids, 'notFound' => $notFound];
    }
}
