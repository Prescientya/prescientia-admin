<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    protected $table = 'students';

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

    /* ── Relationships ──────────────────────────────── */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function classRole()
    {
        return $this->hasOne(StudentClassRole::class, 'student_id');
    }

    /* ── Accessors ──────────────────────────────────── */

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    public function getKelasLengkapAttribute(): string
    {
        if (!$this->schoolClass) return '-';
        $c = $this->schoolClass;
        return $c->class . ($c->major ? ' - ' . $c->major : '');
    }
}
