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
        $wifiNetworks = WifiNetwork::all();
        
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
        try {
            $validated = $request->validate([
                'ssid' => 'required|string|max:100',
                'bssid' => 'required|string|max:50|unique:wifi_networks,bssid',
                'ip_address' => 'nullable|string|max:45|ip',
            ]);

            WifiNetwork::create([
                'ssid' => $validated['ssid'],
                'bssid' => $validated['bssid'],
                'ip_address' => $validated['ip_address'] ?? null,
            ]);

            // Return JSON response if AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data WiFi berhasil ditambahkan'
                ]);
            }

            return redirect()->route('admin.wifi.index')
                ->with('success', 'Data WiFi berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response if AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        }
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
            'bssid' => 'required|string|max:50|unique:wifi_networks,bssid,' . $id,
            'ip_address' => 'nullable|string|max:45|ip',
        ]);

        $wifi->update([
            'ssid' => $request->ssid,
            'bssid' => $request->bssid,
            'ip_address' => $request->ip_address ?? null,
        ]);

        return redirect()->route('admin.wifi.index')
            ->with('success', 'Data WiFi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
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
