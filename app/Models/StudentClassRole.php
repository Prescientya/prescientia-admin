<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassRole extends Model
{
    protected $table = 'student_class_roles';

    protected $fillable = ['class_id', 'student_id', 'role'];

    /* ── Relationships ──────────────────────────────────── */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /* ── Labels ─────────────────────────────────────────── */

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'km'        => 'KM',
            'wakil_km'  => 'Wakil KM',
            'sekretaris' => 'Sekretaris',
            default     => 'Pelajar',
        };
    }

    /** Max holders per role per class */
    public static function limits(): array
    {
        return [
            'km'         => 1,
            'wakil_km'   => 1,
            'sekretaris' => 2,
        ];
    }
}
