<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentAttendance;
use App\Models\SchoolCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // ── Stat cards ─────────────────────────────────────
        $totalSiswa = Student::count();
        $totalGuru  = Teacher::count();
        $totalKelas = ClassModel::count();

        // Today's calendar entry
        $calendar = SchoolCalendar::where('date', $today)->first();

        $hadirSiswaHariIni = 0;
        if ($calendar) {
            $hadirSiswaHariIni = StudentAttendance::where('calendar_id', $calendar->id)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();
        }

        // ── Class-level cards (10, 11, 12) ─────────────────
        $allClasses = ClassModel::withCount('students')
            ->orderBy('class')
            ->orderBy('major')
            ->get();

        $levelData = [];
        foreach ([10, 11, 12] as $level) {
            $levelClasses = $allClasses->where('class', $level)->values();

            // Per-class today attendance
            $classDetails = $levelClasses->map(function ($kelas) use ($calendar) {
                $counts = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                if ($calendar) {
                    $rows = StudentAttendance::where('class_id', $kelas->id)
                        ->where('calendar_id', $calendar->id)
                        ->select('status', DB::raw('count(*) as total'))
                        ->groupBy('status')
                        ->pluck('total', 'status');
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
