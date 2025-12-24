<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiringMbg extends Model
{
    protected $table = 'piring_mbg';

    protected $fillable = [
        'stok',
        'tanggal_distribusi',
    ];

    protected $casts = [
        'tanggal_distribusi' => 'date',
    ];

    /**
     * Get all class daily records for this piring mbg record.
     */
    public function mbgClassDaily()
    {
        return $this->hasMany(MbgClassDaily::class);
    }

    /**
     * Get all teacher excess records for this piring mbg record.
     */
    public function mbgTeacherExcess()
    {
        return $this->hasMany(MbgTeacherExcess::class);
    }

    /**
     * Calculate total stok from attended students across all classes.
     */
    public function calculateTotalStok()
    {
        return $this->mbgClassDaily()->sum('attended_students');
    }
}
