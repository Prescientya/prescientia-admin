<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmitTeacherPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'period_id',
        'day',
        'photo_url',
        'is_present',
        'submitted_at',
    ];

    protected $casts = [
        'is_present' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the teacher for this submission.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class for this submission.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the subject for this submission.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the period for this submission.
     */
    public function period()
    {
        return $this->belongsTo(ClassPeriod::class, 'period_id');
    }
}
