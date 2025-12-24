<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MbgTeacherExcess extends Model
{
    protected $table = 'mbg_teacher_excess';

    protected $fillable = [
        'piring_mbg_id',
        'teacher_id',
        'quantity',
        'location',
        'notes',
    ];

    /**
     * Get the piring mbg that this record belongs to.
     */
    public function piringMbg()
    {
        return $this->belongsTo(PiringMbg::class);
    }

    /**
     * Get the teacher that received the excess plates.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
