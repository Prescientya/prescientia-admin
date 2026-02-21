<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = Student::with(['user', 'schoolClass'])
            ->whereNull('students.deleted_at');

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

        $students = $query->orderBy('students.name')->paginate(15)->withQueryString();
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
            'password'      => 'required|string|min:8',
            'gender'        => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'class_id'      => 'nullable|exists:classes,id',
            'phone_number'  => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'photo_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nis.unique'   => 'NIS sudah terdaftar.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
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
        $siswa->load(['user', 'schoolClass']);
        $classes = ClassModel::orderBy('class')->orderBy('major')->get();
        return view('Data_Siswa.edit', compact('siswa', 'classes'));
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
            $siswa->delete();                     // soft-delete student
            optional($siswa->user)->delete();     // soft-delete user account

            DB::commit();
            return redirect()->route('siswa.index')
                ->with('success', "Data siswa {$name} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
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
            $count = $import->getImportedCount();
            return redirect()->route('siswa.index')
                ->with('success', "Berhasil mengimpor {$count} data siswa.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
