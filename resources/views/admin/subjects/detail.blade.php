@extends('layouts.app')

@section('title', 'Detail Mata Pelajaran - SekolahKu Admin')

@section('page-title', 'Detail Mata Pelajaran')

@section('css')
<link rel="stylesheet" href="{{ asset('css/classes.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Mata Pelajaran</h5>
        <div>
            <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.subjects.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Kode</th>
                        <td><span class="badge bg-secondary fs-6">{{ $subject->code }}</span></td>
                    </tr>
                    <tr>
                        <th>Nama Mata Pelajaran</th>
                        <td><strong>{{ $subject->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Jurusan</th>
                        <td>{{ $subject->major }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($subject->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ $subject->description ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Statistik Penggunaan</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-3 bg-white">
                                    <h3 class="text-primary mb-0">{{ $subject->teachers->count() }}</h3>
                                    <small class="text-muted">Guru</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 bg-white">
                                    <h3 class="text-success mb-0">{{ $subject->teachedClasses->count() }}</h3>
                                    <small class="text-muted">Kelas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guru yang mengajar -->
        <div class="mt-4">
            <h6 class="border-bottom pb-2">Guru yang Mengajar Mata Pelajaran Ini</h6>
            @if($subject->teachers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Guru</th>
                                <th>NIP</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subject->teachers as $key => $teacher)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $teacher->name }}</td>
                                    <td>{{ $teacher->nip }}</td>
                                    <td>{{ $teacher->user->email ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada guru yang mengajar mata pelajaran ini.
                </div>
            @endif
        </div>

        <!-- Kelas yang menggunakan -->
        <div class="mt-4">
            <h6 class="border-bottom pb-2">Kelas yang Menggunakan Mata Pelajaran Ini</h6>
            @if($subject->teachedClasses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Kelas</th>
                                <th>Jurusan</th>
                                <th>Tahun Ajaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subject->teachedClasses as $key => $teachedClass)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $teachedClass->classModel->name }}</td>
                                    <td>{{ $teachedClass->classModel->major }}</td>
                                    <td>{{ $teachedClass->year }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada kelas yang menggunakan mata pelajaran ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
