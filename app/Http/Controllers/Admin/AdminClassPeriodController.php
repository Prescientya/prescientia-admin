<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassPeriodRequest;
use App\Models\ClassPeriod;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminClassPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Dapatkan hari ini dalam bahasa Indonesia
        $today = strtolower(\Carbon\Carbon::now()->locale('id')->translatedFormat('l'));
        
        // Mapping hari ke bahasa Indonesia
        $dayMapping = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        
        $dayEnglish = strtolower(\Carbon\Carbon::now()->format('l'));
        $currentDay = $dayMapping[$dayEnglish] ?? $today;
        
        // Ambil jadwal hari ini saja
        $todayPeriods = ClassPeriod::where('day', $currentDay)
                                   ->orderBy('sequence')
                                   ->get();
        
        return view('admin.class-periods.index', [
            'currentDay' => $currentDay,
            'todayPeriods' => $todayPeriods,
            'dayName' => ucfirst($currentDay)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $activityTypes = ['belajar', 'istirahat', 'shalat'];
        
        return view('admin.class-periods.create', compact('days', 'activityTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassPeriodRequest $request)
    {
        ClassPeriod::create($request->validated());
        
        return redirect()->route('admin.class-periods.index')
            ->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassPeriod $classPeriod)
    {
        return view('admin.class-periods.show', compact('classPeriod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassPeriod $classPeriod)
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $activityTypes = ['belajar', 'istirahat', 'shalat'];
        
        return view('admin.class-periods.edit', compact('classPeriod', 'days', 'activityTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClassPeriodRequest $request, ClassPeriod $classPeriod)
    {
        $classPeriod->update($request->validated());
        
        return redirect()->route('admin.class-periods.index')
            ->with('success', 'Jam pelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassPeriod $classPeriod)
    {
        $classPeriod->delete();
        
        return redirect()->route('admin.class-periods.index')
            ->with('success', 'Jam pelajaran berhasil dihapus.');
    }
}
