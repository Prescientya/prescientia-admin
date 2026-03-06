<?php

namespace App\Http\Controllers;

use App\Models\WifiNetwork;
use Illuminate\Http\Request;

class WifiNetworkController extends Controller
{
    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = WifiNetwork::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ssid',       'like', "%{$s}%")
                  ->orWhere('bssid',      'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $networks = $query->orderBy('ssid')->paginate(15)->withQueryString();

        return view('Wifi_Networks.index', compact('networks'));
    }

    /* ── STORE ──────────────────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'ssid'       => 'required|string|max:100',
            'bssid'      => 'required|string|max:50|unique:wifi_networks,bssid',
            'ip_address' => 'nullable|ip|max:45',
        ], [
            'ssid.required'    => 'SSID wajib diisi.',
            'bssid.required'   => 'BSSID wajib diisi.',
            'bssid.unique'     => 'BSSID sudah terdaftar.',
            'ip_address.ip'    => 'Format IP address tidak valid.',
        ]);

        WifiNetwork::create([
            'ssid'       => trim($request->ssid),
            'bssid'      => strtoupper(trim($request->bssid)),
            'ip_address' => $request->ip_address ? trim($request->ip_address) : null,
        ]);

        return back()->with('success', "Jaringan WiFi \"{$request->ssid}\" berhasil ditambahkan.");
    }

    /* ── UPDATE ─────────────────────────────────────── */

    public function update(Request $request, WifiNetwork $wifi_network)
    {
        $request->validate([
            'ssid'       => 'required|string|max:100',
            'bssid'      => 'required|string|max:50|unique:wifi_networks,bssid,' . $wifi_network->id,
            'ip_address' => 'nullable|ip|max:45',
        ], [
            'ssid.required'  => 'SSID wajib diisi.',
            'bssid.required' => 'BSSID wajib diisi.',
            'bssid.unique'   => 'BSSID sudah digunakan oleh jaringan lain.',
            'ip_address.ip'  => 'Format IP address tidak valid.',
        ]);

        $wifi_network->update([
            'ssid'       => trim($request->ssid),
            'bssid'      => strtoupper(trim($request->bssid)),
            'ip_address' => $request->ip_address ? trim($request->ip_address) : null,
        ]);

        return back()->with('success', "Jaringan WiFi \"{$wifi_network->ssid}\" berhasil diperbarui.");
    }

    /* ── DESTROY ────────────────────────────────────── */

    public function destroy(WifiNetwork $wifi_network)
    {
        $name = $wifi_network->ssid;
        $wifi_network->delete();

        return back()->with('success', "Jaringan WiFi \"{$name}\" berhasil dihapus.");
    }
}
