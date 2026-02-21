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
        
        // Count students by class grade (using integer class column)
        $siswaKelas10 = Student::whereNull('deleted_at')
            ->whereHas('class', function($query) {
                $query->where('class', 10);
            })->count();
            
        $siswaKelas11 = Student::whereNull('deleted_at')
            ->whereHas('class', function($query) {
                $query->where('class', 11);
            })->count();
            
        $siswaKelas12 = Student::whereNull('deleted_at')
            ->whereHas('class', function($query) {
                $query->where('class', 12);
            })->count();

        // Count total classes by grade
        $totalKelas10 = ClassModel::where('class', 10)->count();
        $totalKelas11 = ClassModel::where('class', 11)->count();
        $totalKelas12 = ClassModel::where('class', 12)->count();
        
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
            'siswaKelas10' => $siswaKelas10,
            'siswaKelas11' => $siswaKelas11,
            'siswaKelas12' => $siswaKelas12,
            'totalKelas10' => $totalKelas10,
            'totalKelas11' => $totalKelas11,
            'totalKelas12' => $totalKelas12,
        ]);
    }

    /**
     * Get detail classes by grade (JSON endpoint)
     */
    public function getClassesByGrade($grade)
    {
        $today = Carbon::today();
        // Get today's calendar entry to determine whether today is active or holiday
        $todayCalendar = SchoolCalendar::whereDate('date', $today)->first();
        $dayStatus = $todayCalendar ? $todayCalendar->status : 'aktif';
        $dayStatusLabel = $dayStatus === 'libur' ? 'Hari Libur' : 'Hari Masuk';
        
        // Get all classes with the specified grade
        $classes = ClassModel::where('class', (int)$grade)
            ->with(['students' => function($query) {
                $query->whereNull('deleted_at');
            }])
            ->orderBy('major')
            ->get();

        $classesData = $classes->map(function($class) use ($today, $dayStatus, $dayStatusLabel) {
            $totalSiswa = $class->students->count();
            
            // Count students present today (unless today is a holiday)
            $siswaHadir = 0;
            $siswaBelumHadir = 0;
            if ($dayStatus !== 'libur') {
                $siswaHadir = DB::table('student_attendances')
                    ->whereDate('check_in_time', $today)
                    ->where('status', 'hadir')
                    ->whereIn('student_id', $class->students->pluck('id'))
                    ->distinct('student_id')
                    ->count('student_id');

                $siswaBelumHadir = $totalSiswa - $siswaHadir;
            }
            
            // Get class info from database columns
            $grade = $class->class; // This is integer (10, 11, 12)
            $major = $class->major ?? 'Umum'; // This is string (TKJ, RPL, etc.)
            
            // Generate display name
            $displayName = $grade . ' ' . $major;
            
            return [
                'id' => $class->id,
                'name' => $displayName,
                'grade' => $grade,
                'major' => $major,
                'displayName' => $displayName,
                'totalSiswa' => $totalSiswa,
                'siswaHadir' => $siswaHadir,
                'siswaBelumHadir' => $siswaBelumHadir,
                // include day status (frontend will use this to render badge)
                'dayStatus' => $dayStatus ?? 'aktif',
                'dayStatusLabel' => $dayStatusLabel ?? 'Hari Masuk',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $classesData,
        ]);
    }

    /**
     * Get teacher statistics (JSON endpoint)
     */
    public function getTeacherStats()
    {
        $today = Carbon::today();
        
        $totalGuru = Teacher::whereNull('deleted_at')->count();
        
        // Count teachers present today
        $guruHadir = DB::table('teacher_attendances')
            ->whereDate('check_in_time', $today)
            ->where('status', 'hadir')
            ->distinct('teacher_id')
            ->count('teacher_id');
        
        $guruTidakHadir = $totalGuru - $guruHadir;
        
        return response()->json([
            'success' => true,
            'data' => [
                'totalGuru' => $totalGuru,
                'guruHadir' => $guruHadir,
                'guruTidakHadir' => $guruTidakHadir,
            ],
        ]);
    }

    /**
     * Get students in specific class with attendance status (JSON endpoint)
     */
    public function getClassStudents($classId)
    {
        $today = Carbon::today();
        
        // Get class with students
        $class = ClassModel::with(['students' => function($query) {
            $query->whereNull('deleted_at')->orderBy('name');
        }])->findOrFail($classId);

        $studentsData = $class->students->map(function($student) use ($today) {
            // Check if student attended today
            $attendance = DB::table('student_attendances')
                ->whereDate('check_in_time', $today)
                ->where('student_id', $student->id)
                ->where('status', 'hadir')
                ->first();
            
            $checkInTime = null;
            if ($attendance) {
                $checkInTime = Carbon::parse($attendance->check_in_time)->format('H:i');
            }
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn,
                'isPresent' => $attendance ? true : false,
                'checkInTime' => $checkInTime,
                'status' => $attendance ? 'Hadir' : 'Belum Hadir',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'class' => [
                    'id' => $class->id,
                    'name' => $class->class,
                    'totalStudents' => $studentsData->count(),
                    'presentCount' => $studentsData->where('isPresent', true)->count(),
                    'absentCount' => $studentsData->where('isPresent', false)->count(),
                ],
                'students' => $studentsData,
            ],
        ]);
    }
}
