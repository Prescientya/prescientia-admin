<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WifiPresenceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wifi_id',
        'detected_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
    ];

    /**
     * Get the user that owns the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wifi network for the log.
     */
    public function wifiNetwork()
    {
        return $this->belongsTo(WifiNetwork::class, 'wifi_id');
    }
}
