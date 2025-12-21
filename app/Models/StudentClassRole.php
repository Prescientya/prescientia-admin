<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClassRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'student_id',
        'role',
    ];

    /**
     * Get the class for the role.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the student for the role.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
