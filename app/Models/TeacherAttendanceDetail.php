<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'reason',
        'description',
        'evidence_url',
    ];

    /**
     * Get the attendance that owns the detail.
     */
    public function attendance()
    {
        return $this->belongsTo(TeacherAttendance::class, 'attendance_id');
    }

    /**
     * Get the class period for this attendance detail.
     */
    public function period()
    {
        return $this->belongsTo(ClassPeriod::class, 'period_id');
    }
}
