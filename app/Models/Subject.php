<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel mata pelajaran (subjects).
 * Mengelola data mata pelajaran yang tersedia di sekolah.
 */
class Subject extends Model
{
    use HasFactory;

    /**
     * Kolom-kolom yang bisa diisi secara mass assignment.
     */
    protected $fillable = [
        'name',        // Nama mata pelajaran
        'major',       // Jurusan yang memiliki mata pelajaran ini
        'kelas',       // Kelas untuk mata pelajaran ini (10, 11, 12, dll) - nullable
        'description', // Deskripsi mata pelajaran
        'is_active',   // Status aktif/tidak
    ];

    /**
     * Casting tipe data untuk kolom-kolom tertentu.
     */
    protected $casts = [
        'is_active' => 'boolean', // Cast ke boolean
    ];

    /**
     * Relasi many-to-many dengan guru (teachers).
     * 1 mata pelajaran bisa diajar oleh banyak guru.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject')
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * Relasi many-to-many dengan teached_classes.
     * 1 mata pelajaran bisa diajar di banyak kelas.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teachedClasses()
    {
        return $this->belongsToMany(TeachedClass::class, 'subject_teached_class')
            ->withTimestamps();
    }

    /**
     * Scope untuk filter mata pelajaran berdasarkan jurusan.
     * Contoh: Subject::forMajor('IPA')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $major Jurusan (IPA, IPS, RPL, dll)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMajor($query, $major)
    {
        return $query->where(function($q) use ($major) {
            $q->where('major', $major)
              ->orWhereNull('major') // Termasuk mata pelajaran umum
              ->orWhere('major', 'Umum');
        });
    }

    /**
     * Scope untuk filter hanya mata pelajaran yang aktif.
     * Contoh: Subject::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor untuk mendapatkan nama lengkap dengan kode.
     * Contoh: "Matematika (MAT)"
     * 
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->code})";
    }
}
