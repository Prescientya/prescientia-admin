<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AutoAttendanceMarking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if attendance marking has already run today
        $cacheKey = 'attendance_marking_ran_' . now()->format('Y-m-d');
        
        // If not in cache, run attendance marking and cache it
        if (!Cache::has($cacheKey)) {
            // Only run if current time is >= 00:05 WIB
            $currentTime = now('Asia/Jakarta');
            $minRunTime = $currentTime->copy()->setTime(0, 5, 0);
            
            if ($currentTime->greaterThanOrEqualTo($minRunTime)) {
                try {
                    // Pass today's date, command will subtract 1 day to get yesterday
                    $today = $currentTime->toDateString();
                    
                    // Run the attendance:mark-absent command
                    Artisan::call('attendance:mark-absent', [
                        '--date' => $today,
                    ]);
                    
                    // Cache for the rest of the day
                    Cache::put($cacheKey, true, now('Asia/Jakarta')->endOfDay());
                    
                } catch (\Exception $e) {
                    // Log error but don't crash the request
                    \Illuminate\Support\Facades\Log::error(
                        'AutoAttendanceMarking middleware error: ' . $e->getMessage()
                    );
                }
            }
        }

        return $next($request);
    }
}
