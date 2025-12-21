@extends('layouts.app')

@section('content')
<div class="content-header">
    <h1>Detail Kelas</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a> / 
        <a href="{{ route('admin.classes.index') }}">Data Kelas</a> / 
        Detail
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Informasi Kelas</h3>
                <div class="card-actions">
                    <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-warning">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <table class="detail-table">
                    <tr>
                        <th>Nama Kelas</th>
                        <td><strong>{{ $class->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Wali Kelas</th>
                        <td>{{ $class->homeroomTeacher->name ?? 'Belum ada' }}</td>
                    </tr>
                    <tr>
                        <th>NIP Wali Kelas</th>
                        <td>{{ $class->homeroomTeacher->nip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Siswa</th>
                        <td><span class="badge badge-info">{{ $class->students->count() }} siswa</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Daftar Siswa</h3>
            </div>
            <div class="card-body">
                @if($class->students->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class->students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->nis }}</td>
                                <td>{{ $student->name }}</td>
                                <td>
                                    <span class="badge {{ $student->gender == 'L' ? 'badge-primary' : 'badge-danger' }}">
                                        {{ $student->gender == 'L' ? 'L' : 'P' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-info">Detail</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">Belum ada siswa di kelas ini</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
