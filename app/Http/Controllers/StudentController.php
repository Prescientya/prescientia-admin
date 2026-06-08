<?php

namespace App\Http\Controllers;

use App\Exports\StudentTemplateExport;
use App\Imports\StudentsImport;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentClassRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = Student::with(['user', 'schoolClass']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('students.name', 'like', "%{$s}%")
                  ->orWhere('students.nis', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('class_id')) {
            $query->where('students.class_id', $request->class_id);
        }

        // Filter by account status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->whereHas('user', fn($q) => $q->where('is_active', $isActive));
        }

        $students = $query->orderBy('students.name')->paginate(10)->withQueryString();
        $classes  = ClassModel::orderBy('class')->orderBy('major')->get();

        // Count inactive students for "Aktifkan Semua" button
        $inactiveCount = Student::whereHas('user', fn($q) => $q->where('is_active', false))->count();

        // Get unique majors for filter
        $majors = ClassModel::whereNotNull('major')
            ->where('major', '!=', '')
            ->distinct()
            ->orderBy('major')
            ->pluck('major');

        return view('Data_Siswa.Index', compact('students', 'classes', 'inactiveCount', 'majors'));
    }

    /* ── STORE (manual add) ─────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:50',
            'nis'           => 'required|string|max:20|unique:students,nis',
            'email'         => 'required|email|max:50|unique:users,email',
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'class_id'      => 'nullable|exists:classes,id',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.max'     => 'Nama maksimal 50 karakter.',
            'nis.max'      => 'NIS maksimal 20 karakter.',
            'nis.unique'   => 'NIS sudah terdaftar.',
            'email.max'    => 'Email maksimal 50 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'phone_number.max' => 'Nomor HP maksimal 20 karakter.',
            'address.max'  => 'Alamat maksimal 500 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'email'       => $request->email,
                'password'    => Hash::make($request->nis), // default password = NIS, wajib diganti saat login pertama
                'first_login' => true, // paksa ganti password saat login pertama via Flutter app
            ]);
            // role & is_active dikunci dari mass assignment (lihat User::$fillable). Set eksplisit.
            $user->role      = 'student';
            $user->is_active = true;
            $user->save();

            $photoPath = null;
            if ($request->hasFile('photo_profile')) {
                $photoPath = $request->file('photo_profile')->store('students', 'public');
            }

            Student::create([
                'user_id'       => $user->id,
                'nis'           => $request->nis,
                'name'          => $request->name,
                'gender'        => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number'  => $request->phone_number,
                'address'       => $request->address,
                'class_id'      => $request->class_id,
                'photo_profile' => $photoPath,
            ]);

            DB::commit();
            Cache::forget('dashboard.total_siswa');
            return redirect()->route('siswa.index')
                ->with('success', "Siswa {$request->name} berhasil ditambahkan.");
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('[Students] store QueryException: ' . $e->getMessage());
            $msg = $e->getCode() === '23000'
                ? 'NIS atau email sudah terdaftar. Silakan periksa kembali.'
                : 'Terjadi kesalahan database. Silakan coba lagi.';
            return back()->withInput()->with('error', $msg);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[Students] store failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menambahkan siswa. Silakan coba lagi.');
        }
    }

    /* ── SHOW (JSON for modal detail) ───────────────── */

    public function show(Student $siswa)
    {
        $siswa->load(['user', 'schoolClass']);

        return response()->json([
            'id'            => $siswa->id,
            'name'          => $siswa->name,
            'nis'           => $siswa->nis,
            'email'         => optional($siswa->user)->email,
            'gender'        => $siswa->gender,
            'gender_label'  => $siswa->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'date_of_birth' => $siswa->date_of_birth?->format('d-m-Y'),
            'phone_number'  => $siswa->phone_number,
            'address'       => $siswa->address,
            'class'         => optional($siswa->schoolClass)->class,
            'major'         => optional($siswa->schoolClass)->major,
            'kelas_lengkap' => $siswa->kelas_lengkap,
            'photo_url'     => $siswa->photo_profile
                                    ? Storage::url($siswa->photo_profile)
                                    : null,
            'is_active'     => (bool) optional($siswa->user)->is_active,
            'created_at'    => $siswa->created_at?->format('d-m-Y'),
        ]);
    }

    /* ── EDIT ───────────────────────────────────────── */

    public function edit(Student $siswa)
    {
        $siswa->load(['user', 'schoolClass', 'classRole']);
        $classes = ClassModel::orderBy('class')->orderBy('major')->get();

        $currentRole   = $siswa->classRole?->role ?? 'pelajar';
        $classRoleData = $siswa->class_id
            ? $this->getRoleHolders($siswa->class_id, $siswa->id)
            : ['km' => null, 'wakil_km' => null, 'sekretaris' => []];

        return view('Data_Siswa.edit', compact('siswa', 'classes', 'currentRole', 'classRoleData'));
    }

    /* ── CHECK ROLE (AJAX) ──────────────────────────────── */

    public function checkRole(Request $request)
    {
        $classId   = $request->integer('class_id');
        $studentId = $request->integer('student_id');
        if (!$classId) {
            return response()->json(['km' => null, 'wakil_km' => null, 'sekretaris' => []]);
        }
        return response()->json($this->getRoleHolders($classId, $studentId));
    }

    private function getRoleHolders(int $classId, int $excludeStudentId): array
    {
        $rows = StudentClassRole::with('student')
            ->where('class_id', $classId)
            ->where('student_id', '!=', $excludeStudentId)
            ->whereIn('role', ['km', 'wakil_km', 'sekretaris'])
            ->get();

        $data = ['km' => null, 'wakil_km' => null, 'sekretaris' => []];
        foreach ($rows as $r) {
            if ($r->role === 'km')           $data['km']          = $r->student->name;
            elseif ($r->role === 'wakil_km') $data['wakil_km']    = $r->student->name;
            elseif ($r->role === 'sekretaris') $data['sekretaris'][] = $r->student->name;
        }
        return $data;
    }

    /* ── UPDATE ─────────────────────────────────────── */

    public function update(Request $request, Student $siswa)
    {
        $request->validate([
            'name'          => 'required|string|max:50',
            'nis'           => 'required|string|max:20|unique:students,nis,' . $siswa->id,
            'email'         => 'required|email|max:50|unique:users,email,' . $siswa->user_id,
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'class_id'      => 'nullable|exists:classes,id',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'password'      => 'nullable|string|min:8',
            'is_active'     => 'nullable|boolean',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.max'     => 'Nama maksimal 50 karakter.',
            'nis.max'      => 'NIS maksimal 20 karakter.',
            'nis.unique'   => 'NIS sudah digunakan siswa lain.',
            'email.max'    => 'Email maksimal 50 karakter.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'phone_number.max' => 'Nomor HP maksimal 20 karakter.',
            'address.max'  => 'Alamat maksimal 500 karakter.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        DB::beginTransaction();
        try {
            // Update user account
            $userUpdate = [
                'email' => $request->email,
            ];
            if ($request->filled('password')) {
                $userUpdate['password'] = Hash::make($request->password);
            }
            $siswa->user->update($userUpdate);
            // is_active dikunci dari mass assignment (lihat User::$fillable). Set eksplisit.
            $siswa->user->is_active = $request->boolean('is_active', true);
            $siswa->user->save();

            // Photo
            $photoPath = $siswa->photo_profile;
            if ($request->hasFile('photo_profile')) {
                if ($photoPath) Storage::disk('public')->delete($photoPath);
                $photoPath = $request->file('photo_profile')->store('students', 'public');
            }

            $siswa->update([
                'nis'           => $request->nis,
                'name'          => $request->name,
                'gender'        => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number'  => $request->phone_number,
                'address'       => $request->address,
                'class_id'      => $request->class_id,
                'photo_profile' => $photoPath,
            ]);

            // ── Role siswa di kelas ─────────────────────────
            $role    = $request->input('role', 'pelajar');
            $classId = $request->class_id;

            if ($classId) {
                if (in_array($role, ['km', 'wakil_km', 'sekretaris'])) {
                    $limits = StudentClassRole::limits();
                    $taken  = StudentClassRole::where('class_id', $classId)
                                ->where('student_id', '!=', $siswa->id)
                                ->where('role', $role)
                                ->count();
                    if ($taken >= $limits[$role]) {
                        DB::rollback();
                        return back()->withInput()
                            ->with('error', 'Slot role ' . StudentClassRole::roleLabel($role) . ' sudah penuh di kelas ini.');
                    }
                }
                // Hapus role lama dulu (termasuk stale records dari kelas sebelumnya)
                StudentClassRole::where('student_id', $siswa->id)->delete();
                StudentClassRole::create([
                    'student_id' => $siswa->id,
                    'class_id'   => $classId,
                    'role'       => $role,
                ]);
            } else {
                StudentClassRole::where('student_id', $siswa->id)->delete();
            }

            DB::commit();
            return redirect()->route('siswa.index')
                ->with('success', "Data siswa {$siswa->name} berhasil diperbarui.");
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('[Students] update QueryException: ' . $e->getMessage());
            $msg = $e->getCode() === '23000'
                ? 'NIS atau email sudah digunakan akun lain. Silakan periksa kembali.'
                : 'Terjadi kesalahan database. Silakan coba lagi.';
            return back()->withInput()->with('error', $msg);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[Students] update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data siswa. Silakan coba lagi.');
        }
    }

    /* ── DESTROY ────────────────────────────────────── */

    public function destroy(Student $siswa)
    {
        DB::beginTransaction();
        try {
            $name  = $siswa->name;
            $photo = $siswa->photo_profile;
            $user  = $siswa->user; // load relasi sebelum dihapus

            if ($user) {
                // Hapus user → students otomatis CASCADE terhapus via FK DB
                // (students.user_id → users ON DELETE CASCADE)
                // termasuk child tables: student_class_roles, student_attendances, dst.
                $user->delete();
            } else {
                // Edge case: student tidak punya user (data lama/rusak)
                $siswa->delete();
            }

            // Hapus foto profil jika ada
            if ($photo) {
                Storage::disk('public')->delete($photo);
            }

            DB::commit();
            Cache::forget('dashboard.total_siswa');
            return redirect()->route('siswa.index')
                ->with('success', "Data siswa {$name} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
    }

    public function resetPassword(Student $siswa)
    {
        try {
            DB::table('users')
                ->where('id', $siswa->user_id)
                ->update([
                    'password' => Hash::make($siswa->nis),
                    'first_login' => true,
                    'updated_at' => now(),
                ]);

            return redirect()->route('siswa.index')
                ->with('success', "Password siswa {$siswa->name} berhasil direset ke NIS.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset password siswa: ' . $e->getMessage());
        }
    }
    /* ── CHECK CLASSES (AJAX) ──────────────────────────────────── */

    public function checkClasses(Request $request): \Illuminate\Http\JsonResponse
    {
        $rawClasses = $request->input('classes', []);
        $missing    = [];

        foreach ((array) $rawClasses as $item) {
            $tingkat = (int) ($item['tingkat'] ?? 0);
            $jurusan = strtoupper(trim($item['jurusan'] ?? ''));
            if (!$tingkat) continue;

            $exists = ClassModel::where('class', $tingkat)
                                ->where('major', $jurusan ?: null)
                                ->exists();

            if (!$exists) {
                $missing[] = [
                    'tingkat' => $tingkat,
                    'jurusan' => $jurusan,
                    'key'     => "{$tingkat}|{$jurusan}",
                    'label'   => "Kelas {$tingkat}" . ($jurusan ? " – {$jurusan}" : ''),
                ];
            }
        }

        return response()->json(['missing' => $missing]);
    }
    /* ── DOWNLOAD TEMPLATE ──────────────────────────── */

    public function downloadTemplate()
    {
        return Excel::download(new StudentTemplateExport, 'template_import_siswa.xlsx');
    }

    /* ── IMPORT EXCEL ───────────────────────────────── */

    public function importExcel(Request $request)
    {
        // Bulk import can be slow on large files — remove PHP time limit for this request
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            // Pre-create specific classes that user explicitly selected
            $classesToCreate  = $request->input('classes_to_create', []);
            $preCreatedLabels = [];
            if (!empty($classesToCreate)) {
                foreach ((array) $classesToCreate as $key) {
                    $parts   = explode('|', $key, 2);
                    $tingkat = (int) ($parts[0] ?? 0);
                    $jurusan = strtoupper(trim($parts[1] ?? ''));
                    if ($tingkat) {
                        $cls = ClassModel::firstOrCreate(
                            ['class' => $tingkat, 'major' => $jurusan ?: null]
                        );
                        if ($cls->wasRecentlyCreated) {
                            $label = "{$tingkat}" . ($jurusan ? " - {$jurusan}" : '');
                            $preCreatedLabels[$label] = $cls->id;
                        }
                    }
                }
            }

            // autoCreate: true only when old checkbox used & no specific classes sent
            $autoCreate = $request->boolean('auto_create_classes') && empty($classesToCreate);
            $import = new StudentsImport($autoCreate);
            foreach ($this->loadSheetsAsCollections($request->file('file')->getRealPath()) as $sheet) {
                $import->importSheet($sheet['rows'], $sheet['name']);
            }

            // Hapus file sisa import (chunk reading menyimpan temp di imports/)
            $this->cleanupImportFiles();

            $count          = $import->getImportedCount();
            $failed         = $import->getFailedRows();
            $createdClasses = array_merge($preCreatedLabels, $import->getCreatedClasses());

            if (count($failed) > 0) {
                return redirect()->route('siswa.index')
                    ->with('import_failed', $failed)
                    ->with('import_success_count', $count)
                    ->with('import_type', 'siswa')
                    ->with('import_created_classes', $createdClasses)
                    ->with('success', $count > 0 ? "Berhasil mengimpor {$count} data siswa." : null);
            }

            if ($count === 0) {
                return redirect()->route('siswa.index')
                    ->with('error', 'Tidak ada data siswa baru yang berhasil diimpor. Pastikan format file sesuai template.');
            }

            $msg = "Berhasil mengimpor {$count} data siswa.";
            if (!empty($createdClasses)) {
                $msg .= ' Kelas baru dibuat: ' . implode(', ', array_keys($createdClasses)) . '.';
            }

            return redirect()->route('siswa.index')
                ->with('success', $msg)
                ->with('import_created_classes', $createdClasses);
        } catch (\Exception $e) {
            $this->cleanupImportFiles();
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /* ── DELETE GRADUATES ───────────────────────────── */

    public function destroyGraduates()
    {
        $now = now()->timezone('Asia/Jakarta');

        // Only allowed from July 19 onwards
        $isAfterPromotion = ($now->month > 7) || ($now->month === 7 && $now->day >= 19);
        if (! $isAfterPromotion) {
            return back()->with('error', 'Hapus siswa lulus hanya bisa dilakukan mulai 19 Juli.');
        }

        $flagPath = storage_path('app/graduates_deleted_' . $now->year . '.flag');

        // Buat flag file secara atomik dengan mode 'x' (gagal jika sudah ada).
        // Ini mencegah race condition di mana dua request paralel bisa melewati
        // cek file_exists() sebelum salah satu selesai menulis flag.
        $fp = @fopen($flagPath, 'x');
        if ($fp === false) {
            return back()->with('error', 'Siswa lulus untuk tahun ini sudah pernah dihapus.');
        }
        fclose($fp);

        $graduates = Student::whereNull('class_id')->with('user')->get();

        if ($graduates->isEmpty()) {
            @unlink($flagPath); // hapus flag agar tidak memblokir jika memang tidak ada data
            return back()->with('info', 'Tidak ada siswa lulus yang perlu dihapus.');
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($graduates as $student) {
                if ($student->user) {
                    $student->user->delete(); // cascades to student record
                } else {
                    $student->delete();
                }
                $count++;
            }

            DB::commit();

            file_put_contents($flagPath, $now->toDateTimeString());

            Log::info("[Students] Deleted {$count} graduated students ({$now->year}).");
            Cache::forget('dashboard.total_siswa');

            return back()->with('success', "Berhasil menghapus {$count} siswa lulus.");
        } catch (\Exception $e) {
            DB::rollback();
            @unlink($flagPath); // hapus flag agar bisa dicoba ulang setelah error
            Log::error('[Students] destroyGraduates failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus siswa lulus: ' . $e->getMessage());
        }
    }

    /* ── PREVIEW BULK (AJAX) ────────────────────────── */

    public function previewBulk(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Student::whereNotNull('class_id')
            ->whereHas('user', fn($q) => $q->where('is_active', true));

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by grade level (tingkat)
        if ($request->filled('grade')) {
            $query->whereHas('schoolClass', fn($q) => $q->where('class', $request->grade));
        }

        // Filter by major (jurusan)
        if ($request->filled('major')) {
            $query->whereHas('schoolClass', fn($q) => $q->where('major', $request->major));
        }

        // Filter by individual IDs
        if ($request->filled('student_ids')) {
            $ids = is_array($request->student_ids) ? $request->student_ids : explode(',', $request->student_ids);
            $query->whereIn('id', $ids);
        }

        $count = $query->count();
        $students = $query->with('schoolClass')
            ->select('id', 'name', 'nis', 'class_id')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'nis'   => $s->nis,
                'kelas' => $s->schoolClass?->full_name ?? '-',
            ]);

        return response()->json([
            'count'    => $count,
            'students' => $students,
            'hasMore'  => $count > 10,
        ]);
    }

    /* ── BULK DEACTIVATE ────────────────────────────── */

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'mode'        => 'required|in:class,grade,major,grade_major,individual',
            'class_id'    => 'required_if:mode,class|nullable|exists:classes,id',
            'grade'       => 'required_if:mode,grade,grade_major|nullable|in:10,11,12',
            'major'       => 'required_if:mode,major,grade_major|nullable|string',
            'student_ids' => 'required_if:mode,individual|nullable|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $query = Student::whereNotNull('class_id')
            ->whereHas('user', fn($q) => $q->where('is_active', true));

        switch ($request->mode) {
            case 'class':
                $query->where('class_id', $request->class_id);
                break;

            case 'grade':
                $query->whereHas('schoolClass', fn($q) => $q->where('class', $request->grade));
                break;

            case 'major':
                $query->whereHas('schoolClass', fn($q) => $q->where('major', $request->major));
                break;

            case 'grade_major':
                $query->whereHas('schoolClass', fn($q) => 
                    $q->where('class', $request->grade)->where('major', $request->major)
                );
                break;

            case 'individual':
                $query->whereIn('id', $request->student_ids);
                break;
        }

        $students = $query->with('user')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang dapat di-nonaktifkan.');
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($students as $student) {
                if ($student->user) {
                    // is_active dikunci dari mass assignment (lihat User::$fillable). Set eksplisit.
                    $student->user->is_active = false;
                    $student->user->save();
                    $count++;
                }
            }

            DB::commit();
            Log::info("[Students] Bulk deactivated {$count} students. Mode: {$request->mode}");

            return back()->with('success', "Berhasil menonaktifkan {$count} akun siswa.");
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[Students] bulkDeactivate failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menonaktifkan siswa: ' . $e->getMessage());
        }
    }

    /* ── BULK ACTIVATE ALL ──────────────────────────── */

    public function bulkActivate(Request $request)
    {
        $students = Student::whereHas('user', fn($q) => $q->where('is_active', false))
            ->with('user')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('info', 'Tidak ada siswa nonaktif yang perlu diaktifkan.');
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($students as $student) {
                if ($student->user) {
                    // is_active dikunci dari mass assignment (lihat User::$fillable). Set eksplisit.
                    $student->user->is_active = true;
                    $student->user->save();
                    $count++;
                }
            }

            DB::commit();
            Log::info("[Students] Bulk activated {$count} students.");

            return back()->with('success', "Berhasil mengaktifkan {$count} akun siswa.");
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[Students] bulkActivate failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengaktifkan siswa: ' . $e->getMessage());
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
}
