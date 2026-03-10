<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTarget extends Model
{
    protected $table = 'event_targets';

    protected $fillable = [
        'event_id',
        'class_id',
        'grade',
        'major',
    ];

    /* ── Relationships ──────────────────────────────── */

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function targetClass()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /* ── Accessors ──────────────────────────────────── */

    public function getTargetLabelAttribute(): string
    {
        if ($this->class_id && $this->targetClass) {
            $c = $this->targetClass;
            return "Kelas {$c->class}" . ($c->major ? " - {$c->major}" : '');
        }
        if ($this->grade && $this->major) {
            return "Kelas {$this->grade} - {$this->major}";
        }
        if ($this->grade) {
            return "Kelas {$this->grade} (Semua Jurusan)";
        }
        if ($this->major) {
            return "Jurusan {$this->major}";
        }
        return '-';
    }
}
