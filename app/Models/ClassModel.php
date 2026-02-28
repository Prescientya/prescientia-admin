<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'class',
        'major',
        'homeroom_teacher_id',
    ];

    /* ── Relationships ──────────────────────────────── */

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /* ── Accessors ──────────────────────────────────── */

    public function getFullNameAttribute(): string
    {
        return 'Kelas ' . $this->class . ($this->major ? ' ' . $this->major : '');
    }

    public function getShortNameAttribute(): string
    {
        return $this->class . ($this->major ? ' - ' . $this->major : '');
    }
}
