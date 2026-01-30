<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WifiNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class WifiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wifiNetworks = WifiNetwork::all();
        $detectedNetworks = [];
        $error = null;

        // Scan WiFi networks
        try {
            $detectedNetworks = $this->scanWifiNetworks();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error('WiFi Scan Error: ' . $error);
        }

        // Filter detected networks - hanya tampilkan yang belum ada di database
        $detectedNetworksFiltered = collect($detectedNetworks)
            ->filter(function ($network) use ($wifiNetworks) {
                // Ambil semua BSSID yang sudah ada di database
                $existingBssids = $wifiNetworks->pluck('bssid')->toArray();
                
                // Filter BSSID yang belum ada
                $newBssids = collect($network['bssids'])
                    ->reject(function ($bssid) use ($existingBssids) {
                        return in_array($bssid['bssid'], $existingBssids);
                    });

                // Hanya kembalikan network jika ada BSSID yang baru
                if ($newBssids->count() > 0) {
                    $network['bssids'] = $newBssids->values()->toArray();
                    return true;
                }
                return false;
            })
            ->values()
            ->toArray();

        return view('admin.wifi.index', compact('wifiNetworks', 'detectedNetworksFiltered', 'error'));
    }

    /**
     * Scan available WiFi networks
     */
    private function scanWifiNetworks()
    {
        $pythonScript = base_path('python_scripts/scan_wifi.py');
        
        // Check if script exists
        if (!file_exists($pythonScript)) {
            throw new \Exception("Script Python tidak ditemukan di: $pythonScript");
        }

        $process = null;
        $lastError = null;
        
        // List of Python executables to try
        $pythonExes = [
            'python',
            'python3',
            'C:\\Users\\fahma\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
        ];
        
        foreach ($pythonExes as $pythonExe) {
            try {
                $command = "\"$pythonExe\" \"$pythonScript\"";
                $process = Process::fromShellCommandline($command);
                $process->setTimeout(15);
                $process->run();
                
                if ($process->isSuccessful()) {
                    break; // Success, exit loop
                } else {
                    $lastError = "[$pythonExe] " . ($process->getErrorOutput() ?: 'Unknown error');
                }
            } catch (\Exception $e) {
                $lastError = "[$pythonExe] Exception: " . $e->getMessage();
            }
        }

        // Check if any process was successful
        if (!$process || !$process->isSuccessful()) {
            $errorOutput = $process ? $process->getErrorOutput() : 'Process tidak tersedia';
            $lastErrorMsg = $lastError ?: 'Error tidak diketahui';
            
            throw new \Exception(
                "Gagal menjalankan script Python. " .
                "Last Error: $lastErrorMsg. " .
                "Error Output: $errorOutput"
            );
        }

        $output = $process->getOutput();
        
        if (empty($output)) {
            throw new \Exception('Script Python tidak menghasilkan output. Pastikan netsh command tersedia.');
        }

        $networks = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                'Gagal memparse output Python: ' . json_last_error_msg() . 
                '. Output: ' . substr($output, 0, 500)
            );
        }

        return $networks ?? [];
    }

    /**
     * Convert detected WiFi to stored network
     */
    public function convert(Request $request)
    {
        $request->validate([
            'ssid' => 'required|string|max:100',
            'bssid' => 'required|string|max:50|unique:wifi_networks,bssid',
        ]);

        try {
            WifiNetwork::create([
                'ssid' => $request->ssid,
                'bssid' => $request->bssid,
                'ip_address' => $request->ip_address ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WiFi berhasil ditambahkan ke database',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan WiFi: ' . $e->getMessage(),
            ], 400);
        }
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
