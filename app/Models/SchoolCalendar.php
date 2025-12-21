<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolCalendar extends Model
{
    use HasFactory;

    protected $table = 'school_calendar';

    protected $fillable = [
        'date',
        'year',
        'month',
        'day',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get all student attendances for the calendar date.
     */
    public function studentAttendances()
    {
        return $this->hasMany(StudentAttendance::class, 'calendar_id');
    }

    /**
     * Get all teacher attendances for the calendar date.
     */
    public function teacherAttendances()
    {
        return $this->hasMany(TeacherAttendance::class, 'calendar_id');
    }

    /**
     * Scope for active school days.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope for holidays.
     */
    public function scopeHoliday($query)
    {
        return $query->where('status', 'libur');
    }
}
