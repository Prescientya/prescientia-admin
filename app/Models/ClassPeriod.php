<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

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
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'sequence' => 'integer',
        'duration_minutes' => 'integer',
    ];

    /**
     * Scope: Get semua periode untuk hari tertentu
     */
    public function scopeByDay($query, string $day)
    {
        return $query->where('day', $day)->orderBy('sequence', 'asc');
    }

    /**
     * Scope: Get hanya periode pelajaran (exclude istirahat, upacara, dll)
     */
    public function scopeLessonOnly($query)
    {
        return $query->where('activity_type', 'lesson');
    }

    /**
     * Scope: Get periode berdasarkan waktu yang sedang berlangsung
     */
    public function scopeCurrentPeriod($query, string $day)
    {
        $now = now()->format('H:i:s');
        return $query->where('day', $day)
                     ->where('start_time', '<=', $now)
                     ->where('end_time', '>', $now)
                     ->first();
    }

    /**
     * Get periode berikutnya setelah periode tertentu
     */
    public function nextPeriod()
    {
        return self::where('day', $this->day)
                   ->where('sequence', '>', $this->sequence)
                   ->orderBy('sequence', 'asc')
                   ->first();
    }

    /**
     * Get periode sebelumnya
     */
    public function previousPeriod()
    {
        return self::where('day', $this->day)
                   ->where('sequence', '<', $this->sequence)
                   ->orderBy('sequence', 'desc')
                   ->first();
    }

    /**
     * Format readable untuk tampilan
     */
    public function getTimeRangeAttribute(): string
    {
        $start = $this->start_time instanceof Carbon 
            ? $this->start_time->format('H:i') 
            : date('H:i', strtotime($this->start_time));
        
        $end = $this->end_time instanceof Carbon 
            ? $this->end_time->format('H:i') 
            : date('H:i', strtotime($this->end_time));
        
        return "{$start} - {$end}";
    }

    /**
     * Get label untuk jenis aktivitas
     */
    public function getActivityLabelAttribute(): string
    {
        $labels = [
            'lesson' => 'Pelajaran',
            'break' => 'Istirahat',
            'ceremony' => 'Upacara',
            'prayer' => 'Ibadah',
            'cleaning' => 'Kebersihan',
            'other' => 'Lainnya',
        ];

        return $labels[$this->activity_type] ?? $this->activity_type;
    }

    /**
     * Check apakah saat ini termasuk jam pelajaran
     */
    public static function isSchoolHours(): bool
    {
        $today = now()->format('l');
        $dayMap = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
        ];

        $day = $dayMap[$today] ?? null;

        if (!$day) {
            return false; // Weekend
        }

        $now = now()->format('H:i:s');

        return self::where('day', $day)
                   ->where('start_time', '<=', $now)
                   ->where('end_time', '>', $now)
                   ->exists();
    }

    /**
     * Get total jam pelajaran per hari (exclude break/ceremony/dll)
     */
    public static function totalLessonHoursByDay(string $day): int
    {
        return self::byDay($day)
                   ->lessonOnly()
                   ->sum('duration_minutes');
    }
}
