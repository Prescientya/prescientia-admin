<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCalendar extends Model
{
    protected $table = 'school_calendar';

    protected $fillable = ['date', 'year', 'month', 'day', 'status'];

    protected $casts = [
        'date' => 'date',
    ];

    /* ── Get or create calendar entry for a given date string (Y-m-d) ── */
    public static function forDate(string $date): self
    {
        $d = \Carbon\Carbon::parse($date);

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
