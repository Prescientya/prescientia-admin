@extends('layouts.app')

@section('content')
<div class="content-header">
    <h1>Detail Siswa</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a> / 
        <a href="{{ route('admin.students.index') }}">Data Siswa</a> / 
        Detail
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Informasi Siswa</h3>
                <div class="card-actions">
                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <table class="detail-table">
                    <tr>
                        <th>NIS</th>
                        <td>{{ $student->nis }}</td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td>{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td>
                            <span class="badge {{ $student->gender == 'L' ? 'badge-primary' : 'badge-danger' }}">
                                {{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal Lahir</th>
                        <td>{{ \Carbon\Carbon::parse($student->date_of_birth)->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Umur</th>
                        <td>{{ \Carbon\Carbon::parse($student->date_of_birth)->age }} tahun</td>
                    </tr>
                    <tr>
                        <th>No. Telepon</th>
                        <td>{{ $student->phone_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $student->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td>{{ $student->class->name ?? 'Belum ada kelas' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $student->user->email }}</td>
                    </tr>
                    <tr>
                        <th>Status Akun</th>
                        <td>
                            <span class="badge {{ $student->user->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $student->user->is_active ? 'Aktif' : 'Non-aktif' }}
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
                @if($student->attendanceSummary)
                <div class="stat-item">
                    <div class="stat-label">Total Hadir</div>
                    <div class="stat-value text-success">{{ $student->attendanceSummary->total_present }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Izin</div>
                    <div class="stat-value text-warning">{{ $student->attendanceSummary->total_permission }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Sakit</div>
                    <div class="stat-value text-info">{{ $student->attendanceSummary->total_sick }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Alfa</div>
                    <div class="stat-value text-danger">{{ $student->attendanceSummary->total_absent }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Terlambat</div>
                    <div class="stat-value text-warning">{{ $student->attendanceSummary->total_late }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Persentase Kehadiran</div>
                    <div class="stat-value text-primary">{{ number_format($student->attendanceSummary->attendance_percentage, 1) }}%</div>
                </div>
                @else
                <p class="text-muted">Belum ada data kehadiran</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
