<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject');
    }

    public function subjectClasses()
    {
        return $this->hasMany(SubjectClass::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'subject_classes', 'subject_id', 'class_id')
                    ->withTimestamps();
    }

    /** Jumlah kelas yang diajarkan mapel ini */
    public function getJumlahKelasAttribute(): int
    {
        return $this->classes()->count();
    }
}
