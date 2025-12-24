<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = ClassModel::with('homeroomTeacher')->paginate(10);
        $totalClasses = ClassModel::count();
        return view('admin.classes.index', compact('classes', 'totalClasses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get only teachers with wali_kelas role (from teacher_class_roles)
        $teachers = Teacher::whereHas('classRoles', function ($query) {
            $query->where('role', 'wali_kelas');
        })->with(['homeroomClasses'])->get();

        return view('admin.classes.create', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class' => 'required|integer',
            'major' => 'nullable|string|max:100',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
        ]);

        ClassModel::create([
            'class' => $request->class,
            'major' => $request->major,
            'homeroom_teacher_id' => $request->homeroom_teacher_id,
        ]);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Data kelas berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $class = ClassModel::with(['homeroomTeacher', 'students'])->findOrFail($id);
        return view('admin.classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $class = ClassModel::findOrFail($id);
        // Get only teachers with wali_kelas role (from teacher_class_roles)
        $teachers = Teacher::whereHas('classRoles', function ($query) {
            $query->where('role', 'wali_kelas');
        })->with(['homeroomClasses'])->get();

        return view('admin.classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $class = ClassModel::findOrFail($id);

        $request->validate([
            'class' => 'required|integer',
            'major' => 'nullable|string|max:100',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $class->update([
            'class' => $request->class,
            'major' => $request->major,
            'homeroom_teacher_id' => $request->homeroom_teacher_id,
        ]);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Data kelas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $class = ClassModel::findOrFail($id);
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Data kelas berhasil dihapus');
    }
}

