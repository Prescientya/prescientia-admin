<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WifiNetwork;
use Illuminate\Http\Request;

class WifiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wifiNetworks = WifiNetwork::paginate(20);
        return view('admin.wifi.index', compact('wifiNetworks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.wifi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ssid' => 'required|string|max:100',
            'bssid' => 'required|string|max:17|unique:wifi_networks,bssid',
            'location' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        WifiNetwork::create($request->all());

        return redirect()->route('admin.wifi.index')
            ->with('success', 'Data WiFi berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $wifi = WifiNetwork::findOrFail($id);
        return view('admin.wifi.edit', compact('wifi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $wifi = WifiNetwork::findOrFail($id);

        $request->validate([
            'ssid' => 'required|string|max:100',
            'bssid' => 'required|string|max:17|unique:wifi_networks,bssid,' . $id,
            'location' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $wifi->update($request->all());

        return redirect()->route('admin.wifi.index')
            ->with('success', 'Data WiFi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $wifi = WifiNetwork::findOrFail($id);
        $wifi->delete();

        return redirect()->route('admin.wifi.index')
            ->with('success', 'Data WiFi berhasil dihapus');
    }
}

