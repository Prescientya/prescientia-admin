<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\VerifiesBulkDelete;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KelasController extends Controller
{
    use VerifiesBulkDelete;

    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = ClassModel::withCount('students');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('major', 'like', "%{$s}%")
                  ->orWhereRaw("CAST(class AS CHAR) LIKE ?", ["%{$s}%"]);
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
            'major' => 'required|string|max:100',
        ], [
            'major.required' => 'Nama Kelas / Jurusan wajib diisi.',
        ]);

        $major = strtoupper(trim($request->major));
        $created = 0;
        $skipped = 0;

        foreach ([10, 11, 12] as $level) {
            $exists = ClassModel::where('class', $level)
                                ->where('major', $major)
                                ->exists();

            if (!$exists) {
                ClassModel::create([
                    'class' => $level,
                    'major' => $major,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        if ($created > 0) {
            Cache::forget('dashboard.total_kelas');
            $msg = "Berhasil membuat {$created} kelas tingkat 10, 11, 12 untuk jurusan {$major}.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} kelas dilewati karena sudah ada).";
            }
            return back()->with('success', $msg);
        }

        return back()->withInput()->with('error', "Semua tingkat kelas untuk jurusan {$major} sudah ada di database.");
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

    public function destroyAll(Request $request)
    {
        $this->verifyBulkDelete($request, 'HAPUS SEMUA KELAS');

        $count = ClassModel::count();
        if ($count < 1) {
            return back()->with('info', 'Tidak ada data kelas untuk dihapus.');
        }

        ClassModel::query()->delete();
        Cache::forget('dashboard.total_kelas');

        return back()->with('success', "Berhasil menghapus semua data kelas ({$count} data).");
    }
}
