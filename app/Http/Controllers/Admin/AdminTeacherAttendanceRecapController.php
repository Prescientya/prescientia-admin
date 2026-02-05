<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolCalendar;
use App\Models\SubmitTeacherPeriod;
use App\Models\Teacher;
use App\Models\TeacherClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTeacherAttendanceRecapController extends Controller
{
    /**
     * Display attendance recap.
     */
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $dateObj = \Carbon\Carbon::parse($selectedDate);
        $dayName = $this->getDayName($dateObj->dayOfWeek);
        
        // Get all teachers
        $teachers = Teacher::orderBy('name')->get();
        
        $recapData = [];
        
        foreach ($teachers as $teacher) {
            // Get teacher's schedules for this day
            $schedules = TeacherClassSchedule::with(['class', 'subject', 'period'])
                ->where('teacher_id', $teacher->id)
                ->where('day', $dayName)
                ->orderBy('period_id')
                ->get();
            
            if ($schedules->isEmpty()) {
                continue; // Skip teachers with no schedule on this day
            }
            
            // Get all submissions for this teacher on this day
            $submissions = SubmitTeacherPeriod::where('teacher_id', $teacher->id)
                ->where('day', $dayName)
                ->whereDate('submitted_at', $selectedDate)
                ->get()
                ->keyBy('period_id');
            
            // Determine first period status
            $firstPeriod = $schedules->first();
            $firstSubmission = $submissions->get($firstPeriod->period_id);
            
            $firstPeriodStatus = 'Belum Absen';
            if ($firstSubmission) {
                $firstPeriodStatus = $firstSubmission->is_present ? 'Hadir' : 'Alpha';
            }
            
            // Prepare detail data
            $details = [];
            foreach ($schedules as $schedule) {
                $submission = $submissions->get($schedule->period_id);
                
                $details[] = [
                    'period_sequence' => $schedule->period->sequence,
                    'period_time' => $schedule->period->start_time . ' – ' . $schedule->period->end_time,
                    'class_name' => $schedule->class->class . ' ' . ($schedule->class->major ?? ''),
                    'subject_name' => $schedule->subject->name,
                    'status' => $submission ? ($submission->is_present ? 'Hadir' : 'Alpha') : 'Belum Absen',
                    'photo_url' => $submission->photo_url ?? null,
                    'submitted_at' => $submission ? $submission->submitted_at->format('H:i') : null,
                ];
            }
            
            $recapData[] = [
                'teacher' => $teacher,
                'first_period_status' => $firstPeriodStatus,
                'details' => $details,
            ];
        }
        
        return view('admin.attendance.recap', compact('recapData', 'selectedDate', 'dayName'));
    }

    /**
     * Convert day of week number to Indonesian day name.
     */
    private function getDayName($dayOfWeek)
    {
        $days = [
            0 => 'minggu', // Sunday
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];
        
        return $days[$dayOfWeek] ?? 'senin';
    }
}
