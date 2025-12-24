<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::with(['user', 'class'])->paginate(10);
        $totalStudents = Student::count();
        return view('admin.students.index', compact('students', 'totalStudents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = ClassModel::all();
        return view('admin.students.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'nish' => 'required|unique:students,nis',
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $photoPath = null;
            if ($request->hasFile('photo_profile')) {
                $photoPath = $request->file('photo_profile')->store('students', 'public');
            }

            // Create user with NISH as password
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->nish),
            ]);

            Student::create([
                'user_id' => $user->id,
                'nis' => $request->nish,
                'name' => $request->name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'class_id' => $request->class_id,
                'photo_profile' => $photoPath,
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan. Password: ' . $request->nish);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::with(['user', 'class', 'attendanceSummary'])->findOrFail($id);
        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $student = Student::with('user')->findOrFail($id);
        $classes = ClassModel::all();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($student->user_id),
            ],
            'nish' => [
                'required',
                Rule::unique('students', 'nis')->ignore($student->id),
            ],
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::transaction(function () use ($request, $student) {
            $userData = [
                'email' => $request->email,
            ];

            $student->user->update($userData);

            // Update password jika NISH berubah
            if ($request->nish !== $student->nis) {
                $student->user->update([
                    'password' => Hash::make($request->nish),
                ]);
            }

            $studentData = [
                'nis' => $request->nish,
                'name' => $request->name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'class_id' => $request->class_id,
            ];

            if ($request->hasFile('photo_profile')) {
                if ($student->photo_profile) {
                    \Storage::disk('public')->delete($student->photo_profile);
                }
                $studentData['photo_profile'] = $request->file('photo_profile')->store('students', 'public');
            }

            $student->update($studentData);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        
        DB::transaction(function () use ($student) {
            $student->delete();
            $student->user->delete();
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil dihapus');
    }
}

