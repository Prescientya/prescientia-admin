<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherClassRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'role',
    ];

    /**
     * Get the teacher for the role.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class for the role.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
