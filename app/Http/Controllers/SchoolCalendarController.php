<?php

namespace App\Http\Controllers;

use App\Models\SchoolCalendar;
use Database\Seeders\SchoolCalendarSeeder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchoolCalendarController extends Controller
{
    /* ── INDEX ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $currentYear  = (int) ($request->year  ?? date('Y'));
        $currentMonth = (int) ($request->month ?? date('n'));

        // Available years: those that have been generated + current
        $years = SchoolCalendar::selectRaw('DISTINCT year')
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        if (!in_array($currentYear, $years)) {
            $years[] = $currentYear;
            sort($years);
        }

        // Fetch all days of selected month, keyed by day number
        $days = SchoolCalendar::forMonth($currentYear, $currentMonth)
            ->orderBy('day')
            ->get();

        // Map day number → entry for O(1) look-up in the calendar grid
        $daysMap = $days->keyBy('day');

        // Calendar grid helpers
        $firstDayOffset = Carbon::create($currentYear, $currentMonth, 1)->dayOfWeek; // 0=Sun … 6=Sat
        $daysInMonth    = Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;

        // Monthly stats
        $totalDays    = $days->count();
        $holidayDays  = $days->where('status', 'libur')->count();
        $schoolDays   = $days->where('status', 'aktif')->count();

        // Year-level summary
        $yearSummary = SchoolCalendar::forYear($currentYear)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        return view('School_Calendar.index', compact(
            'days', 'daysMap', 'years', 'currentYear', 'currentMonth',
            'firstDayOffset', 'daysInMonth',
            'totalDays', 'holidayDays', 'schoolDays',
            'yearSummary', 'monthNames', 'dayNames'
        ));
    }

    /* ── GENERATE (Buat Calendar) ────────────────────── */

    public function generate(Request $request)
    {
        // Reasonable time limit for calendar generation (120 seconds)
        set_time_limit(120);

        $request->validate([
            'year' => 'required|integer|min:2020|max:2035',
        ], [
            'year.required' => 'Tahun wajib dipilih.',
            'year.min'      => 'Tahun minimal 2020.',
            'year.max'      => 'Tahun maksimal 2035.',
        ]);

        $year = (int) $request->year;

        try {
            $seeder = new SchoolCalendarSeeder();
            $seeder->run($year);

            $total    = SchoolCalendar::forYear($year)->count();
            $holidays = SchoolCalendar::forYear($year)->holidays()->count();
            $school   = SchoolCalendar::forYear($year)->schoolDays()->count();

            return redirect()
                ->route('school-calendar.index', ['year' => $year, 'month' => 1])
                ->with('success', "Kalender tahun {$year} berhasil dibuat: {$total} hari ({$school} hari sekolah, {$holidays} hari libur).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat kalender: ' . $e->getMessage());
        }
    }

    /* ── UPDATE ──────────────────────────────────────── */

    public function update(Request $request, int $id)
    {
        $entry = SchoolCalendar::findOrFail($id);

        $request->validate([
            'status' => 'required|in:aktif,libur',
            'notes'  => 'nullable|string|max:255',
        ]);

        $entry->update([
            'status' => $request->status,
            'notes'  => $request->status === 'aktif' ? null : ($request->notes ?: null),
        ]);

        $date = Carbon::parse($entry->date)->locale('id')->isoFormat('D MMMM YYYY');
        return back()->with('success', "Tanggal {$date} berhasil diperbarui.");
    }

    /* ── DESTROY ─────────────────────────────────────── */

    public function destroy(int $id)
    {
        $entry = SchoolCalendar::findOrFail($id);
        $date  = Carbon::parse($entry->date)->locale('id')->isoFormat('D MMMM YYYY');
        $entry->delete();

        return back()->with('success', "Tanggal {$date} berhasil dihapus dari kalender.");
    }

    /* ── DESTROY YEAR (hapus seluruh tahun) ─────────── */

    public function destroyYear(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2035',
        ]);

        $year    = (int) $request->year;
        $deleted = SchoolCalendar::forYear($year)->count();
        SchoolCalendar::forYear($year)->delete();

        return redirect()
            ->route('school-calendar.index')
            ->with('success', "Seluruh {$deleted} data kalender tahun {$year} berhasil dihapus.");
    }
}
