<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MbgClassDaily extends Model
{
    protected $table = 'mbg_class_daily';

    protected $fillable = [
        'piring_mbg_id',
        'class_id',
        'total_students',
        'attended_students',
        'returned_plates',
        'class_code',
        'student_representative',
    ];

    /**
     * Get the piring mbg that this record belongs to.
     */
    public function piringMbg()
    {
        return $this->belongsTo(PiringMbg::class);
    }

    /**
     * Get the class that this record belongs to.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    /**
     * Calculate remaining plates (attended - returned).
     */
    public function getRemainingPlates()
    {
        return $this->attended_students - $this->returned_plates;
    }
}
