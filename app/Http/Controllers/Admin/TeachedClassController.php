<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeachedClass;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Ambil semua kelas dengan relasi guru pengajar dan wali kelas
        $classes = ClassModel::with(['teachedClasses.teacher', 'teachedClasses.subjects', 'homeroomTeacher'])
            ->paginate(15);
        
        return view('admin.teached-classes.index', compact('classes'));
    }

    /**
     * Tampilkan form untuk mengelola guru pengajar di kelas tertentu.
     * 
     * @param ClassModel $class Kelas yang akan dikelola
     * @return \Illuminate\View\View
     */
    public function edit(ClassModel $class)
    {
        // Muat relasi yang diperlukan
        $class->load(['teachedClasses.teacher', 'teachedClasses.subjects', 'homeroomTeacher']);
        
        // Ambil mata pelajaran yang tersedia untuk jurusan kelas ini
        $availableSubjects = Subject::forMajor($class->major ?? 'Umum')
            ->active()
            ->orderBy('name')
            ->get();
        
        // Ambil semua guru dengan mata pelajaran yang mereka ajar
        $allTeachers = Teacher::with('subjects')
            ->orderBy('name')
            ->get();
        
        // Proses guru dan cek mata pelajaran yang cocok dengan kelas
        $processedTeachers = $allTeachers->map(function($teacher) use ($availableSubjects) {
            // Ambil mata pelajaran yang diajar guru
            $teacherSubjects = $teacher->subjects;
            
            // Cari mata pelajaran yang cocok dengan kebutuhan kelas
            $matched = $teacherSubjects->filter(function($subject) use ($availableSubjects) {
                return $availableSubjects->contains('id', $subject->id);
            });
            
            $teacher->matched_subjects = $matched;
            $teacher->has_match = $matched->count() > 0;
            return $teacher;
        });
        
        // Filter guru yang memiliki mata pelajaran sesuai kelas
        $matchedTeachers = $processedTeachers->filter(function($teacher) {
            return $teacher->has_match;
        })->values();
        
        // Jika tidak ada guru yang cocok, tampilkan semua guru (fallback)
        $teachers = $matchedTeachers->count() > 0 ? $matchedTeachers : $processedTeachers;
        
        // Ambil semester saat ini (atau default ke 1)
        $currentSemester = 1; // TODO: Ambil dari tabel school_calendar jika ada
        
        return view('admin.teached-classes.edit', compact('class', 'teachers', 'currentSemester', 'availableSubjects'));
    }

    /**
     * Simpan penugasan guru ke kelas (tambah guru pengajar baru).
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
            'semester' => 'required|integer|min:1|max:2',
            'subject_ids' => 'required|array|min:1', // Wajib pilih minimal 1 mata pelajaran
            'subject_ids.*' => 'required|exists:subjects,id', // Setiap ID harus valid
        ]);

        try {
            DB::transaction(function () use ($request, $class) {
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

                // Buat teached_class baru
                $teachedClass = TeachedClass::create([
                    'teacher_id' => $request->teacher_id,
                    'class_id' => $class->id,
                    'semester' => $request->semester,
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
            'semester' => 'required|integer|min:1|max:2',
            'subject_ids' => 'required|array|min:1', // Wajib pilih minimal 1 mata pelajaran
            'subject_ids.*' => 'required|exists:subjects,id', // Setiap ID harus valid
        ]);

        try {
            DB::transaction(function () use ($request, $teachedClass) {
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

                // Update semester
                $teachedClass->update([
                    'semester' => $request->semester,
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
}
