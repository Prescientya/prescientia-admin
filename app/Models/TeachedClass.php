<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachedClass extends Model
{
    use HasFactory;

    protected $table = 'teached_classes';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'semester',
        'departments',
    ];

    protected $casts = [
        'departments' => 'array', // Akan deprecated, gunakan relasi subjects
    ];

    /**
     * Dapatkan guru yang mengajar di kelas ini.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Dapatkan kelas yang diajar.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Relasi many-to-many dengan mata pelajaran (subjects).
     * 1 teached_class bisa mengajar banyak mata pelajaran.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teached_class')
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * Cek apakah teached_class mengajar mata pelajaran tertentu.
     * Contoh: $teachedClass->teachesSubject($subjectId)
     * 
     * @param int $subjectId ID mata pelajaran
     * @return bool
     */
    public function teachesSubject($subjectId)
    {
        return $this->subjects()->where('subjects.id', $subjectId)->exists();
    }

    /**
     * Ambil daftar nama mata pelajaran yang diajar di teached class ini.
     * Contoh: $teachedClass->getSubjectNames() => ['Matematika', 'Fisika']
     * 
     * @return array
     */
    public function getSubjectNames()
    {
        return $this->subjects()->pluck('name')->toArray();
    }

    /**
     * Ambil daftar ID mata pelajaran yang diajar di teached class ini.
     * Contoh: $teachedClass->getSubjectIds() => [1, 2, 3]
     * 
     * @return array
     */
    public function getSubjectIds()
    {
        return $this->subjects()->pluck('subjects.id')->toArray();
    }
}
