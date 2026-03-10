<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $table = 'student_attendances';

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
        'check_in_time'  => 'datetime',
        'check_out_time' => 'datetime',
    ];

    /* ── Status options ─────────────────────────── */
    public const STATUSES = ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'];

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'hadir'     => 'Hadir',
            'sakit'     => 'Sakit',
            'izin'      => 'Izin',
            'alpa'      => 'Alpa',
            'terlambat' => 'Terlambat',
            default     => ucfirst($status),
        };
    }

    /* ── Relationships ──────────────────────────── */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function kelas()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function calendar()
    {
        return $this->belongsTo(SchoolCalendar::class);
    }
}
