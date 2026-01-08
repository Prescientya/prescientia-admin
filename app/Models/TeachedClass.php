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
        'departments' => 'array',
    ];

    /**
     * Get the teacher that teaches this class.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the class being taught.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
