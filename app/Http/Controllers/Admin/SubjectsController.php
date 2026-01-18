<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk mengelola data mata pelajaran.
 * Mengelola CRUD mata pelajaran di sistem.
 */
class SubjectsController extends Controller
{
    /**
     * Tampilkan daftar semua mata pelajaran.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil semua mata pelajaran dengan filter dan pagination
        $subjects = Subject::query()
            ->when(request('search'), function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when(request('major'), function($query, $major) {
                $query->where('major', $major);
            })
            ->when(request('kelas'), function($query, $kelas) {
                $query->where('kelas', $kelas);
            })
            ->when(request('status') !== null, function($query) {
                $isActive = request('status') === '1';
                $query->where('is_active', $isActive);
            })
            ->orderBy('kelas')
            ->orderBy('major')
            ->orderBy('name')
            ->paginate(20);
        
        // Ambil daftar jurusan untuk filter
        $majors = Subject::select('major')
            ->distinct()
            ->whereNotNull('major')
            ->orderBy('major')
            ->pluck('major');
        
        // Ambil daftar kelas untuk filter
        $kelases = Subject::select('kelas')
            ->distinct()
            ->whereNotNull('kelas')
            ->orderBy('kelas')
            ->pluck('kelas');
        
        return view('admin.subjects.index', compact('subjects', 'majors', 'kelases'));
    }

    /**
     * Tampilkan form untuk membuat mata pelajaran baru.
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Daftar jurusan yang tersedia
        $majors = ['IPA', 'IPS', 'Bahasa', 'TKJ', 'RPL', 'Kuliner', 'Umum'];
        
        // Daftar kelas yang tersedia
        $kelases = ClassModel::select('class')
            ->distinct()
            ->orderBy('class')
            ->pluck('class')
            ->toArray();
        
        return view('admin.subjects.create', compact('majors', 'kelases'));
    }

    /**
     * Simpan mata pelajaran baru ke database.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'major' => 'required|string|max:50',
            'kelas' => 'nullable|integer|min:1|max:99',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi',
            'major.required' => 'Jurusan wajib dipilih',
            'kelas.integer' => 'Kelas harus berupa angka',
        ]);

        try {
            // Cek apakah sudah ada mata pelajaran dengan nama, kelas, dan jurusan yang sama
            $exists = Subject::where('name', $validated['name'])
                ->where('major', $validated['major'])
                ->where('kelas', $validated['kelas'] ?? null)
                ->exists();
            
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Mata pelajaran "' . $validated['name'] . '" sudah ada untuk Kelas ' . ($validated['kelas'] ?? 'Umum') . ' Jurusan ' . $validated['major']);
            }

            // Buat mata pelajaran baru
            Subject::create([
                'name' => $validated['name'],
                'major' => $validated['major'],
                'kelas' => $validated['kelas'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.subjects.index')
                ->with('success', 'Mata pelajaran berhasil ditambahkan');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan mata pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail mata pelajaran tertentu.
     * 
     * @param Subject $subject
     * @return \Illuminate\View\View
     */
    public function show(Subject $subject)
    {
        // Muat relasi guru dan teached classes
        $subject->load(['teachers', 'teachedClasses.teacher', 'teachedClasses.class']);
        
        return view('admin.subjects.detail', compact('subject'));
    }

    /**
     * Tampilkan form untuk edit mata pelajaran.
     * 
     * @param Subject $subject
     * @return \Illuminate\View\View
     */
    public function edit(Subject $subject)
    {
        // Daftar jurusan yang tersedia
        $majors = ['IPA', 'IPS', 'Bahasa', 'TKJ', 'RPL', 'Kuliner', 'Umum'];
        
        // Daftar kelas yang tersedia
        $kelases = ClassModel::select('class')
            ->distinct()
            ->orderBy('class')
            ->pluck('class')
            ->toArray();
        
        return view('admin.subjects.edit', compact('subject', 'majors', 'kelases'));
    }

    /**
     * Update data mata pelajaran di database.
     * 
     * @param Request $request
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Subject $subject)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'major' => 'required|string|max:50',
            'kelas' => 'nullable|integer|min:1|max:99',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi',
            'major.required' => 'Jurusan wajib dipilih',
            'kelas.integer' => 'Kelas harus berupa angka',
        ]);

        try {
            // Cek apakah sudah ada mata pelajaran dengan nama, kelas, dan jurusan yang sama (kecuali yang sedang diedit)
            $exists = Subject::where('name', $validated['name'])
                ->where('major', $validated['major'])
                ->where('kelas', $validated['kelas'] ?? null)
                ->where('id', '!=', $subject->id)
                ->exists();
            
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Mata pelajaran "' . $validated['name'] . '" sudah ada untuk Kelas ' . ($validated['kelas'] ?? 'Umum') . ' Jurusan ' . $validated['major']);
            }

            // Update mata pelajaran
            $subject->update([
                'name' => $validated['name'],
                'major' => $validated['major'],
                'kelas' => $validated['kelas'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.subjects.index')
                ->with('success', 'Mata pelajaran berhasil diperbarui');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui mata pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Hapus mata pelajaran dari database.
     * 
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Subject $subject)
    {
        try {
            // Cek apakah mata pelajaran sedang digunakan
            $usedByTeachers = $subject->teachers()->count();
            $usedByClasses = $subject->teachedClasses()->count();
            
            if ($usedByTeachers > 0 || $usedByClasses > 0) {
                return redirect()->back()
                    ->with('error', 'Mata pelajaran tidak dapat dihapus karena sedang digunakan oleh ' . 
                        ($usedByTeachers > 0 ? "$usedByTeachers guru" : '') . 
                        ($usedByTeachers > 0 && $usedByClasses > 0 ? ' dan ' : '') . 
                        ($usedByClasses > 0 ? "$usedByClasses kelas" : ''));
            }
            
            // Hapus mata pelajaran
            $subject->delete();

            return redirect()->route('admin.subjects.index')
                ->with('success', 'Mata pelajaran berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus mata pelajaran: ' . $e->getMessage());
        }
    }
}
