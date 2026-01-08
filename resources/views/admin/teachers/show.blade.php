@extends('layouts.app')

@section('title', 'Detail Guru - SekolahKu Admin')

@section('page-title', 'Detail Guru')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teachers-show.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informasi Lengkap Guru</h5>
                <div>
                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        @if($teacher->photo_profile)
                            <img src="{{ asset('storage/' . $teacher->photo_profile) }}" alt="Foto Profil" class="teacher-photo">
                        @else
                            <div class="teacher-photo-placeholder">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <h4>{{ $teacher->name }}</h4>
                        <p class="text-muted">NIP: <strong>{{ $teacher->nip }}</strong></p>
                        <p class="mb-2">
                            <span class="badge bg-info">{{ is_array($teacher->department) ? implode(', ', $teacher->department) : ($teacher->department ?? 'Belum ada bidang studi') }}</span>
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3"><strong>Data Pribadi</strong></h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Nama Lengkap:</strong> {{ $teacher->name }}<br>
                            <strong>NIP:</strong> {{ $teacher->nip }}<br>
                            <strong>Jenis Kelamin:</strong> {{ $teacher->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}<br>
                            <strong>Tanggal Lahir:</strong> {{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('d F Y') : '-' }}<br>
                            <strong>Umur:</strong> {{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->age : '-' }} tahun
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>No. Telepon:</strong> {{ $teacher->phone_number ?? '-' }}<br>
                            <strong>Bidang Studi:</strong> {{ is_array($teacher->department) ? implode(', ', $teacher->department) : ($teacher->department ?? '-') }}<br>
                            <strong>Alamat:</strong> {{ $teacher->address ?? '-' }}<br>
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3"><strong>Informasi Akun</strong></h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Email:</strong> {{ $teacher->user->email }}<br>
                            @php
                                $currentRoleRaw = optional($teacher->classRoles->first())->role;
                                $roleLabel = $currentRoleRaw === 'pengajar' ? 'Pengajar' : ($currentRoleRaw === 'wali_kelas' ? 'Walikelas' : 'Pengajar');
                            @endphp
                            <strong>Role:</strong> {{ $roleLabel }}<br>
                            <strong>Status:</strong> 
                            @if ($teacher->user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                            <br>
                            <strong>Dibuat:</strong> {{ $teacher->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Device ID:</strong> {{ $teacher->user->device_id ?? '-' }}<br>
                            <strong>WiFi MAC:</strong> {{ $teacher->user->wifi_mac ?? '-' }}<br>
                            <strong>Terakhir Diperbarui:</strong> {{ $teacher->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>

                <hr>

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
    </div>

    <div class="col-md-4">
        <!-- Kelas yang Diampu (untuk semua guru) -->
        @php
            // Get teaching classes (not homeroom) - use DB values (lowercase)
            $teachingClasses = $teacher->classRoles->where('role', 'pengajar')->map(function($role) {
                return $role->class;
            });
            
            // Get homeroom class
            $homeroomClass = $teacher->homeroomClasses->first();
        @endphp
        
        @if($teachingClasses->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Kelas yang Diampu</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($teachingClasses as $class)
                        <a href="{{ route('admin.classes.show', $class->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $class->class }}</h6>
                                    <small class="text-muted">{{ $class->major ?? 'Umum' }}</small>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Kelas yang Diampu sebagai Walikelas (hanya jika ada homeroom) -->
        @if($homeroomClass)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kelas yang Diampu sebagai Walikelas</h5>
            </div>
            <div class="card-body">
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
        </div>
        @endif

        @if($teachingClasses->count() == 0 && !$homeroomClass)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kelas yang Diampu</h5>
            </div>
            <div class="card-body">
                <p class="text-muted text-center">Belum ada kelas yang diampu</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
