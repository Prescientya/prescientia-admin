<?php

namespace App\Services;

use App\Models\PiringMbg;
use App\Models\MbgClassDaily;
use App\Models\MbgTeacherExcess;
use App\Models\ClassModel;
use Carbon\Carbon;

class MbgService
{
    /**
     * Create daily MBG record with class attendance data.
     * This should be called once per school day.
     */
    public function createDailyMbgRecord($attendanceData = [])
    {
        // Create piring_mbg record for today
        $piringMbg = PiringMbg::create([
            'stok' => 0, // Will be calculated from attended students
            'tanggal_distribusi' => Carbon::today(),
        ]);

        // Get all classes
        $classes = ClassModel::all();

        $totalStok = 0;

        // Create mbg_class_daily record for each class
        foreach ($classes as $class) {
            // Get total students in class
            $totalStudents = $class->students()->count();

            // Get attended students (either from provided data or query attendance)
            $attendedStudents = $attendanceData[$class->id] ?? $this->getAttendedStudentsForClass($class->id);

            // Create the record
            $mbgClassDaily = MbgClassDaily::create([
                'piring_mbg_id' => $piringMbg->id,
                'class_id' => $class->id,
                'total_students' => $totalStudents,
                'attended_students' => $attendedStudents,
                'returned_plates' => 0, // Initially 0, will be updated as plates are returned
            ]);

            $totalStok += $attendedStudents;
        }

        // Update piring_mbg with total stok
        $piringMbg->update(['stok' => $totalStok]);

        return $piringMbg;
    }

    /**
     * Get attended students count for a class on a specific date.
     */
    public function getAttendedStudentsForClass($classId, $date = null)
    {
        $date = $date ?? Carbon::today();

        $class = ClassModel::find($classId);
        if (!$class) {
            return 0;
        }

        // Count students with 'hadir' status on the given date
        $attendedCount = $class->students()
            ->whereHas('attendances', function ($query) use ($date) {
                $query->whereDate('date', $date)
                    ->where('status', 'hadir');
            })
            ->count();

        return $attendedCount;
    }

    /**
     * Update returned plates for a class.
     */
    public function updateReturnedPlates($mbgClassDailyId, $returnedPlates)
    {
        $mbgClassDaily = MbgClassDaily::find($mbgClassDailyId);

        if ($mbgClassDaily) {
            $mbgClassDaily->update(['returned_plates' => $returnedPlates]);
            return $mbgClassDaily;
        }

        return null;
    }

    /**
     * Get today's MBG record with all class data.
     */
    public function getTodayMbgRecord()
    {
        return PiringMbg::where('tanggal_distribusi', Carbon::today())
            ->with('mbgClassDaily.class')
            ->first();
    }

    /**
     * Get MBG statistics for a specific date.
     */
    public function getMbgStatisticsForDate($date)
    {
        $piringMbg = PiringMbg::where('tanggal_distribusi', $date)
            ->with('mbgClassDaily.class')
            ->first();

        if (!$piringMbg) {
            return null;
        }

        $stats = [
            'piring_mbg_id' => $piringMbg->id,
            'date' => $piringMbg->tanggal_distribusi,
            'total_stok' => $piringMbg->stok,
            'total_returned' => $piringMbg->mbgClassDaily()->sum('returned_plates'),
            'total_remaining' => $piringMbg->stok - $piringMbg->mbgClassDaily()->sum('returned_plates'),
            'classes' => $piringMbg->mbgClassDaily,
        ];

        return $stats;
    }

    /**
     * Record excess MBG plates given to teacher.
     */
    public function giveExcessToTeacher($piringMbgId, $teacherId, $quantity, $location, $notes = null)
    {
        $mbgTeacherExcess = MbgTeacherExcess::create([
            'piring_mbg_id' => $piringMbgId,
            'teacher_id' => $teacherId,
            'quantity' => $quantity,
            'location' => $location,
            'notes' => $notes,
        ]);

        return $mbgTeacherExcess;
    }

    /**
     * Get total excess plates given for a specific piring_mbg.
     */
    public function getTotalExcessGiven($piringMbgId)
    {
        return MbgTeacherExcess::where('piring_mbg_id', $piringMbgId)
            ->sum('quantity');
    }

    /**
     * Get all excess records for a specific piring_mbg with teacher details.
     */
    public function getExcessRecordsForDate($date)
    {
        $piringMbg = PiringMbg::where('tanggal_distribusi', $date)->first();

        if (!$piringMbg) {
            return [];
        }

        return $piringMbg->mbgTeacherExcess()->with('teacher')->get();
    }

    /**
     * Calculate remaining plates after class returns and excess given to teachers.
     */
    public function calculateRemainingPlates($piringMbgId)
    {
        $piringMbg = PiringMbg::find($piringMbgId);

        if (!$piringMbg) {
            return 0;
        }

        $totalReturned = $piringMbg->mbgClassDaily()->sum('returned_plates');
        $totalExcessGiven = $this->getTotalExcessGiven($piringMbgId);

        return $piringMbg->stok - $totalReturned - $totalExcessGiven;
    }}