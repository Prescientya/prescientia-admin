@extends('layouts.app')

@section('title', 'Detail Siswa - SekolahKu Admin')

@section('page-title', 'Detail Siswa')

@section('css')
<link rel="stylesheet" href="{{ asset('css/students-show.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informasi Lengkap Siswa</h5>
                <div>
                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        @if($student->photo_profile)
                            <img src="{{ asset('storage/' . $student->photo_profile) }}" alt="Foto Profil" class="student-photo">
                        @else
                            <div class="student-photo-placeholder">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <h4>{{ $student->name }}</h4>
                        <p class="text-muted">NIS: <strong>{{ $student->nis }}</strong></p>
                        <p class="mb-2">
                            <span class="badge bg-info">{{ $student->class?->name ?? 'Belum ada kelas' }}</span>
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3"><strong>Data Pribadi</strong></h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Nama Lengkap:</strong> {{ $student->name }}<br>
                            <strong>NIS:</strong> {{ $student->nis }}<br>
                            <strong>Jenis Kelamin:</strong> {{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}<br>
                            <strong>Tanggal Lahir:</strong> {{ \Carbon\Carbon::parse($student->date_of_birth)->format('d F Y') }}<br>
                            <strong>Umur:</strong> {{ \Carbon\Carbon::parse($student->date_of_birth)->age }} tahun
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>No. Telepon:</strong> {{ $student->phone_number ?? '-' }}<br>
                            <strong>Kelas:</strong> {{ $student->class?->name ?? '-' }}<br>
                            <strong>Alamat:</strong> {{ $student->address ?? '-' }}<br>
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3"><strong>Informasi Akun</strong></h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Email:</strong> {{ $student->user->email }}<br>
                            <strong>Status:</strong> 
                            @if ($student->user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                            <br>
                            <strong>Dibuat:</strong> {{ $student->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Device ID:</strong> {{ $student->user->device_id ?? '-' }}<br>
                            <strong>WiFi MAC:</strong> {{ $student->user->wifi_mac ?? '-' }}<br>
                            <strong>Terakhir Diperbarui:</strong> {{ $student->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>

                <hr>

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
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan Kehadiran</h5>
            </div>
            <div class="card-body">
                @if($student->attendanceSummary)
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Hadir</span>
                            <span class="badge bg-success">{{ $student->attendanceSummary->total_present ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Izin</span>
                            <span class="badge bg-warning">{{ $student->attendanceSummary->total_permission ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Sakit</span>
                            <span class="badge bg-info">{{ $student->attendanceSummary->total_sick ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Alfa</span>
                            <span class="badge bg-danger">{{ $student->attendanceSummary->total_absent ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Terlambat</span>
                            <span class="badge bg-warning text-dark">{{ $student->attendanceSummary->total_late ?? 0 }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-muted text-center">Data kehadiran belum tersedia</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
