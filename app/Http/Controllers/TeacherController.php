<?php

namespace App\Http\Controllers;

use App\Exports\TeacherTemplateExport;
use App\Imports\TeachersImport;
use App\Models\ClassModel;
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
                $q->where('teachers.name', 'like', "%{$s}%")
                  ->orWhere('teachers.nip', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$s}%"));
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
            'name'          => 'required|string|max:50',
            'nip'           => 'required|string|max:25|unique:teachers,nip',
            'email'         => 'required|email|max:50|unique:users,email',
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mapel_text'    => 'nullable|string|max:500',
        ], [
            'name.max'     => 'Nama maksimal 50 karakter.',
            'nip.max'      => 'NIP maksimal 25 karakter.',
            'nip.unique'   => 'NIP sudah terdaftar.',
            'email.max'    => 'Email maksimal 50 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'phone_number.max' => 'Nomor HP maksimal 20 karakter.',
            'address.max'  => 'Alamat maksimal 500 karakter.',
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
                'email'       => $request->email,
                'password'    => Hash::make($request->nip), // default password = NIP, wajib diganti saat login pertama
                'role'        => 'teacher',
                'is_active'   => true,
                'first_login' => true, // paksa ganti password saat login pertama via Flutter app
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
        $homeroomClass = ClassModel::where('homeroom_teacher_id', $guru->id)->first();

        return response()->json([
            'id'              => $guru->id,
            'name'            => $guru->name,
            'nip'             => $guru->nip,
            'email'           => optional($guru->user)->email,
            'gender'          => $guru->gender,
            'gender_label'    => $guru->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'date_of_birth'   => $guru->date_of_birth?->format('d-m-Y'),
            'phone_number'    => $guru->phone_number,
            'address'         => $guru->address,
            'subjects'        => $guru->subjects->pluck('name')->values(),
            'photo_url'       => $guru->photo_profile
                                      ? Storage::url($guru->photo_profile)
                                      : null,
            'is_active'       => (bool) optional($guru->user)->is_active,
            'created_at'      => $guru->created_at?->format('d-m-Y'),
            'teacher_role'    => $homeroomClass ? 'walikelas' : 'pengajar',
            'homeroom_class'  => $homeroomClass ? $homeroomClass->full_name : null,
        ]);
    }

    /* ── EDIT ───────────────────────────────────────── */

    public function edit(Teacher $guru)
    {
        $guru->load(['user', 'subjects']);
        $subjects      = Subject::orderBy('name')->get();
        $assignedIds   = $guru->subjects->pluck('id')->toArray();
        $classes       = ClassModel::orderByRaw("class, major")->get();
        $homeroomClass = ClassModel::where('homeroom_teacher_id', $guru->id)->first();
        return view('Data_Guru.edit', compact('guru', 'subjects', 'assignedIds', 'classes', 'homeroomClass'));
    }

    /* ── UPDATE ─────────────────────────────────────── */

    public function update(Request $request, Teacher $guru)
    {
        $request->validate([
            'name'             => 'required|string|max:50',
            'nip'              => 'required|string|max:25|unique:teachers,nip,' . $guru->id,
            'email'            => 'required|email|max:50|unique:users,email,' . $guru->user_id,
            'gender'           => 'required|in:L,P',
            'date_of_birth'    => 'required|date',
            'phone_number'     => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'password'         => 'nullable|string|min:8',
            'is_active'        => 'nullable|boolean',
            'photo_profile'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mapel_text'       => 'nullable|string|max:500',
            'teacher_role'     => 'required|in:pengajar,walikelas',
            'homeroom_class_id'=> 'nullable|integer|exists:classes,id',
        ], [
            'name.max'                => 'Nama maksimal 50 karakter.',
            'nip.max'                 => 'NIP maksimal 25 karakter.',
            'nip.unique'              => 'NIP sudah digunakan guru lain.',
            'email.max'               => 'Email maksimal 50 karakter.',
            'email.unique'            => 'Email sudah digunakan akun lain.',
            'phone_number.max'        => 'Nomor HP maksimal 20 karakter.',
            'address.max'             => 'Alamat maksimal 500 karakter.',
            'password.min'            => 'Password minimal 8 karakter.',
            'homeroom_class_id.exists'=> 'Kelas yang dipilih tidak ditemukan.',
        ]);

        if ($request->teacher_role === 'walikelas' && !$request->filled('homeroom_class_id')) {
            return back()->withInput()->withErrors(['homeroom_class_id' => 'Pilih kelas yang di-walii.']);
        }

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

            // ── Homeroom class assignment ─────────────────────
            // First, remove this teacher from any class they currently wali
            ClassModel::where('homeroom_teacher_id', $guru->id)
                      ->update(['homeroom_teacher_id' => null]);

            // Sync teacher_class_roles: remove old wali_kelas role(s) for this teacher
            DB::table('teacher_class_roles')
                ->where('teacher_id', $guru->id)
                ->where('role', 'wali_kelas')
                ->delete();

            if ($request->teacher_role === 'walikelas' && $request->filled('homeroom_class_id')) {
                // If the target class already has a different homeroom teacher, clear it first
                ClassModel::where('id', $request->homeroom_class_id)
                          ->update(['homeroom_teacher_id' => $guru->id]);

                // Also insert into teacher_class_roles so the BE login API picks up the role
                DB::table('teacher_class_roles')->updateOrInsert(
                    [
                        'teacher_id' => $guru->id,
                        'class_id'   => $request->homeroom_class_id,
                    ],
                    [
                        'role'       => 'wali_kelas',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

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
            $name  = $guru->name;
            $photo = $guru->photo_profile;
            $user  = $guru->user; // load relasi sebelum dihapus

            if ($user) {
                // Hapus user → teachers otomatis CASCADE terhapus via FK DB
                // (teachers.user_id → users ON DELETE CASCADE)
                // termasuk child tables: teacher_schedules, teacher_attendances, dst.
                $user->delete();
            } else {
                // Edge case: guru tidak punya user (data lama/rusak)
                $guru->delete();
            }

            // Hapus foto profil jika ada
            if ($photo) {
                Storage::disk('public')->delete($photo);
            }

            DB::commit();
            Cache::forget('dashboard.total_guru');
            return redirect()->route('guru.index')
                ->with('success', "Data guru {$name} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }

    public function resetPassword(Teacher $guru)
    {
        try {
            DB::table('users')
                ->where('id', $guru->user_id)
                ->update([
                    'password' => Hash::make($guru->nip),
                    'first_login' => true,
                    'updated_at' => now(),
                ]);

            return redirect()->route('guru.index')
                ->with('success', "Password guru {$guru->name} berhasil direset ke NIP.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset password guru: ' . $e->getMessage());
        }
    }

    /* ── CHECK SUBJECTS (AJAX for Excel import) ─────── */

    public function checkSubjects(Request $request): \Illuminate\Http\JsonResponse
    {
        $names   = $request->input('subjects', []);
        $missing = [];

        foreach ($names as $name) {
            $name = trim((string) $name);
            if (empty($name)) continue;
            $exists = Subject::whereRaw('LOWER(name) = LOWER(?)', [$name])->exists();
            if (!$exists) {
                $missing[] = ['name' => $name, 'label' => $name];
            }
        }

        return response()->json(['missing' => $missing]);
    }

    /* ── DOWNLOAD TEMPLATE ──────────────────────────── */

    public function downloadTemplate()
    {
        return Excel::download(new TeacherTemplateExport, 'template_import_guru.xlsx');
    }

    /* ── IMPORT EXCEL ───────────────────────────────── */

    public function importExcel(Request $request)
    {
        set_time_limit(0);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            // Pre-create subjects selected by user before running importer
            $subjectsToCreate = array_filter(array_map('trim', (array) $request->input('subjects_to_create', [])));
            $preCreatedNames  = [];
            foreach ($subjectsToCreate as $name) {
                if (empty($name)) continue;
                $subject = Subject::firstOrCreate(['name' => $name]);
                if ($subject->wasRecentlyCreated) {
                    $preCreatedNames[] = $name;
                }
            }

            $import = new TeachersImport;
            foreach ($this->loadSheetsAsCollections($request->file('file')->getRealPath()) as $sheet) {
                $import->importSheet($sheet['rows'], $sheet['name']);
            }

            // Hapus file sisa import (chunk reading menyimpan temp di imports/)
            $this->cleanupImportFiles();

            $count  = $import->getImportedCount();
            $failed = $import->getFailedRows();

            $successMsg = $count > 0 ? "Berhasil mengimpor {$count} data guru." : null;
            if (!empty($preCreatedNames)) {
                $successMsg = ($successMsg ?? '') . ' Mapel baru dibuat: ' . implode(', ', $preCreatedNames) . '.';
            }

            if (count($failed) > 0) {
                return redirect()->route('guru.index')
                    ->with('import_failed', $failed)
                    ->with('import_success_count', $count)
                    ->with('import_type', 'guru')
                    ->with('success', $successMsg);
            }

            if ($count === 0 && empty($preCreatedNames)) {
                return redirect()->route('guru.index')
                    ->with('error', 'Tidak ada data guru baru yang berhasil diimpor. Pastikan format file sesuai template.');
            }

            return redirect()->route('guru.index')
                ->with('success', $successMsg ?? 'Import selesai.');
        } catch (\Exception $e) {
            $this->cleanupImportFiles();
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Hapus file sisa import yang tertinggal di storage/app/private/imports.
     */
    private function cleanupImportFiles(): void
    {
        $dir = storage_path('app/private/imports');
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Baca semua sheet dari file Excel menggunakan PhpSpreadsheet.
     * Mengembalikan array of ['name' => string, 'rows' => Collection].
     * Setiap baris adalah Collection dengan kunci nama kolom (lowercase).
     */
    private function loadSheetsAsCollections(string $filePath): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $result      = [];

        foreach ($spreadsheet->getAllSheets() as $worksheet) {
            $data = $worksheet->toArray(null, false, false, false);
            if (empty($data)) {
                continue;
            }

            $headers = array_map(fn($h) => strtolower(trim((string) ($h ?? ''))), $data[0]);

            // Skip sheet kosong (semua header kosong)
            if (array_filter($headers) === []) {
                continue;
            }

            $rows = collect(array_slice($data, 1))->map(function ($rowData) use ($headers) {
                $padded = array_pad((array) $rowData, count($headers), null);
                return collect(array_combine($headers, array_slice($padded, 0, count($headers))));
            });

            $result[] = ['name' => $worksheet->getTitle(), 'rows' => $rows];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $result;
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
