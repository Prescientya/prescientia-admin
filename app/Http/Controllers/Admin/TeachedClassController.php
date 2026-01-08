<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeachedClass;
use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeachedClassController extends Controller
{
    /**
     * Display a listing of teached classes.
     */
    public function index()
    {
        $classes = ClassModel::with(['teachedClasses.teacher', 'homeroomTeacher'])
            ->paginate(15);
        
        return view('admin.teached-classes.index', compact('classes'));
    }

    /**
     * Show form to assign teachers to a class.
     */
    public function edit(ClassModel $class)
    {
        $class->load(['teachedClasses.teacher', 'homeroomTeacher']);
        
        // Get all teachers and ensure department is an array
        $teachers = Teacher::select('id', 'nip', 'name', 'department')
            ->orderBy('name')
            ->get()
            ->map(function($t) {
                $t->department = is_array($t->department) ? $t->department : ($t->department ? [$t->department] : []);
                return $t;
            });
        
        // Get current semester (or default to 1)
        $currentSemester = 1; // TODO: Get from school_calendar table if exists
        
        return view('admin.teached-classes.edit', compact('class', 'teachers', 'currentSemester'));
    }

    /**
     * Store teached class assignment.
     */
    public function store(Request $request, ClassModel $class)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'semester' => 'required|integer|min:1|max:2',
            'departments' => 'nullable|array',
            'departments.*' => 'nullable|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($request, $class) {
                $departments = $request->departments ? array_filter($request->departments) : [];

                // Check for department conflicts in this class
                $existing = TeachedClass::where('class_id', $class->id)->get();
                foreach ($existing as $ex) {
                    $exDeps = is_array($ex->departments) ? $ex->departments : [];
                    $inter = array_intersect($exDeps, $departments);
                    if (count($inter) > 0) {
                        throw new \Exception('Mata pelajaran "' . implode(', ', $inter) . '" sudah terdaftar untuk kelas ini');
                    }
                }
                // Ensure selected departments belong to the selected teacher
                $teacher = Teacher::find($request->teacher_id);
                $teacherDeps = is_array($teacher->department) ? $teacher->department : ($teacher->department ? [$teacher->department] : []);
                $invalid = array_diff($departments, $teacherDeps);
                if (count($invalid) > 0) {
                    throw new \Exception('Mata pelajaran tidak valid untuk guru ini: ' . implode(', ', $invalid));
                }

                TeachedClass::create([
                    'teacher_id' => $request->teacher_id,
                    'class_id' => $class->id,
                    'semester' => $request->semester,
                    'departments' => $departments,
                ]);
            });

            return redirect()->route('admin.teached-classes.edit', $class->id)
                ->with('success', 'Guru pengajar berhasil ditambahkan');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan guru pengajar: ' . $e->getMessage());
        }
    }

    /**
     * Remove teached class assignment.
     */
    public function destroy(TeachedClass $teachedClass)
    {
        $classId = $teachedClass->class_id;
        
        try {
            $teachedClass->delete();
            
            return redirect()->route('admin.teached-classes.edit', $classId)
                ->with('success', 'Guru pengajar berhasil dihapus');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus guru pengajar');
        }
    }

    /**
     * Update teached class assignment.
     */
    public function update(Request $request, TeachedClass $teachedClass)
    {
        $request->validate([
            'semester' => 'required|integer|min:1|max:2',
            'departments' => 'nullable|array',
            'departments.*' => 'nullable|string|max:100',
        ]);

        try {
            $departments = $request->departments ? array_filter($request->departments) : [];

            // Check for conflicts excluding current record
            $existing = TeachedClass::where('class_id', $teachedClass->class_id)
                ->where('id', '!=', $teachedClass->id)
                ->get();
            foreach ($existing as $ex) {
                $exDeps = is_array($ex->departments) ? $ex->departments : [];
                $inter = array_intersect($exDeps, $departments);
                if (count($inter) > 0) {
                    throw new \Exception('Mata pelajaran "' . implode(', ', $inter) . '" sudah terdaftar untuk kelas ini');
                }
            }

            // Ensure departments belong to current teacher
            $teacher = $teachedClass->teacher;
            $teacherDeps = is_array($teacher->department) ? $teacher->department : ($teacher->department ? [$teacher->department] : []);
            $invalid = array_diff($departments, $teacherDeps);
            if (count($invalid) > 0) {
                throw new \Exception('Mata pelajaran tidak valid untuk guru ini: ' . implode(', ', $invalid));
            }

            $teachedClass->update([
                'semester' => $request->semester,
                'departments' => $departments,
            ]);

            return redirect()->back()
                ->with('success', 'Data guru pengajar berhasil diperbarui');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data');
        }
    }
}
