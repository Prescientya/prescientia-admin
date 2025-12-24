<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\HistoryLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Detect device information
            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());
            
            $deviceInfo = $agent->device() ?: 'Unknown';
            $platform = $agent->platform();
            $browser = $agent->browser();
            $deviceId = sprintf('%s - %s (%s)', $deviceInfo, $platform, $browser);

            // Create login history record
            HistoryLogin::create([
                'user_id' => Auth::id(),
                'login_at' => Carbon::now(),
                'ip_address' => $request->ip(),
                'device_id' => $deviceId,
                'status' => 'success',
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        // Update login history - set logout time and calculate duration
        $lastLogin = HistoryLogin::where('user_id', Auth::id())
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();

        if ($lastLogin) {
            $logoutAt = Carbon::now();
            $duration = (int) $logoutAt->diffInMinutes($lastLogin->login_at);
            
            $lastLogin->update([
                'logout_at' => $logoutAt,
                'duration_minutes' => $duration,
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
