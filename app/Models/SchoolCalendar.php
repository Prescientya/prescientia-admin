<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SchoolCalendar extends Model
{
    protected $table = 'school_calendar';

    protected $fillable = ['date', 'year', 'month', 'day', 'status', 'notes'];

    protected $casts = [
        'date'  => 'date',
        'year'  => 'integer',
        'month' => 'integer',
        'day'   => 'integer',
    ];

    /* ── Accessors ───────────────────────────────────── */

    /** Indonesian day name (Senin–Minggu) */
    public function getDayNameAttribute(): string
    {
        $map = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        return $map[Carbon::parse($this->date)->format('l')] ?? '-';
    }

    public function getIsHolidayAttribute(): bool
    {
        return $this->status === 'libur';
    }

    /* ── Scopes ──────────────────────────────────────── */

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeHolidays($query)
    {
        return $query->where('status', 'libur');
    }

    public function scopeSchoolDays($query)
    {
        return $query->where('status', 'aktif');
    }

    /* ── Static helpers ──────────────────────────────── */

    /** Get or create calendar entry for a given date string (Y-m-d) */
    public static function forDate(string $date): self
    {
        $d = Carbon::parse($date);

        return self::firstOrCreate(
            ['date' => $d->toDateString()],
            [
                'year'   => $d->year,
                'month'  => $d->month,
                'day'    => $d->day,
                'status' => 'aktif',
            ]
        );
    }
}
