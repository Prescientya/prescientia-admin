<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassPeriod extends Model
{
    protected $table = 'class_periods';

    protected $fillable = [
        'day',
        'sequence',
        'start_time',
        'end_time',
        'duration_minutes',
        'activity_type',
        'note',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time'   => 'string',
    ];

    /* ── Constants ─────────────────────────────────────── */

    const DAYS = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

    const DAY_LABELS = [
        'senin'   => 'Senin',
        'selasa'  => 'Selasa',
        'rabu'    => 'Rabu',
        'kamis'   => 'Kamis',
        'jumat'   => 'Jumat',
    ];

    const ACTIVITY_TYPES = [
        'lesson'   => 'Jam Pelajaran',
        'break'    => 'Istirahat',
        'ceremony' => 'Upacara',
        'prayer'   => 'Ibadah / Sholat',
        'cleaning' => 'Kebersihan',
        'other'    => 'Lainnya',
    ];

    const ACTIVITY_COLORS = [
        'lesson'   => 'white',
        'break'    => 'green',
        'ceremony' => 'yellow',
        'prayer'   => 'blue',
        'cleaning' => 'purple',
        'other'    => 'gray',
    ];

    /* ── Accessors ──────────────────────────────────────── */

    public function getDayLabelAttribute(): string
    {
        return self::DAY_LABELS[$this->day] ?? ucfirst($this->day);
    }

    public function getActivityLabelAttribute(): string
    {
        return self::ACTIVITY_TYPES[$this->activity_type] ?? $this->activity_type;
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . ' – ' . substr($this->end_time, 0, 5);
    }

    public function getIsLessonAttribute(): bool
    {
        return $this->activity_type === 'lesson';
    }

    /* ── Scopes ─────────────────────────────────────────── */

    public function scopeForDay($query, string $day)
    {
        return $query->where('day', $day)->orderBy('sequence');
    }

    public function scopeLessonsOnly($query)
    {
        return $query->where('activity_type', 'lesson');
    }
}
