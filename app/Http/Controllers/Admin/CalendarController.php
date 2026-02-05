<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class CalendarController extends Controller
{
    /**
     * Display the calendar.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        // Get calendar events for the selected month
        $calendars = SchoolCalendar::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function($item) {
                // key by Y-m-d to match the date string used in the view
                return optional($item->date)->toDateString();
            });
        
        // Statistics
        $stats = [
            'total' => SchoolCalendar::count(),
            'aktif' => SchoolCalendar::where('status', 'aktif')->count(),
            'libur' => SchoolCalendar::where('status', 'libur')->count(),
            'this_month' => SchoolCalendar::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->count(),
        ];
        
        return view('admin.calendar.index', compact('calendars', 'month', 'year', 'stats'));
    }

    /**
     * Store a newly created calendar event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:aktif,libur',
            'description' => 'nullable|string',
        ]);

        SchoolCalendar::create($request->all());

        return redirect()->route('admin.calendar.index')
            ->with('success', 'Kalender berhasil ditambahkan');
    }

    /**
     * Update the specified calendar event.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:aktif,libur',
            'description' => 'nullable|string',
        ]);

        $calendar = SchoolCalendar::findOrFail($id);
        $calendar->update($request->all());

        return redirect()->route('admin.calendar.index')
            ->with('success', 'Kalender berhasil diperbarui');
    }

    /**
     * Check if calendar already exists for a specific year
     */
    public function checkCalendarExists(Request $request)
    {
        $year = $request->get('year', now()->year);
        
        // Check if there's any calendar event for the requested year
        $exists = SchoolCalendar::whereYear('date', $year)->exists();
        
        return response()->json([
            'exists' => $exists,
            'year' => $year,
        ]);
    }

    /**
     * Run calendar seeder from admin UI (protected by admin middleware).
     */
    public function seed(Request $request)
    {
        // small confirmation guard (optional)
        // Run seeder in-process using Artisan::call to avoid spawning a separate PHP process
        // which can have different environment and cause DB/socket issues.
        try {
            // allow longer execution time within this request
            if (function_exists('set_time_limit')) {
                @set_time_limit(240);
            }

            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\SchoolCalendarSeeder',
                '--force' => true,
            ]);

            $output = Artisan::output();
            return redirect()->route('admin.calendar.index')
                ->with('success', 'Seeder executed successfully.');
        } catch (\Throwable $e) {
            Log::error('Calendar seeder error: '.$e->getMessage());
            return redirect()->route('admin.calendar.index')
                ->with('error', 'Seeder failed: '.$e->getMessage());
        }
    }

    /**
     * Get the status of a specific date
     */
    public function getDateStatus(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->get('date');
        $calendar = SchoolCalendar::whereDate('date', $date)->first();

        if (!$calendar) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal tidak ditemukan dalam kalender sekolah',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'id' => $calendar->id,
            'date' => $calendar->date->toDateString(),
            'day' => $calendar->day,
            'month' => $calendar->month,
            'year' => $calendar->year,
            'status' => $calendar->status,
            'day_name' => $this->getDayName($calendar->date->dayOfWeek),
            'month_name' => $this->getMonthName($calendar->month),
        ]);
    }

    /**
     * Toggle the status of a specific date
     */
    public function toggleDateStatus($id)
    {
        // Basic validation for id: ensure it's a positive integer and reasonable bigint length
        if (!preg_match('/^\d{1,19}$/', (string) $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid id',
            ], 400);
        }

        $calendar = SchoolCalendar::find($id);

        if (! $calendar) {
            return response()->json([
                'success' => false,
                'message' => 'Calendar entry not found',
            ], 404);
        }

        // Only allow toggling known statuses
        $allowed = ['aktif', 'libur'];
        if (! in_array($calendar->status, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Current status is not allowed to be toggled',
                'current_status' => $calendar->status,
            ], 400);
        }

        $newStatus = $calendar->status === 'aktif' ? 'libur' : 'aktif';
        $calendar->status = $newStatus;
        $calendar->save();

        return response()->json([
            'success' => true,
            'id' => $calendar->id,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Get day name
     */
    private function getDayName($dayOfWeek)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[$dayOfWeek] ?? 'Tidak diketahui';
    }

    /**
     * Get month name
     */
    private function getMonthName($month)
    {
        $months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return $months[$month - 1] ?? 'Tidak diketahui';
    }
}
