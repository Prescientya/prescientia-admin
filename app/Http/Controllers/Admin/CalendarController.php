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
}
