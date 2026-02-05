<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAssignTeacherScheduleRequest;
use App\Imports\TeacherScheduleImport;
use App\Models\ClassModel;
use App\Models\ClassPeriod;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTeacherScheduleController extends Controller
{
    /**
     * Display the schedule management page.
     */
    public function index(Request $request)
    {
        $schedules = TeacherClassSchedule::with(['teacher', 'class', 'subject', 'period'])
            ->orderBy('day')
            ->orderBy('period_id')
            ->paginate(20);
        
        $teachers = Teacher::orderBy('name')->get();
        $classes = ClassModel::orderBy('class')->orderBy('major')->get();
        $subjects = Subject::orderBy('name')->get();
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $semesters = [1, 2];
        
        return view('admin.teacher-schedules.index', compact(
            'schedules', 
            'teachers', 
            'classes', 
            'subjects', 
            'days', 
            'semesters'
        ));
    }

    /**
     * Get periods for a specific day (AJAX).
     */
    public function getPeriodsByDay(Request $request)
    {
        $day = $request->input('day');
        
        $periods = ClassPeriod::where('day', $day)
            ->where('activity_type', 'belajar') // Only learning periods
            ->orderBy('sequence')
            ->get();
        
        return response()->json(['periods' => $periods]);
    }

    /**
     * Store bulk schedules.
     */
    public function storeBulk(BulkAssignTeacherScheduleRequest $request)
    {
        $validated = $request->validated();
        
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($validated['period_ids'] as $periodId) {
                try {
                    TeacherClassSchedule::create([
                        'teacher_id' => $validated['teacher_id'],
                        'class_id' => $validated['class_id'],
                        'subject_id' => $validated['subject_id'],
                        'period_id' => $periodId,
                        'day' => $validated['day'],
                        'semester' => $validated['semester'],
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    // Likely duplicate, skip
                    $skipped++;
                }
            }
            
            DB::commit();
            
            $message = "Berhasil: {$inserted} jadwal ditambahkan.";
            if ($skipped > 0) {
                $message .= " {$skipped} jadwal dilewati (duplikat).";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Import schedules from Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);
        
        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $import = new TeacherScheduleImport($worksheet);
            $result = $import->process();
            
            $message = "Import selesai! Inserted: {$result['inserted']}, Skipped: {$result['skipped']}, Failed: {$result['failed']}";
            
            if (!empty($result['failures'])) {
                $message .= " (Ada " . count($result['failures']) . " baris error)";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template.
     */
    public function downloadTemplate()
    {
        $filePath = resource_path('templates/teacher_schedule_template.xlsx');
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Template file tidak ditemukan.');
        }
        
        return response()->download($filePath);
    }

    /**
     * Delete a schedule.
     */
    public function destroy(TeacherClassSchedule $schedule)
    {
        $schedule->delete();
        
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus.');
    }
}
