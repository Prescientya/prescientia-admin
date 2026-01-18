<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeachedClass;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\Subject;
use App\Helpers\AttendanceHelper;
use App\Exports\TeacherAssignmentTemplateExport;
use App\Imports\TeacherAssignmentImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller untuk mengelola guru pengajar di kelas (teached classes).
 * Mengelola relasi antara guru, kelas, dan mata pelajaran.
 */
class TeachedClassController extends Controller
{
    /**
     * Tampilkan daftar semua kelas beserta guru pengajarnya.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get current semester info
        $currentSemester = AttendanceHelper::getCurrentSemester();
        $semesterName = AttendanceHelper::getSemesterName($currentSemester);
        $academicYear = AttendanceHelper::getCurrentAcademicYear();
        
        // Ambil semua kelas dengan relasi guru pengajar dan wali kelas
        // Show 10 classes per page so the view can render a 2 x 5 card grid per page
        $classes = ClassModel::with([
            'teachedClasses' => function($query) use ($currentSemester) {
                $query->where('semester', $currentSemester)
                      ->whereNotNull('subject_id');
            },
            'teachedClasses.teacher',
            'teachedClasses.subject',
            'homeroomTeacher'
        ])->paginate(10);
        
        // For each class, calculate unassigned subjects
        foreach ($classes as $class) {
            // Get all subjects for this class major
            $availableSubjects = Subject::forMajor($class->major ?? 'Umum')
                ->active()
                ->get();
            
            // Get assigned subject IDs
            $assignedSubjectIds = $class->teachedClasses->pluck('subject_id')->toArray();
            
            // Get unassigned subjects
            $unassignedSubjects = $availableSubjects->whereNotIn('id', $assignedSubjectIds);
            
            // Attach to class object
            $class->unassignedSubjects = $unassignedSubjects;
            $class->totalTeachers = $class->teachedClasses->unique('teacher_id')->count();
        }
        
        return view('admin.teached-classes.index', compact('classes', 'currentSemester', 'semesterName', 'academicYear'));
    }

    /**
     * Provide suggestions for class filter autocomplete.
     * Returns all classes with their numbers and majors.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions()
    {
        $classes = ClassModel::select('id', 'class', 'major')
            ->orderBy('class')
            ->orderBy('major')
            ->get()
            ->map(function($class) {
                return [
                    'id' => $class->id,
                    'number' => $class->class,
                    'major' => $class->major,
                ];
            });
        
        return response()->json([
            'classes' => $classes,
        ]);
    }

    /**
     * Get subject list for a specific teacher (AJAX endpoint)
     * Returns all subjects taught by the teacher with conflict info
     * 
     * @param Request $request
     * @param ClassModel $class
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeacherSubjects(Request $request, ClassModel $class)
    {
        $teacherId = $request->input('teacher_id');
        
        if (!$teacherId) {
            return response()->json(['subjects' => []]);
        }
        
        $teacher = Teacher::with('subjects')->find($teacherId);
        
        if (!$teacher) {
            return response()->json(['error' => 'Guru tidak ditemukan'], 404);
        }
        
        // Get subjects already assigned to this class
        $assignedSubjectIds = $class->teachedClasses()
            ->with('subjects')
            ->get()
            ->pluck('subjects')
            ->flatten()
            ->pluck('id')
            ->toArray();
        
        // Filter teacher's subjects by class major
        $subjects = $teacher->subjects()
            ->forMajor($class->major ?? 'Umum')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(function($subject) use ($assignedSubjectIds) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'is_assigned' => in_array($subject->id, $assignedSubjectIds),
                ];
            });
        
        // Check for conflicts
        $conflicts = $subjects->filter(fn($s) => $s['is_assigned'])->count();
        
        return response()->json([
            'subjects' => $subjects,
            'conflict_count' => $conflicts,
            'teacher_name' => $teacher->name,
        ]);
    }

    /**
     * Tampilkan form untuk mengelola guru pengajar di kelas tertentu.
     * 
     * @param ClassModel $class Kelas yang akan dikelola
     * @return \Illuminate\View\View
     */
    public function edit(ClassModel $class)
    {
        // Get current semester automatically
        $currentSemester = AttendanceHelper::getCurrentSemester();
        $semesterName = AttendanceHelper::getSemesterName($currentSemester);
        $academicYear = AttendanceHelper::getCurrentAcademicYear();
        
        // Ambil mata pelajaran yang tersedia untuk jurusan kelas ini
        $availableSubjects = Subject::forMajor($class->major ?? 'Umum')
            ->active()
            ->orderBy('name')
            ->get();
        
        // Ambil existing assignments untuk kelas ini (dengan subject_id sudah terisi)
        $existingAssignments = TeachedClass::with(['teacher', 'subject'])
            ->where('class_id', $class->id)
            ->where('semester', $currentSemester)
            ->whereNotNull('subject_id') // Only get new-style assignments
            ->get();
        
        // Group teachers by subject_id for quick lookup
        $teachersBySubject = [];
        foreach ($availableSubjects as $subject) {
            // Get teachers who teach this subject
            $teachersBySubject[$subject->id] = Teacher::with('subjects')
                ->whereHas('subjects', function($query) use ($subject) {
                    $query->where('subjects.id', $subject->id);
                })
                ->orderBy('name')
                ->get();
        }
        
        return view('admin.teached-classes.edit', compact(
            'class', 
            'availableSubjects', 
            'existingAssignments',
            'teachersBySubject',
            'currentSemester', 
            'semesterName', 
            'academicYear'
        ));
    }

    /**
     * Assign teacher to specific subject in a class (NEW: subject-first approach)
     * 
     * @param Request $request
     * @param ClassModel $class
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignTeacherToSubject(Request $request, ClassModel $class, Subject $subject)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);
        
        $currentSemester = AttendanceHelper::getCurrentSemester();
        
        try {
            DB::transaction(function () use ($request, $class, $subject, $currentSemester) {
                // Check if this subject is already assigned to a teacher in this class
                $existing = TeachedClass::where('class_id', $class->id)
                    ->where('subject_id', $subject->id)
                    ->where('semester', $currentSemester)
                    ->first();
                
                if ($existing) {
                    throw new \Exception('Mata pelajaran "' . $subject->name . '" sudah diajar oleh ' . $existing->teacher->name);
                }
                
                // Verify teacher teaches this subject
                $teacher = Teacher::with('subjects')->find($request->teacher_id);
                if (!$teacher->subjects->contains('id', $subject->id)) {
                    throw new \Exception('Guru tidak mengajar mata pelajaran ini');
                }
                
                // Create new assignment
                TeachedClass::create([
                    'teacher_id' => $request->teacher_id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'semester' => $currentSemester,
                    'departments' => [], // Deprecated field
                ]);
            });
            
            return redirect()->back()
                ->with('success', 'Guru berhasil ditugaskan untuk mengajar ' . $subject->name);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update teacher for a specific assignment (change teacher but keep subject)
     * 
     * @param Request $request
     * @param TeachedClass $teachedClass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTeacherForSubject(Request $request, TeachedClass $teachedClass)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);
        
        try {
            DB::transaction(function () use ($request, $teachedClass) {
                // Verify new teacher teaches this subject
                $teacher = Teacher::with('subjects')->find($request->teacher_id);
                if (!$teacher->subjects->contains('id', $teachedClass->subject_id)) {
                    throw new \Exception('Guru tidak mengajar mata pelajaran ini');
                }
                
                $teachedClass->update([
                    'teacher_id' => $request->teacher_id,
                ]);
            });
            
            return redirect()->back()
                ->with('success', 'Guru pengajar berhasil diubah');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove teacher assignment from specific subject
     * 
     * @param TeachedClass $teachedClass
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeTeacherFromSubject(TeachedClass $teachedClass)
    {
        try {
            $subjectName = $teachedClass->subject->name;
            $teachedClass->delete();
            
            return redirect()->back()
                ->with('success', 'Penugasan untuk ' . $subjectName . ' berhasil dihapus');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus penugasan');
        }
    }

    /**
     * Simpan penugasan guru ke kelas (tambah guru pengajar baru).
     * DEPRECATED: Use assignTeacherToSubject() instead
     * 
     * @param Request $request Request dari form
     * @param ClassModel $class Kelas yang akan ditambahkan gurunya
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, ClassModel $class)
    {
        // Validasi input dari form
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_ids' => 'required|array|min:1', // Wajib pilih minimal 1 mata pelajaran
            'subject_ids.*' => 'required|exists:subjects,id', // Setiap ID harus valid
        ]);
        
        // Get current semester automatically
        $currentSemester = AttendanceHelper::getCurrentSemester();

        try {
            DB::transaction(function () use ($request, $class, $currentSemester) {
                $subjectIds = $request->subject_ids;
                
                // Cek apakah ada mata pelajaran yang sudah diajar di kelas ini
                $assignedSubjects = $class->teachedClasses()
                    ->with('subjects')
                    ->get()
                    ->pluck('subjects')
                    ->flatten()
                    ->pluck('id')
                    ->toArray();
                
                // Cari mata pelajaran yang duplikat
                $duplicates = array_intersect($subjectIds, $assignedSubjects);
                if (count($duplicates) > 0) {
                    $duplicateNames = Subject::whereIn('id', $duplicates)->pluck('name')->toArray();
                    throw new \Exception('Mata pelajaran "' . implode(', ', $duplicateNames) . '" sudah terdaftar untuk kelas ini');
                }

                // Buat teached_class baru dengan semester otomatis
                $teachedClass = TeachedClass::create([
                    'teacher_id' => $request->teacher_id,
                    'class_id' => $class->id,
                    'semester' => $currentSemester,
                    'departments' => [], // Deprecated, untuk backward compatibility
                ]);

                // Attach mata pelajaran ke teached_class
                $teachedClass->subjects()->attach($subjectIds);
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
     * Hapus penugasan guru dari kelas.
     * 
     * @param TeachedClass $teachedClass Teached class yang akan dihapus
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TeachedClass $teachedClass)
    {
        $classId = $teachedClass->class_id;
        
        try {
            // Hapus relasi dengan mata pelajaran dulu
            $teachedClass->subjects()->detach();
            
            // Hapus teached_class
            $teachedClass->delete();
            
            return redirect()->route('admin.teached-classes.edit', $classId)
                ->with('success', 'Guru pengajar berhasil dihapus');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus guru pengajar');
        }
    }

    /**
     * Update penugasan guru di kelas (edit data guru pengajar).
     * 
     * @param Request $request Request dari form
     * @param TeachedClass $teachedClass Teached class yang akan diupdate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TeachedClass $teachedClass)
    {
        // Validasi input dari form
        $request->validate([
            'subject_ids' => 'required|array|min:1', // Wajib pilih minimal 1 mata pelajaran
            'subject_ids.*' => 'required|exists:subjects,id', // Setiap ID harus valid
        ]);
        
        // Get current semester automatically
        $currentSemester = AttendanceHelper::getCurrentSemester();

        try {
            DB::transaction(function () use ($request, $teachedClass, $currentSemester) {
                $subjectIds = $request->subject_ids;
                
                // Cek apakah ada mata pelajaran yang sudah diajar di kelas ini (kecuali yang sedang diedit)
                $assignedSubjects = $teachedClass->class->teachedClasses()
                    ->where('id', '!=', $teachedClass->id) // Exclude current teached_class
                    ->with('subjects')
                    ->get()
                    ->pluck('subjects')
                    ->flatten()
                    ->pluck('id')
                    ->toArray();
                
                // Cari mata pelajaran yang duplikat
                $duplicates = array_intersect($subjectIds, $assignedSubjects);
                if (count($duplicates) > 0) {
                    $duplicateNames = Subject::whereIn('id', $duplicates)->pluck('name')->toArray();
                    throw new \Exception('Mata pelajaran "' . implode(', ', $duplicateNames) . '" sudah terdaftar untuk kelas ini');
                }

                // Update semester otomatis
                $teachedClass->update([
                    'semester' => $currentSemester,
                ]);

                // Sync mata pelajaran (replace yang lama dengan yang baru)
                $teachedClass->subjects()->sync($subjectIds);
            });

            return redirect()->back()
                ->with('success', 'Data guru pengajar berhasil diperbarui');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel for teacher class assignment import
     */
    public function downloadAssignmentTemplate()
    {
        $export = new TeacherAssignmentTemplateExport();
        $spreadsheet = $export->generate();
        
        $filename = 'Template_Import_Guru_Mengajar_' . date('Y-m-d_His') . '.xlsx';
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });
        
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }

    /**
     * Import teacher class assignments from Excel
     */
    public function importAssignments(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Use import class to process assignments
            $importer = new TeacherAssignmentImport($worksheet);
            [$success, $failures] = $importer->process();
            
            // If there are failures, show them
            if (!empty($failures)) {
                return view('admin.teached-classes.import_result', [
                    'successCount' => $success,
                    'failures' => $failures,
                    'totalRows' => $success + count($failures),
                ]);
            }
            
            // Success - redirect with message
            return redirect()->route('admin.teached-classes.index')
                ->with('success', "✅ Import berhasil: {$success} penugasan guru ditambahkan");
                
        } catch (\Throwable $e) {
            Log::error('Import teacher assignments failed', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
