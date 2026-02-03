@extends('layouts.app')

@section('title', 'Dashboard - SekolahKu Admin')

@section('page-title', 'Dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<div class="dashboard-welcome">
    <h2>Selamat Datang, {{ Auth::user()->admin->name ?? Auth::user()->email }}!</h2>
    <p>Sistem Manajemen Sekolah Terpadu</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>{{ \App\Models\Student::count() }}</h3>
            <p>Total Siswa</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>{{ \App\Models\Teacher::count() }}</h3>
            <p>Total Guru</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>{{ \App\Models\ClassModel::count() }}</h3>
            <p>Total Kelas</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>{{ \App\Models\WifiNetwork::count() }}</h3>
            <p>Jaringan WiFi</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Aksi Cepat</h5>
    </div>
    <div class="card-body">
        <div class="quick-actions-grid">
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                Tambah Siswa
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                Tambah Guru
            </a>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                Tambah Kelas
            </a>
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-primary">
                Lihat Absensi
            </a>
        </div>
    </div>
</div>
@endsection
