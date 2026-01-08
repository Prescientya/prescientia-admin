<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\TeacherClassRole;
use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::with('user')->paginate(10);
        $totalTeachers = Teacher::count();
        return view('admin.teachers.index', compact('teachers', 'totalTeachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {

        try {
            DB::transaction(function () use ($request) {
                $photoPath = null;
                if ($request->hasFile('photo_profile')) {
                    $photoPath = $request->file('photo_profile')->store('teachers', 'public');
                }

                // Create user with NIP as password
                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->nip),
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'name' => $request->name,
                    'gender' => $request->gender,
                    'date_of_birth' => $request->date_of_birth,
                    'phone_number' => $request->phone_number,
                    'department' => $request->departments ? array_filter($request->departments) : null,
                    'address' => $request->address,
                    'photo_profile' => $photoPath,
                ]);

                // Map UI role to DB enum value and create teacher class role entry
                $dbRole = $request->role === 'Pengajar' ? 'pengajar' : 'wali_kelas';
                TeacherClassRole::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => null, // Will be set when assigning to classes
                    'role' => $dbRole,
                ]);
            });
        } catch (\Throwable $e) {
            // Build a concise, user-friendly error message for common DB errors
            $friendly = 'Terjadi kesalahan saat menyimpan data.';

            if ($e instanceof \Illuminate\Database\QueryException) {
                $sqlState = $e->errorInfo[0] ?? null;
                $sqlMessage = $e->getMessage();

                // String data right truncated (value too long)
                if ($sqlState === '22001' || str_contains($sqlMessage, 'value too long')) {
                    $over = [];
                    if (isset($request->nip) && mb_strlen($request->nip) > 20) {
                        $over[] = 'NIP (maks 20 karakter)';
                    }
                    if (isset($request->phone_number) && mb_strlen($request->phone_number) > 20) {
                        $over[] = 'No. Telepon (maks 20 karakter)';
                    }
                    if (isset($request->name) && mb_strlen($request->name) > 100) {
                        $over[] = 'Nama (maks 100 karakter)';
                    }
                    if (isset($request->departments)) {
                        $depts = array_filter($request->departments);
                        if (count($depts) > 0) {
                            $over[] = 'Bidang Studi (jumlah atau karakter melebihi batas)';
                        }
                    }

                    if (!empty($over)) {
                        $friendly = 'Panjang data melebihi batas: ' . implode(', ', $over) . '.';
                    } else {
                        $friendly = 'Salah satu nilai terlalu panjang untuk kolom database.';
                    }
                }

                // Unique constraint violation
                elseif ($sqlState === '23505' || str_contains($sqlMessage, 'duplicate key')) {
                    if (str_contains($sqlMessage, 'teachers_nip') || str_contains($sqlMessage, 'nip')) {
                        $friendly = 'NIP sudah terdaftar.';
                    } elseif (str_contains($sqlMessage, 'users_email') || str_contains($sqlMessage, 'email')) {
                        $friendly = 'Email sudah digunakan.';
                    } else {
                        $friendly = 'Terjadi pelanggaran constraint unik di database.';
                    }
                } else {
                    $friendly = 'Kesalahan database: silakan periksa input.';
                }
            } else {
                // Non-database exception
                $friendly = 'Kesalahan server: silakan hubungi administrator.';
            }

            return redirect()->back()->withInput()->with('error', 'Gagal! ' . $friendly);
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil ditambahkan. Password: ' . $request->nip);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        return view('admin.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        return view('admin.teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, string $id)
    {
        $teacher = Teacher::findOrFail($id);

        DB::transaction(function () use ($request, $teacher) {
            $userData = [
                'email' => $request->email,
            ];

            $teacher->user->update($userData);

            // Update password jika NIP berubah
            if ($request->nip !== $teacher->nip) {
                $teacher->user->update([
                    'password' => Hash::make($request->nip),
                ]);
            }

            $teacherData = [
                'nip' => $request->nip,
                'name' => $request->name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
                'department' => $request->departments ? array_filter($request->departments) : null,
                'address' => $request->address,
            ];

            if ($request->hasFile('photo_profile')) {
                if ($teacher->photo_profile) {
                    Storage::disk('public')->delete($teacher->photo_profile);
                }
                $teacherData['photo_profile'] = $request->file('photo_profile')->store('teachers', 'public');
            }

            $teacher->update($teacherData);
            
            // Update or recreate teacher class role (map UI role to DB enum)
            $teacher->classRoles()->delete();
            $dbRole = $request->role === 'Pengajar' ? 'pengajar' : 'wali_kelas';
            TeacherClassRole::create([
                'teacher_id' => $teacher->id,
                'class_id' => null, // Akan diisi saat assign ke kelas
                'role' => $dbRole,
            ]);

            // Jika guru tidak lagi berperan sebagai wali_kelas, hapus penugasan wali kelas pada tabel classes
            if ($dbRole !== 'wali_kelas') {
                ClassModel::where('homeroom_teacher_id', $teacher->id)
                    ->update(['homeroom_teacher_id' => null]);
            }
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $teacher = Teacher::findOrFail($id);
        
        DB::transaction(function () use ($teacher) {
            $teacher->delete();
            $teacher->user->delete();
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil dihapus');
    }
}

