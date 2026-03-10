<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    protected $table = 'teacher_attendances';

    protected $fillable = [
        'teacher_id',
        'calendar_id',
        'period_id',
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
    public const STATUSES = ['hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat'];

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'hadir'     => 'Hadir',
            'sakit'     => 'Sakit',
            'izin'      => 'Izin',
            'dinas'     => 'Dinas',
            'alpa'      => 'Alpa',
            'terlambat' => 'Terlambat',
            default     => ucfirst($status),
        };
    }

    /* ── Relationships ──────────────────────────── */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function calendar()
    {
        return $this->belongsTo(SchoolCalendar::class);
    }
}
