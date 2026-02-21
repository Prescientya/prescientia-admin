@extends('layouts.app')

@section('title', 'Detail Guru - SekolahKu Admin')

@section('page-title', 'Detail Guru')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show-pages.css') }}">
@endsection

@section('content')
<div class="show-container">
    <div class="card">
        <div class="card-header card-header-form">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0;">Informasi Lengkap Guru</h4>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body card-body-form">
            <!-- Header with Photo -->
            <div class="show-header">
                <div>
                    @if($teacher->photo_profile)
                        <img src="{{ asset('storage/' . $teacher->photo_profile) }}" alt="Foto Profil" class="show-avatar">
                    @else
                        <div class="show-avatar-placeholder">
                            <i class="bi bi-person-circle"></i>
                        </div>
                    @endif
                </div>
                <div class="show-header-info">
                    <h4>{{ $teacher->name }}</h4>
                    <p><strong>NIP:</strong> {{ $teacher->nip }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $teacher->user?->email ?? '-' }}</p>
                    <div class="badge-row">
                        @foreach($teacher->subjects as $sub)
                            <span class="badge bg-info">{{ $sub->name }}</span>
                        @endforeach
                        <span class="badge bg-primary">Peran: {{ $roleLabel ?? 'Pengajar' }}</span>
                        @if ($teacher->user?->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Data Pribadi Section -->
            <div class="info-section">
                <h5 class="info-section-title">Data Pribadi</h5>
                <div class="info-section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-item-label">Nama Lengkap</span>
                            <span class="info-item-value">{{ $teacher->name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">NIP</span>
                            <span class="info-item-value">{{ $teacher->nip }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Jenis Kelamin</span>
                            <span class="info-item-value">{{ $teacher->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Tanggal Lahir</span>
                            <span class="info-item-value">{{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('d F Y') : '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Umur</span>
                            <span class="info-item-value">{{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->age . ' tahun' : '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">No. Telepon</span>
                            <span class="info-item-value text-muted">{{ $teacher->phone_number ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-grid-full" style="margin-top: 12px;">
                        <div class="info-item">
                            <span class="info-item-label">Alamat</span>
                            <span class="info-item-value text-muted">{{ $teacher->address ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Akun Section -->
            <div class="info-section">
                <h5 class="info-section-title">Informasi Akun</h5>
                <div class="info-section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-item-label">Email</span>
                            <span class="info-item-value">{{ $teacher->user?->email ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Status Akun</span>
                            <span class="info-item-value">
                                @if ($teacher->user?->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Device ID</span>
                            <span class="info-item-value text-muted">{{ $teacher->user?->device_id ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">WiFi MAC</span>
                            <span class="info-item-value text-muted">{{ $teacher->user?->wifi_mac ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Dibuat</span>
                            <span class="info-item-value text-muted">{{ $teacher->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Diperbarui</span>
                            <span class="info-item-value text-muted">{{ $teacher->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary Card -->
            <div class="info-section">
                <h5 class="info-section-title">Ringkasan Kehadiran</h5>
                <div class="info-section-content">
                    <div class="info-grid-3">
                        <div class="info-item">
                            <span class="info-item-label">Total Hadir</span>
                            <span class="info-item-value"><span class="badge bg-success">{{ $attendanceSummary['present'] ?? 0 }}</span></span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Total Izin</span>
                            <span class="info-item-value"><span class="badge bg-warning">{{ $attendanceSummary['permission'] ?? 0 }}</span></span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Total Sakit</span>
                            <span class="info-item-value"><span class="badge bg-info">{{ $attendanceSummary['sick'] ?? 0 }}</span></span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Total Alfa</span>
                            <span class="info-item-value"><span class="badge bg-danger">{{ $attendanceSummary['absent'] ?? 0 }}</span></span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Total Terlambat</span>
                            <span class="info-item-value"><span class="badge bg-warning">{{ $attendanceSummary['late'] ?? 0 }}</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Data
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- Kelas yang Diampu Card -->
    <div class="card" style="margin-top: 16px;">
        <div class="card-header card-header-form">
            <h5 style="margin:0;">Kelas yang Diampu</h5>
        </div>
        <div class="card-body card-body-form">
            @php
                // Filter out roles with no linked class to avoid null references
                $teachingClasses = $teacher->classRoles->where('role', 'pengajar')->filter(function($r) { return $r->class !== null; })->map(function($role) { return $role->class; });
                $homeroomClass = $teacher->homeroomClasses->first();
            @endphp

            @if($teachingClasses->count() > 0)
                <div class="list-group">
                    @foreach($teachingClasses as $class)
                        @if($class)
                        <a href="{{ route('admin.classes.show', $class->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $class->class }}</h6>
                                    <small class="text-muted">{{ $class->major ?? 'Umum' }}</small>
                                </div>
                            </div>
                        </a>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($homeroomClass)
                <div style="margin-top:12px;">
                    <a href="{{ route('admin.classes.show', $homeroomClass->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $homeroomClass->class }}</h6>
                                <small class="text-muted">{{ $homeroomClass->major ?? 'Umum' }}</small>
                            </div>
                            <span class="badge bg-primary">Walikelas</span>
                        </div>
                    </a>
                </div>
            @endif

            @if($teachingClasses->count() == 0 && !$homeroomClass)
                <p class="text-muted text-center">Belum ada kelas yang diampu</p>
            @endif
        </div>
    </div>
</div>
@endsection
