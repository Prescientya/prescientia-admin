<?php

namespace App\Providers;

use App\Models\StudentAttendance;
use App\Observers\StudentAttendanceObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS for asset URLs when APP_URL uses https
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        
        // Register observers
        StudentAttendance::observe(StudentAttendanceObserver::class);
        // Use custom pagination view across the app
        Paginator::defaultView('vendor.pagination.custom');
        
        // Share recent activities with all views for header notification
        View::composer('*', function($view) {
            $recentActivities = $this->getRecentActivities();
            $view->with('recentActivities', $recentActivities);
        });
    }
    
    /**
     * Get recent activities from last 24 hours
     */
    private function getRecentActivities()
    {
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();
        
        // Student check-ins from last 24 hours
        $studentCheckins = DB::table('student_attendances')
            ->join('students', 'student_attendances.student_id', '=', 'students.id')
            ->where('student_attendances.status', 'hadir')
            ->where('student_attendances.created_at', '>=', $yesterday)
            ->select(
                DB::raw("'student_checkin' as type"),
                'students.name',
                'student_attendances.created_at as timestamp',
                DB::raw("'#3B82F6' as color"),
                DB::raw("CONCAT(students.name, ' - Hadir') as title")
            );
        
        // Teacher check-ins from last 24 hours
        $teacherCheckins = DB::table('teacher_attendances')
            ->join('teachers', 'teacher_attendances.teacher_id', '=', 'teachers.id')
            ->where('teacher_attendances.status', 'hadir')
            ->where('teacher_attendances.created_at', '>=', $yesterday)
            ->select(
                DB::raw("'teacher_checkin' as type"),
                'teachers.name',
                'teacher_attendances.created_at as timestamp',
                DB::raw("'#10B981' as color"),
                DB::raw("CONCAT(teachers.name, ' - Hadir') as title")
            );
        
        // Union all activities and sort by timestamp (newest first)
        $recentActivities = $studentCheckins
            ->union($teacherCheckins)
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->get()
            ->map(function($activity) {
                $now = Carbon::now();
                $timestamp = Carbon::parse($activity->timestamp);
                $diffMinutes = $timestamp->diffInMinutes($now);
                
                if ($diffMinutes < 1) {
                    $timeAgo = 'Baru saja';
                } elseif ($diffMinutes < 60) {
                    $timeAgo = $diffMinutes . ' menit lalu';
                } elseif ($diffMinutes < 1440) {
                    $hours = intval($diffMinutes / 60);
                    $timeAgo = $hours . ' jam lalu';
                } else {
                    $days = intval($diffMinutes / 1440);
                    $timeAgo = $days . ' hari lalu';
                }
                
                $activity->time_ago = $timeAgo;
                $activity->description = $timeAgo;
                return $activity;
            })
            ->toArray();
        
        return $recentActivities;
    }
}
