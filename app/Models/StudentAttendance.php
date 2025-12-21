<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
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
     * Get the student that owns the attendance.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class for the attendance.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
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
        return $this->hasOne(StudentAttendanceDetail::class, 'attendance_id');
    }

    /**
     * Scope for present students.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'hadir');
    }

    /**
     * Scope for absent students.
     */
    public function scopeAbsent($query)
    {
        return $query->whereIn('status', ['sakit', 'izin', 'alpa']);
    }

    /**
     * Scope for late students.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'terlambat');
    }
}
