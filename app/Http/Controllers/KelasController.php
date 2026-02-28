<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KelasController extends Controller
{
    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = ClassModel::withCount('students');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('major', 'ilike', "%{$s}%")
                  ->orWhereRaw("CAST(class AS TEXT) ILIKE ?", ["%{$s}%"]);
            });
        }

        if ($request->filled('tingkat')) {
            $query->where('class', $request->tingkat);
        }

        $classes = $query->orderBy('class')->orderBy('major')->paginate(15)->withQueryString();

        return view('Data_Kelas.index', compact('classes'));
    }

    /* ── STORE ──────────────────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'class' => 'required|integer|in:10,11,12',
            'major' => 'required|string|max:100',
        ], [
            'class.required' => 'Tingkat kelas wajib dipilih.',
            'class.in'       => 'Tingkat kelas hanya boleh 10, 11, atau 12.',
            'major.required' => 'Jurusan wajib diisi.',
        ]);

        $exists = ClassModel::where('class', $request->class)
                            ->where('major', $request->major)
                            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', "Kelas {$request->class} - {$request->major} sudah ada.");
        }

        ClassModel::create([
            'class' => $request->class,
            'major' => strtoupper(trim($request->major)),
        ]);

        Cache::forget('dashboard.total_kelas');
        return back()->with('success', "Kelas {$request->class} - {$request->major} berhasil ditambahkan.");
    }

    /* ── UPDATE ─────────────────────────────────────── */

    public function update(Request $request, ClassModel $kelas)
    {
        $request->validate([
            'class' => 'required|integer|in:10,11,12',
            'major' => 'required|string|max:100',
        ], [
            'class.required' => 'Tingkat kelas wajib dipilih.',
            'class.in'       => 'Tingkat kelas hanya boleh 10, 11, atau 12.',
            'major.required' => 'Jurusan wajib diisi.',
        ]);

        $exists = ClassModel::where('class', $request->class)
                            ->where('major', $request->major)
                            ->where('id', '!=', $kelas->id)
                            ->exists();

        if ($exists) {
            return back()->with('error', "Kelas {$request->class} - {$request->major} sudah ada.");
        }

        $kelas->update([
            'class' => $request->class,
            'major' => strtoupper(trim($request->major)),
        ]);

        return back()->with('success', "Kelas {$kelas->class} - {$kelas->major} berhasil diperbarui.");
    }

    /* ── DESTROY ────────────────────────────────────── */

    public function destroy(ClassModel $kelas)
    {
        $studentCount = $kelas->students()->count();

        if ($studentCount > 0) {
            return back()->with('error',
                "Kelas {$kelas->class} - {$kelas->major} tidak dapat dihapus karena masih memiliki {$studentCount} siswa."
            );
        }

        $name = "{$kelas->class} - {$kelas->major}";
        $kelas->delete();

        Cache::forget('dashboard.total_kelas');
        return back()->with('success', "Kelas {$name} berhasil dihapus.");
    }
}
