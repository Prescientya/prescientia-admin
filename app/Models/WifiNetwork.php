<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiNetwork extends Model
{
    protected $table = 'wifi_networks';

    protected $fillable = [
        'ssid',
        'bssid',
        'ip_address',
    ];

    /* ── Relationships ──────────────────────────────── */

    public function presenceLogs()
    {
        return $this->hasMany(WifiPresenceLog::class, 'wifi_id');
    }
}
