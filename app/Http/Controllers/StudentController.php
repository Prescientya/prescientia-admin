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
                $q->where('students.name', 'ilike', "%{$s}%")
                  ->orWhere('students.nis', 'ilike', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'ilike', "%{$s}%"));
            });
        }

        if ($request->filled('class_id')) {
            $query->where('students.class_id', $request->class_id);
        }

        $students = $query->orderBy('students.name')->paginate(10)->withQueryString();
        $classes  = ClassModel::orderBy('class')->orderBy('major')->get();

        return view('Data_Siswa.index', compact('students', 'classes'));
    }

    /* ── STORE (manual add) ─────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'nis'           => 'required|string|max:20|unique:students,nis',
            'email'         => 'required|email|max:100|unique:users,email',
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'class_id'      => 'nullable|exists:classes,id',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nis.unique'   => 'NIS sudah terdaftar.',
            'email.unique' => 'Email sudah digunakan.',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'email'     => $request->email,
                'password'  => Hash::make($request->nis), // default password = NIS
                'role'      => 'student',
                'is_active' => true,
            ]);

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
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Gagal menambahkan siswa: ' . $e->getMessage());
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
            'name'          => 'required|string|max:100',
            'nis'           => 'required|string|max:20|unique:students,nis,' . $siswa->id,
            'email'         => 'required|email|max:100|unique:users,email,' . $siswa->user_id,
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'class_id'      => 'nullable|exists:classes,id',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'password'      => 'nullable|string|min:8',
            'is_active'     => 'nullable|boolean',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nis.unique'   => 'NIS sudah digunakan siswa lain.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        DB::beginTransaction();
        try {
            // Update user account
            $userUpdate = [
                'email'     => $request->email,
                'is_active' => $request->boolean('is_active', true),
            ];
            if ($request->filled('password')) {
                $userUpdate['password'] = Hash::make($request->password);
            }
            $siswa->user->update($userUpdate);

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
                StudentClassRole::updateOrCreate(
                    ['student_id' => $siswa->id],
                    ['class_id' => $classId, 'role' => $role]
                );
            } else {
                StudentClassRole::where('student_id', $siswa->id)->delete();
            }

            DB::commit();
            return redirect()->route('siswa.index')
                ->with('success', "Data siswa {$siswa->name} berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data siswa: ' . $e->getMessage());
        }
    }

    /* ── DESTROY ────────────────────────────────────── */

    public function destroy(Student $siswa)
    {
        DB::beginTransaction();
        try {
            $name = $siswa->name;
            $user = $siswa->user;
            $siswa->delete();                 // hard delete student
            optional($user)->delete();        // hard delete user account

            DB::commit();
            Cache::forget('dashboard.total_siswa');
            return redirect()->route('siswa.index')
                ->with('success', "Data siswa {$name} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
    }

    /* ── DOWNLOAD TEMPLATE ──────────────────────────── */

    public function downloadTemplate()
    {
        return Excel::download(new StudentTemplateExport, 'template_import_siswa.xlsx');
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
            $import = new StudentsImport;
            Excel::import($import, $request->file('file'));
            $count  = $import->getImportedCount();
            $failed = $import->getFailedRows();

            if (count($failed) > 0) {
                return redirect()->route('siswa.index')
                    ->with('import_failed', $failed)
                    ->with('import_success_count', $count)
                    ->with('import_type', 'siswa')
                    ->with('success', $count > 0 ? "Berhasil mengimpor {$count} data siswa." : null);
            }

            if ($count === 0) {
                return redirect()->route('siswa.index')
                    ->with('error', 'Tidak ada data siswa baru yang berhasil diimpor. Pastikan format file sesuai template.');
            }

            return redirect()->route('siswa.index')
                ->with('success', "Berhasil mengimpor {$count} data siswa.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
