<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendanceSummary extends Model
{
    use HasFactory;

    protected $table = 'student_attendance_summary';

    protected $fillable = [
        'student_id',
        'total_hadir',
        'total_izin',
        'total_sakit',
        'total_alpha',
    ];

    protected $casts = [
        'total_hadir' => 'integer',
        'total_izin' => 'integer',
        'total_sakit' => 'integer',
        'total_alpha' => 'integer',
    ];

    /**
     * Get the student that owns the summary.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get total attendance days.
     */
    public function getTotalDaysAttribute()
    {
        return $this->total_hadir + $this->total_izin + $this->total_sakit + $this->total_alpha;
    }

    /**
     * Get attendance percentage.
     */
    public function getAttendancePercentageAttribute()
    {
        $total = $this->getTotalDaysAttribute();
        return $total > 0 ? round(($this->total_hadir / $total) * 100, 2) : 0;
    }
}
