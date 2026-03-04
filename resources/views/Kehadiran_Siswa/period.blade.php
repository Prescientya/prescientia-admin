@extends('layouts.app')

@section('title', 'Kehadiran Siswa — Per Jam Pelajaran')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Kehadiran</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Kehadiran Siswa</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Per Jam Pelajaran</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Kehadiran_Siswa/period_style.css')) !!}</style>
@endpush

@section('content')
<div class="ka-page" style="display:flex;flex-direction:column;gap:20px;">

    {{-- ── Flash Messages ─────────────────────────────── --}}
    @if(session('success'))
    <div class="alert alert--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Mode Tabs ───────────────────────────────────── --}}
    <div class="mode-tabs">
        <a href="{{ route('attendance.student.index') }}" class="mode-tab">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Per Hari
        </a>
        <a href="{{ route('attendance.student.period') }}" class="mode-tab active">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            Per Jam Pelajaran
        </a>
    </div>

    {{-- ── Info Card ───────────────────────────────────── --}}
    <div class="period-info">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="flex-shrink:0;margin-top:2px;color:var(--accent);">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>
            Klik badge status pada setiap sel untuk mengubah status kehadiran per jam.
            Gunakan tombol <strong>"Auto-isi dari Absensi Harian"</strong> untuk mengisi otomatis berdasarkan data absensi harian yang sudah ada.
            Jika siswa hadir seharian tanpa halangan, semua jam akan otomatis terisi "Hadir".
        </span>
    </div>

    {{-- ── Filter Bar ──────────────────────────────────── --}}
    <form method="GET" action="{{ route('attendance.student.period') }}"
          style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">

        <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:0.84rem;font-weight:600;color:var(--text-secondary);white-space:nowrap;">Tanggal:</label>
            <input type="date" name="date" id="filterDate" class="form-control"
                   value="{{ $date }}" style="width:auto;" onchange="this.form.submit()">
        </div>

        <select name="class_id" id="filterClass" class="dg-filter-select" onchange="this.form.submit()">
            <option value="">— Pilih Kelas —</option>
            @foreach($classes as $kelas)
                <option value="{{ $kelas->id }}" {{ $classId == $kelas->id ? 'selected' : '' }}>
                    Kelas {{ $kelas->class }}{{ $kelas->major ? ' ' . $kelas->major : '' }}
                </option>
            @endforeach
        </select>

        @if($classId && $periods->isNotEmpty())
            <button type="button" class="btn btn--secondary btn--sm" id="btnAutoFill"
                    data-url="{{ route('attendance.student.period.autofill') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                </svg>
                Auto-isi dari Absensi Harian
            </button>
        @endif

        @php
            $dayLabels = [
                'senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu',
                'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu',
            ];
        @endphp
        <span style="margin-left:auto;font-size:0.84rem;color:var(--text-muted);font-weight:500;">
            {{ $dayLabels[$dayId] ?? '–' }},
            {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('D MMMM YYYY') }}
        </span>
    </form>

    {{-- ── Period Grid ─────────────────────────────────── --}}
    @if(!$classId)
        <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.4;">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <p style="margin-top:12px;font-size:0.9rem;">Pilih kelas untuk melihat data kehadiran per jam pelajaran</p>
        </div>
    @elseif($periods->isEmpty())
        <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.4;">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <p style="margin-top:12px;font-size:0.9rem;">Tidak ada jam pelajaran pada hari {{ $dayLabels[$dayId] ?? 'ini' }}</p>
        </div>
    @elseif($students->isEmpty())
        <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
            <p style="font-size:0.9rem;">Tidak ada siswa di kelas ini</p>
        </div>
    @else
        <div class="period-table-wrap">
            <table class="period-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Siswa</th>
                        <th>Harian</th>
                        @foreach($periods as $period)
                            <th>
                                <div class="period-head">
                                    <span class="period-head__jam">Jam {{ $period->sequence + 1 }}</span>
                                    <span class="period-head__time">
                                        {{ \Illuminate\Support\Str::substr($period->start_time, 0, 5) }}–{{ \Illuminate\Support\Str::substr($period->end_time, 0, 5) }}
                                    </span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                    <tr>
                        <td style="color:var(--text-muted);font-size:0.82rem;">{{ $i + 1 }}</td>
                        <td>
                            <span style="font-weight:600;color:var(--text-primary);font-size:0.85rem;">
                                {{ $student->name }}
                            </span>
                            <span style="display:block;font-size:0.73rem;color:var(--text-muted);font-family:monospace;">
                                {{ $student->nis }}
                            </span>
                        </td>
                        <td>
                            @if($dailyStatusMap[$student->id] ?? null)
                                <span class="daily-badge daily-badge--{{ $dailyStatusMap[$student->id] }}">
                                    {{ ucfirst($dailyStatusMap[$student->id]) }}
                                </span>
                            @else
                                <span class="daily-badge daily-badge--none">–</span>
                            @endif
                        </td>
                        @foreach($periods as $period)
                            @php $cellStatus = $periodData[$student->id][$period->id] ?? null; @endphp
                            <td>
                                <span class="status-cell status-cell--{{ $cellStatus ?? 'empty' }}"
                                      data-student-id="{{ $student->id }}"
                                      data-period-id="{{ $period->id }}"
                                      data-store-url="{{ route('attendance.student.period.store') }}">
                                    {{ $cellStatus ? ucfirst($cellStatus) : '—' }}
                                </span>
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Kehadiran_Siswa/period_main.js')) !!}</script>
@endpush
