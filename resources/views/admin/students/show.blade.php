@extends('layouts.app')

@section('title', 'Detail Siswa - SekolahKu Admin')

@section('page-title', 'Detail Siswa')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show-pages.css') }}">
@endsection

@section('content')
<div class="show-container">
    <div class="card">
        <div class="card-header card-header-form">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0;">Informasi Lengkap Siswa</h4>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body card-body-form">
            <!-- Header with Photo -->
            <div class="show-header">
                <div>
                    @if($student->photo_profile)
                        <img src="{{ asset('storage/' . $student->photo_profile) }}" alt="Foto Profil" class="show-avatar">
                    @else
                        <div class="show-avatar-placeholder">
                            <i class="bi bi-person-circle"></i>
                        </div>
                    @endif
                </div>
                <div class="show-header-info">
                    <h4>{{ $student->name }}</h4>
                    <p><strong>NIS:</strong> {{ $student->nis }}</p>
                    <p><strong>Email:</strong> {{ $student->user->email }}</p>
                    <div class="badge-row">
                        <span class="badge bg-info">{{ $student->class?->name ?? 'Belum ada kelas' }}</span>
                        <span class="badge bg-primary">Peran: {{ $currentRole ?? 'Pelajar' }}</span>
                        @if ($student->user->is_active)
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
                            <span class="info-item-value">{{ $student->name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">NIS</span>
                            <span class="info-item-value">{{ $student->nis }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Jenis Kelamin</span>
                            <span class="info-item-value">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Tanggal Lahir</span>
                            <span class="info-item-value">{{ \Carbon\Carbon::parse($student->date_of_birth)->format('d F Y') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Umur</span>
                            <span class="info-item-value">{{ \Carbon\Carbon::parse($student->date_of_birth)->age }} tahun</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">No. Telepon</span>
                            <span class="info-item-value text-muted">{{ $student->phone_number ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-grid-full" style="margin-top: 12px;">
                        <div class="info-item">
                            <span class="info-item-label">Alamat</span>
                            <span class="info-item-value text-muted">{{ $student->address ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Akademis Section -->
            <div class="info-section">
                <h5 class="info-section-title">Informasi Akademis</h5>
                <div class="info-section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-item-label">Kelas</span>
                            <span class="info-item-value">{{ $student->class?->name ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Peran Siswa</span>
                            <span class="info-item-value">{{ $currentRole ?? 'Pelajar' }}</span>
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
                            <span class="info-item-value">{{ $student->user->email }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Status Akun</span>
                            <span class="info-item-value">
                                @if ($student->user->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Device ID</span>
                            <span class="info-item-value text-muted">{{ $student->user->device_id ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">WiFi MAC</span>
                            <span class="info-item-value text-muted">{{ $student->user->wifi_mac ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Dibuat</span>
                            <span class="info-item-value text-muted">{{ $student->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Diperbarui</span>
                            <span class="info-item-value text-muted">{{ $student->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Data
                </a>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance Summary Card -->
    <div class="card">
        <div class="card-header card-header-form">
            <h5 style="margin: 0;">Ringkasan Kehadiran</h5>
        </div>
        <div class="card-body card-body-form">
            @if($student->attendanceSummary)
                <div class="info-grid-3">
                    <div class="info-item">
                        <span class="info-item-label">Total Hadir</span>
                        <span class="info-item-value">
                            <span class="badge bg-success">{{ $student->attendanceSummary->total_present ?? 0 }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Total Izin</span>
                        <span class="info-item-value">
                            <span class="badge bg-warning">{{ $student->attendanceSummary->total_permission ?? 0 }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Total Sakit</span>
                        <span class="info-item-value">
                            <span class="badge bg-info">{{ $student->attendanceSummary->total_sick ?? 0 }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Total Alfa</span>
                        <span class="info-item-value">
                            <span class="badge bg-danger">{{ $student->attendanceSummary->total_absent ?? 0 }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Total Terlambat</span>
                        <span class="info-item-value">
                            <span class="badge bg-warning">{{ $student->attendanceSummary->total_late ?? 0 }}</span>
                        </span>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <p class="empty-state-text">Data kehadiran belum tersedia</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
