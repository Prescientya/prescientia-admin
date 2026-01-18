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

    protected static function booted()
    {
        static::created(function ($student) {
            // assign default role 'Pelajar' via service to enforce class assignment and future logic
            try {
                $service = new \App\Services\StudentRoleService();
                // Assign role regardless of class_id status
                // Student will get role 'Pelajar' immediately upon creation
                $service->assignDefaultRole($student);
            } catch (\Throwable $e) {
                // Log error but don't fail student creation
                \Log::warning('Failed to assign default role to student', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            }
        });
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
