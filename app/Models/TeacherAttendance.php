<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'calendar_id',
        'check_in_time',
        'check_out_time',
        'status',
        'source',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    /**
     * Get the teacher that owns the attendance.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the calendar date for the attendance.
     */
    public function calendar()
    {
        return $this->belongsTo(SchoolCalendar::class, 'calendar_id');
    }

    /**
     * Get the attendance detail.
     */
    public function detail()
    {
        return $this->hasOne(TeacherAttendanceDetail::class, 'attendance_id');
    }

    /**
     * Get the class period for this attendance.
     */
    public function period()
    {
        return $this->belongsTo(ClassPeriod::class, 'period_id');
    }

    /**
     * Scope for present teachers.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'hadir');
    }

    /**
     * Scope for absent teachers.
     */
    public function scopeAbsent($query)
    {
        return $query->whereIn('status', ['sakit', 'izin', 'dinas', 'alpa']);
    }
}
