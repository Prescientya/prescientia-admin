@extends('layouts.app')

@section('title', 'Dashboard - SekolahKu Admin')

@section('content')
<div class="content-header">
    <h1 class="content-title">Dashboard</h1>
    <p class="breadcrumb">Beranda / Dashboard</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            🎓
        </div>
        <div class="stat-info">
            <h3>{{ $totalStudents }}</h3>
            <p>Total Siswa</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            👥
        </div>
        <div class="stat-info">
            <h3>{{ $totalTeachers }}</h3>
            <p>Total Guru</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">
            📚
        </div>
        <div class="stat-info">
            <h3>{{ $totalClasses }}</h3>
            <p>Total Kelas</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            ✅
        </div>
        <div class="stat-info">
            <h3>{{ $studentPresentToday }}</h3>
            <p>Hadir Hari Ini</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Informasi Hari Ini</h2>
        <span class="badge {{ $todayStatus == 'aktif' ? 'badge-success' : 'badge-danger' }}">
            {{ ucfirst($todayStatus) }}
        </span>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div>
                <p style="color: #666; margin-bottom: 8px;">Tanggal</p>
                <h3 style="font-size: 20px;">{{ $todayDate }}</h3>
            </div>
            <div>
                <p style="color: #666; margin-bottom: 8px;">Siswa Hadir</p>
                <h3 style="font-size: 20px; color: #4CAF50;">{{ $studentPresentToday }} Siswa</h3>
            </div>
            <div>
                <p style="color: #666; margin-bottom: 8px;">Guru Hadir</p>
                <h3 style="font-size: 20px; color: #2196F3;">{{ $teacherPresentToday }} Guru</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Selamat Datang, {{ Auth::user()->admin->name ?? Auth::user()->email }}</h2>
    </div>
    <div class="card-body">
        <p>Sistem Manajemen Sekolah - SekolahKu Admin Panel</p>
        <p style="color: #666; margin-top: 10px;">
            Gunakan menu di sebelah kiri untuk mengakses berbagai fitur sistem.
        </p>
    </div>
</div>
@endsection
