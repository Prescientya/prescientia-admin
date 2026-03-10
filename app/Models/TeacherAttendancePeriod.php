<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendancePeriod extends Model
{
    protected $table = 'teacher_attendance_periods';

    protected $fillable = [
        'teacher_attendance_id',
        'class_period_id',
        'status',
    ];

    public const STATUSES = ['hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat'];

    public function teacherAttendance()
    {
        return $this->belongsTo(TeacherAttendance::class, 'teacher_attendance_id');
    }

    public function classPeriod()
    {
        return $this->belongsTo(ClassPeriod::class, 'class_period_id');
    }
}
