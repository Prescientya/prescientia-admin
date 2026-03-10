@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@push('styles')
<style>{!! file_get_contents(resource_path('views/Dashboard/style.css')) !!}</style>
@endpush

@section('content')

{{-- ── Stat Cards ──────────────────────────────────────── --}}
<div class="dash-grid">

    <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Total Siswa</div>
            <div class="stat-value">{{ number_format($totalSiswa) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--green">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
                <polyline points="16 11 18 13 22 9"/>
            </svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Total Guru</div>
            <div class="stat-value">{{ number_format($totalGuru) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--purple">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Total Kelas</div>
            <div class="stat-value">{{ number_format($totalKelas) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Hadir Hari Ini (Siswa)</div>
            <div class="stat-value">{{ number_format($hadirSiswaHariIni) }}</div>
        </div>
    </div>

</div>

{{-- ── Level Cards ─────────────────────────────────────── --}}
<div class="level-row">
    @foreach ([10, 11, 12] as $lvl)
    @php $ld = $levelData[$lvl] @endphp
    @php $ldClasses = $ld['classes'] @endphp
    <div class="level-card" data-level="{{ $lvl }}"
         data-classes='@json($ldClasses)'>
        <div class="level-card__title">Kelas {{ $lvl }}</div>
        <div class="level-card__stats">
            <div class="level-card__stat">
                <span class="level-card__num">{{ $ld['total_kelas'] }}</span>
                <span class="level-card__lbl">Total Kelas</span>
            </div>
            <div class="level-card__divider"></div>
            <div class="level-card__stat">
                <span class="level-card__num">{{ $ld['total_siswa'] }}</span>
                <span class="level-card__lbl">Total Siswa</span>
            </div>
        </div>
        <div class="level-card__caret">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Class Detail Panel ──────────────────────────────── --}}
<div class="class-detail-panel" id="classDetailPanel" hidden>
    <div class="class-detail-header">
        <span class="class-detail-title" id="classDetailTitle"></span>
        <button class="class-detail-close" id="classDetailClose" aria-label="Tutup">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
    <div class="class-detail-grid" id="classDetailGrid"></div>
</div>

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Dashboard/main.js')) !!}</script>
@endpush
