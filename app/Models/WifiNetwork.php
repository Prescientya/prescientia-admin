<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WifiNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'ssid',
        'bssid',
        'ip_address',
    ];

    /**
     * Get all presence logs for the wifi network.
     */
    public function presenceLogs()
    {
        return $this->hasMany(WifiPresenceLog::class, 'wifi_id');
    }
}
