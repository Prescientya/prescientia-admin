<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentAttendance;
use App\Models\SchoolCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // ── Stat cards (cached 5 min — changes rarely within minutes) ──
        $totalSiswa = Cache::remember('dashboard.total_siswa', 300, fn() => Student::count());
        $totalGuru  = Cache::remember('dashboard.total_guru',  300, fn() => Teacher::count());
        $totalKelas = Cache::remember('dashboard.total_kelas', 300, fn() => ClassModel::count());

        // Today's calendar entry
        $calendar = SchoolCalendar::where('date', $today)->first();

        // ── Single query for ALL per-class attendance today ─────────
        $attendanceByClass = collect();
        $hadirSiswaHariIni = 0;

        if ($calendar) {
            $attendanceByClass = StudentAttendance::where('calendar_id', $calendar->id)
                ->select('class_id', 'status', DB::raw('count(*) as total'))
                ->groupBy('class_id', 'status')
                ->get()
                ->groupBy('class_id');

            $hadirSiswaHariIni = $attendanceByClass
                ->flatMap(fn($rows) => $rows->whereIn('status', ['hadir', 'terlambat']))
                ->sum('total');
        }

        // ── Class-level cards (10, 11, 12) ─────────────────
        $allClasses = ClassModel::withCount('students')
            ->orderBy('class')
            ->orderBy('major')
            ->get();

        $levelData = [];
        foreach ([10, 11, 12] as $level) {
            $levelClasses = $allClasses->where('class', $level)->values();

            $classDetails = $levelClasses->map(function ($kelas) use ($attendanceByClass) {
                $counts = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

                // Lookup from the pre-fetched grouped collection — no extra query
                if ($attendanceByClass->has($kelas->id)) {
                    $rows = $attendanceByClass->get($kelas->id)->pluck('total', 'status');
                    foreach ($counts as $k => $_) {
                        $counts[$k] = (int) ($rows[$k] ?? 0);
                    }
                }

                return [
                    'id'          => $kelas->id,
                    'label'       => 'Kelas ' . $kelas->class . ($kelas->major ? ' ' . $kelas->major : ''),
                    'total_siswa' => $kelas->students_count,
                    'hadir'       => $counts['hadir'],
                    'terlambat'   => $counts['terlambat'],
                    'sakit'       => $counts['sakit'],
                    'izin'        => $counts['izin'],
                    'alpa'        => $counts['alpa'],
                ];
            });

            $levelData[$level] = [
                'total_kelas'  => $levelClasses->count(),
                'total_siswa'  => $levelClasses->sum('students_count'),
                'classes'      => $classDetails->values(),
            ];
        }

        return view('Dashboard.dashboard', compact(
            'totalSiswa', 'totalGuru', 'totalKelas',
            'hadirSiswaHariIni', 'levelData'
        ));
    }
}
