<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryLogin extends Model
{
    use HasFactory;

    protected $table = 'history_login';

    protected $fillable = [
        'user_id',
        'device_id',
        'wifi_mac',
        'ip_address',
        'login_at',
        'logout_at',
        'duration_minutes',
        'location',
        'status',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * Get the user that owns the login history.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
