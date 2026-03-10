<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'link',
        'release_date',
        'end_date',
        'target_audience',
    ];

    protected $casts = [
        'release_date' => 'date',
        'end_date'     => 'date',
    ];

    /* ── Relationships ──────────────────────────────── */

    public function targets()
    {
        return $this->hasMany(EventTarget::class, 'event_id');
    }

    /* ── Accessors ──────────────────────────────────── */

    public function getAudienceLabelAttribute(): string
    {
        return match ($this->target_audience) {
            'semua' => 'Semua',
            'guru'  => 'Guru',
            'siswa' => 'Siswa',
            'kelas' => 'Kelas Tertentu',
            default => $this->target_audience,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        $today = now()->startOfDay();

        if ($today->lt($this->release_date)) {
            return 'Terjadwal';
        }
        if ($today->gt($this->end_date)) {
            return 'Selesai';
        }
        return 'Aktif';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_label) {
            'Aktif'     => 'success',
            'Terjadwal' => 'warning',
            'Selesai'   => 'muted',
            default     => 'muted',
        };
    }
}
