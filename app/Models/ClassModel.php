<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'class',
        'major',
        'homeroom_teacher_id',
    ];

    /**
     * Get the homeroom teacher for the class.
     */
    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /**
     * Get all students in the class.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get the teacher roles for the class.
     */
    public function teacherRoles()
    {
        return $this->hasMany(TeacherClassRole::class, 'class_id');
    }

    /**
     * Get the student roles for the class.
     */
    public function studentRoles()
    {
        return $this->hasMany(StudentClassRole::class, 'class_id');
    }

    /**
     * Get all teachers for the class.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_class_roles')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all attendances for the class.
     */
    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class, 'class_id');
    }

    /**
     * Get the teached classes (teachers assigned to this class).
     */
    public function teachedClasses()
    {
        return $this->hasMany(TeachedClass::class, 'class_id');
    }
}
