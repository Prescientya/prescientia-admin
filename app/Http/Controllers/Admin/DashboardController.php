<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\SchoolCalendar;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Get today's calendar entry
        $todayCalendar = SchoolCalendar::whereDate('date', $today)->first();
        
        // Count totals
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassModel::count();
        
        // Count today's attendance (only if it's a school day)
        $studentPresentToday = 0;
        $teacherPresentToday = 0;
        
        if ($todayCalendar && $todayCalendar->status === 'school_day') {
            $studentPresentToday = StudentAttendance::where('calendar_id', $todayCalendar->id)
                ->whereIn('status', ['present', 'late'])
                ->count();
                
            $teacherPresentToday = TeacherAttendance::where('calendar_id', $todayCalendar->id)
                ->whereIn('status', ['present', 'late'])
                ->count();
        }
        
        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'studentPresentToday',
            'teacherPresentToday',
            'todayCalendar'
        ));
    }
}
