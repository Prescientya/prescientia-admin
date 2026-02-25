<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectClass extends Model
{
    protected $table = 'subject_classes';

    protected $fillable = [
        'subject_id',
        'class_id',
        'jam_ke',
        'time_start',
        'time_end',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
