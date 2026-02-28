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

    // Note: WifiPresenceLog model is in the student/teacher app, not admin.
    // Relation kept commented for reference.
    // public function presenceLogs()
    // {
    //     return $this->hasMany(WifiPresenceLog::class, 'wifi_id');
    // }
}
