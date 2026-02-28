<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class DeviceChangeRequest extends Model
{
    protected $table = 'device_change_requests';

    protected $fillable = [
        'user_id',
        'device_id_old',
        'device_id_new',
        'status',
        'submitted_by',
    ];

    /* ── Relationships ──────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): HasOneThrough
    {
        return $this->hasOneThrough(Student::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    /* ── Scopes ──────────────────────────────────────── */
    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeApproved($q)  { return $q->where('status', 'approved'); }
    public function scopeRejected($q)  { return $q->where('status', 'rejected'); }

    /* ── Helpers ─────────────────────────────────────── */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($this->status),
        };
    }
}
