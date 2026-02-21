@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@push('styles')
<style>{!! file_get_contents(resource_path('views/Dashboard/style.css')) !!}</style>
@endpush

@section('content')

<div class="dash-grid">

    {{-- Stat Cards --}}
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
            <div class="stat-value">—</div>
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
            <div class="stat-value">—</div>
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
            <div class="stat-value">—</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="stat-body">
            <div class="stat-label">Hadir Hari Ini</div>
            <div class="stat-value">—</div>
        </div>
    </div>

</div>

{{-- Welcome Card --}}
<div class="welcome-card">
    <div class="welcome-body">
        <h2 class="welcome-title">Selamat datang, {{ Auth::guard('admin')->user()->admin->name ?? 'Admin' }}!</h2>
        <p class="welcome-sub">Kelola data sekolah Anda dengan mudah melalui panel admin Prescientia.</p>
    </div>
    <div class="welcome-illustration">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
            style="opacity:0.15">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
    </div>
</div>

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Dashboard/main.js')) !!}</script>
@endpush