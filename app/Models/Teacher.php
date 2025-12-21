<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nip',
        'name',
        'gender',
        'date_of_birth',
        'phone_number',
        'address',
        'department',
        'photo_profile',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns the teacher.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the classes where this teacher is the homeroom teacher.
     */
    public function homeroomClasses()
    {
        return $this->hasMany(ClassModel::class, 'homeroom_teacher_id');
    }

    /**
     * Get the class roles for the teacher.
     */
    public function classRoles()
    {
        return $this->hasMany(TeacherClassRole::class);
    }

    /**
     * Get all attendances for the teacher.
     */
    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    /**
     * Get the classes that the teacher teaches.
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'teacher_class_roles')
            ->withPivot('role')
            ->withTimestamps();
    }
}
