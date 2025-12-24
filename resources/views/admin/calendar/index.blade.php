@extends('layouts.app')

@section('title', 'Kalender Sekolah - SekolahKu Admin')

@section('page-title', 'Kalender Sekolah')

@section('css')
<style>
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.month-navigation {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.month-navigation h3 {
    margin: 0;
    min-width: 200px;
    text-align: center;
    font-size: 1.5rem;
    color: var(--color-text-dark);
}

.nav-btn {
    padding: 0.5rem 1rem;
    background: var(--color-link);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.nav-btn:hover {
    background: #c73d00;
    color: white;
}

.stats-mini {
    display: flex;
    gap: 1rem;
}

.stat-mini {
    padding: 0.5rem 1rem;
    background: white;
    border-radius: var(--radius-md);
    border-left: 3px solid var(--color-link);
    box-shadow: var(--shadow-sm);
}

.stat-mini.aktif { border-left-color: var(--color-success); }
.stat-mini.libur { border-left-color: var(--color-danger); }

.stat-mini strong {
    display: block;
    font-size: 1.5rem;
    color: var(--color-text-dark);
}

.stat-mini span {
    font-size: 0.75rem;
    color: var(--color-text-light);
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--color-border);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.calendar-day-header {
    background: linear-gradient(135deg, var(--color-link), #c73d00);
    color: white;
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.calendar-day {
    background: white;
    padding: 0.75rem;
    min-height: 100px;
    position: relative;
    cursor: pointer;
    transition: all 0.2s;
}

.calendar-day:hover {
    background: #f8f9fa;
}

.calendar-day.other-month {
    background: #f8f9fa;
    color: #adb5bd;
}

.calendar-day.today {
    background: #fff3e6;
    border: 2px solid var(--color-link);
}

.calendar-day.aktif {
    background: #d4edda;
}

.calendar-day.libur {
    background: #f8d7da;
}

.day-number {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.5rem;
    color: var(--color-text-dark);
}

.day-status {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.day-status.aktif {
    background: var(--color-success);
}

.day-status.libur {
    background: var(--color-danger);
}

.day-description {
    font-size: 0.75rem;
    color: var(--color-text-light);
    margin-top: 0.25rem;
    line-height: 1.3;
}

.legend {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: var(--radius-sm);
}

.legend-color.aktif {
    background: #d4edda;
    border: 1px solid var(--color-success);
}

.legend-color.libur {
    background: #f8d7da;
    border: 1px solid var(--color-danger);
}

.legend-color.today {
    background: #fff3e6;
    border: 2px solid var(--color-link);
}

.add-event-btn {
    padding: 0.625rem 1.25rem;
    background: var(--color-success);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.add-event-btn:hover {
    background: #1e7e34;
    color: white;
}

@media (max-width: 768px) {
    .calendar-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .calendar-day {
        min-height: 80px;
        padding: 0.5rem;
    }
    
    .stats-mini {
        flex-wrap: wrap;
    }
}
</style>
@endsection

@section('content')
<!-- Calendar Header -->
<div class="card">
    <div class="card-body">
        <div class="calendar-header">
            <div class="month-navigation">
                <a href="{{ route('admin.calendar.index', ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year]) }}" 
                   class="nav-btn">
                    ← Bulan Sebelumnya
                </a>
                <h3>{{ \Carbon\Carbon::create($year, $month, 1)->isoFormat('MMMM YYYY') }}</h3>
                <a href="{{ route('admin.calendar.index', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}" 
                   class="nav-btn">
                    Bulan Berikutnya →
                </a>
            </div>
            
            <div class="stats-mini">
                <div class="stat-mini">
                    <strong>{{ $stats['this_month'] }}</strong>
                    <span>Bulan Ini</span>
                </div>
                <div class="stat-mini aktif">
                    <strong>{{ $stats['aktif'] }}</strong>
                    <span>Hari Aktif</span>
                </div>
                <div class="stat-mini libur">
                    <strong>{{ $stats['libur'] }}</strong>
                    <span>Hari Libur</span>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <!-- Day Headers -->
            <div class="calendar-day-header">Minggu</div>
            <div class="calendar-day-header">Senin</div>
            <div class="calendar-day-header">Selasa</div>
            <div class="calendar-day-header">Rabu</div>
            <div class="calendar-day-header">Kamis</div>
            <div class="calendar-day-header">Jumat</div>
            <div class="calendar-day-header">Sabtu</div>

            @php
                $firstDay = \Carbon\Carbon::create($year, $month, 1);
                $lastDay = $firstDay->copy()->endOfMonth();
                $startDate = $firstDay->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                $endDate = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                $currentDate = $startDate->copy();
                $today = \Carbon\Carbon::today()->toDateString();
            @endphp

            @while ($currentDate <= $endDate)
                @php
                    $dateString = $currentDate->toDateString();
                    $isCurrentMonth = $currentDate->month == $month;
                    $isToday = $dateString === $today;
                    $calendarEvent = $calendars->get($dateString);
                    
                    $classes = ['calendar-day'];
                    if (!$isCurrentMonth) $classes[] = 'other-month';
                    if ($isToday) $classes[] = 'today';
                    if ($calendarEvent) $classes[] = $calendarEvent->status;
                @endphp

                <div class="{{ implode(' ', $classes) }}" 
                     onclick="editDate('{{ $dateString }}', '{{ $calendarEvent ? $calendarEvent->status : '' }}', '{{ $calendarEvent ? addslashes($calendarEvent->description) : '' }}')">
                    <div class="day-number">{{ $currentDate->day }}</div>
                    
                    @if ($calendarEvent)
                        <div class="day-status {{ $calendarEvent->status }}"></div>
                        @if ($calendarEvent->description)
                            <div class="day-description">{{ $calendarEvent->description }}</div>
                        @endif
                    @endif
                </div>

                @php
                    $currentDate->addDay();
                @endphp
            @endwhile
        </div>

        <!-- Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color today"></div>
                <span>Hari Ini</span>
            </div>
            <div class="legend-item">
                <div class="legend-color aktif"></div>
                <span>Hari Aktif (Sekolah)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color libur"></div>
                <span>Hari Libur</span>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Edit/Add Event (simple alert for now) -->
@endsection

@section('scripts')
<script>
function editDate(date, status, description) {
    // For now, just show info. Can be enhanced with modal later
    if (status) {
        alert(`Tanggal: ${date}\nStatus: ${status}\nKeterangan: ${description || '-'}`);
    } else {
        alert(`Tanggal: ${date}\nBelum ada data kalender`);
    }
}
</script>
@endsection
