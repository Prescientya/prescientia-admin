<?php

namespace Database\Seeders;

use App\Models\WifiNetwork;
use Illuminate\Database\Seeder;

class WifiNetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wifiNetworks = [
            [
                'ssid' => 'SEKOLAH-WIFI-1',
                'bssid' => '00:11:22:33:44:55',
                'ip_address' => '192.168.1.1',
            ],
            [
                'ssid' => 'SEKOLAH-WIFI-2',
                'bssid' => '00:11:22:33:44:56',
                'ip_address' => '192.168.1.2',
            ],
            [
                'ssid' => 'SEKOLAH-ADMIN',
                'bssid' => '00:11:22:33:44:57',
                'ip_address' => '192.168.1.3',
            ],
        ];

        foreach ($wifiNetworks as $wifi) {
            WifiNetwork::create($wifi);
        }
    }
}
