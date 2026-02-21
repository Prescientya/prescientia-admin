<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

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
        'deleted_at'    => 'datetime',
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
