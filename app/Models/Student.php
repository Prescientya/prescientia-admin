<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nis',
        'name',
        'gender',
        'date_of_birth',
        'phone_number',
        'address',
        'class_id',
        'photo_profile',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns the student.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class that the student belongs to.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the class roles for the student.
     */
    public function classRoles()
    {
        return $this->hasMany(StudentClassRole::class);
    }

    /**
     * Get all attendances for the student.
     */
    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    /**
     * Get the attendance summary for the student.
     */
    public function attendanceSummary()
    {
        return $this->hasOne(StudentAttendanceSummary::class);
    }
}
