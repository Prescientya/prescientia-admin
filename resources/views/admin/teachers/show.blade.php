@extends('layouts.app')

@section('content')
<div class="content-header">
    <h1>Detail Guru</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a> / 
        <a href="{{ route('admin.teachers.index') }}">Data Guru</a> / 
        Detail
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Informasi Guru</h3>
                <div class="card-actions">
                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-warning">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <table class="detail-table">
                    <tr>
                        <th>NIP</th>
                        <td>{{ $teacher->nip }}</td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td>{{ $teacher->name }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td>
                            <span class="badge {{ $teacher->gender == 'L' ? 'badge-primary' : 'badge-danger' }}">
                                {{ $teacher->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>No. Telepon</th>
                        <td>{{ $teacher->phone_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $teacher->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $teacher->user->email }}</td>
                    </tr>
                    <tr>
                        <th>Status Akun</th>
                        <td>
                            <span class="badge {{ $teacher->user->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $teacher->user->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Statistik Kehadiran</h3>
            </div>
            <div class="card-body">
                @if($teacher->attendanceSummary)
                <div class="stat-item">
                    <div class="stat-label">Total Hadir</div>
                    <div class="stat-value text-success">{{ $teacher->attendanceSummary->total_present }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Izin</div>
                    <div class="stat-value text-warning">{{ $teacher->attendanceSummary->total_permission }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Sakit</div>
                    <div class="stat-value text-info">{{ $teacher->attendanceSummary->total_sick }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Alfa</div>
                    <div class="stat-value text-danger">{{ $teacher->attendanceSummary->total_absent }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Terlambat</div>
                    <div class="stat-value text-warning">{{ $teacher->attendanceSummary->total_late }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Persentase Kehadiran</div>
                    <div class="stat-value text-primary">{{ number_format($teacher->attendanceSummary->attendance_percentage, 1) }}%</div>
                </div>
                @else
                <p class="text-muted">Belum ada data kehadiran</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
