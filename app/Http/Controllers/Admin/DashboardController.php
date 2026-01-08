<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\SchoolCalendar;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Get today's calendar entry
        $todayCalendar = SchoolCalendar::whereDate('date', $today)->first();
        
        // Count totals (exclude soft deleted)
        $totalSiswa = Student::whereNull('deleted_at')->count();
        $totalGuru = Teacher::whereNull('deleted_at')->count();
        $totalKelas = ClassModel::count();
        
        // Count total login today (distinct users)
        $loginHariIni = DB::table('history_login')
            ->whereDate('login_at', $today)
            ->distinct('user_id')
            ->count('user_id');
        
        // Get last 7 days ATTENDANCE statistics (from student_attendances and teacher_attendances)
        $last7Days = [];
        $siswaloginData = [];
        $guruLoginData = [];
        $adminLoginData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $last7Days[] = $date->format('d M');
            
            // Count Siswa kehadiran (distinct students present that day)
            $siswaHadirCount = DB::table('student_attendances')
                ->whereDate('check_in_time', $date)
                ->where('status', 'hadir')
                ->distinct('student_id')
                ->count('student_id');
            $siswaloginData[] = $siswaHadirCount;
            
            // Count Guru kehadiran (distinct teachers present that day)
            $guruHadirCount = DB::table('teacher_attendances')
                ->whereDate('check_in_time', $date)
                ->where('status', 'hadir')
                ->distinct('teacher_id')
                ->count('teacher_id');
            $guruLoginData[] = $guruHadirCount;
            
            // Count Admin logins (from history_login - ada di admin table)
            $adminLoginData[] = DB::table('history_login')
                ->whereDate('history_login.login_at', $date)
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('admins')
                        ->whereColumn('admins.user_id', 'history_login.user_id')
                        ->whereNull('admins.deleted_at');
                })
                ->count();
        }
        
        // Get role distribution for today (from attendance, not login)
        $siswaDist = DB::table('student_attendances')
            ->whereDate('check_in_time', $today)
            ->where('status', 'hadir')
            ->distinct('student_id')
            ->count('student_id');
        
        $guruDist = DB::table('teacher_attendances')
            ->whereDate('check_in_time', $today)
            ->where('status', 'hadir')
            ->distinct('teacher_id')
            ->count('teacher_id');
        
        $adminDist = DB::table('history_login')
            ->whereDate('history_login.login_at', $today)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('admins')
                    ->whereColumn('admins.user_id', 'history_login.user_id')
                    ->whereNull('admins.deleted_at');
            })
            ->count();
        
        $totalLoginDistribusi = $siswaDist + $guruDist + $adminDist;
        
        // Ensure arrays are not empty (prevent JavaScript errors)
        if (empty($siswaloginData)) {
            $siswaloginData = array_fill(0, 7, 0);
        }
        if (empty($guruLoginData)) {
            $guruLoginData = array_fill(0, 7, 0);
        }
        if (empty($adminLoginData)) {
            $adminLoginData = array_fill(0, 7, 0);
        }
        if (empty($last7Days)) {
            $last7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $last7Days[] = $today->copy()->subDays($i)->format('d M');
            }
        }
        
        // System summary
        $tahunAjaran = '2024/2025';
        $semester = 'Ganjil';
        $totalPengguna = $totalSiswa + $totalGuru + 1; // +1 for admin
        
        return view('dashboard', [
            'totalSiswa' => $totalSiswa,
            'totalGuru' => $totalGuru,
            'totalKelas' => $totalKelas,
            'loginHariIni' => $loginHariIni,
            'chartDates' => $last7Days,
            'siswaloginData' => $siswaloginData,
            'guruLoginData' => $guruLoginData,
            'adminLoginData' => $adminLoginData,
            'roleDistributionData' => [$siswaDist, $guruDist, $adminDist],
            'totalLoginDistribusi' => $totalLoginDistribusi,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'totalPengguna' => $totalPengguna,
        ]);
    }
}
