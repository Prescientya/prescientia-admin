<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenceLetter extends Model
{
    protected $table = 'absence_letters';

    protected $fillable = [
        'user_type',
        'student_id',
        'teacher_id',
        'class_id',
        'calendar_id',
        'date',
        'reason',
        'description',
        'status',
        'approved_by_wali',
        'approved_by_wali_at',
        'approved_by_admin',
        'approved_by_admin_at',
        'rejected_by',
        'rejected_by_role',
        'rejected_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_by_wali_at' => 'datetime',
        'approved_by_admin_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];
}
