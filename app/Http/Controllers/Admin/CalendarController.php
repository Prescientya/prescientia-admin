<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolCalendar;
use Illuminate\Http\Request;

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
                return $item->date;
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
}
