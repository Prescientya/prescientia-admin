<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendancePeriod extends Model
{
    protected $table = 'student_attendance_periods';

    protected $fillable = [
        'attendance_id',
        'class_period_id',
        'status',
    ];

    public const STATUSES = ['hadir', 'sakit', 'izin', 'alpa', 'terlambat', 'dispen'];

    public function attendance()
    {
        return $this->belongsTo(StudentAttendance::class, 'attendance_id');
    }

    public function classPeriod()
    {
        return $this->belongsTo(ClassPeriod::class, 'class_period_id');
    }
}
