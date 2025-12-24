@extends('layouts.app')

@section('title', 'Detail Kelas - SekolahKu Admin')

@section('page-title', 'Detail Kelas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/classes-show.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Kelas</h5>
            </div>
            <div class="card-body">
                <h4 class="mb-3">{{ $class->class }}</h4>
                
                <div class="info-item mb-3">
                    <label class="text-muted small">Jurusan/Program</label>
                    <p class="mb-0"><strong>{{ $class->major ?? '-' }}</strong></p>
                </div>

                <div class="info-item mb-3">
                    <label class="text-muted small">Wali Kelas</label>
                    @if($class->homeroomTeacher)
                        <p class="mb-0"><strong>{{ $class->homeroomTeacher->name }}</strong></p>
                        <small class="text-muted">{{ $class->homeroomTeacher->nip }}</small>
                    @else
                        <p class="mb-0 text-muted">Belum ada wali kelas</p>
                    @endif
                </div>

                <hr>

                <div class="info-item">
                    <label class="text-muted small">Total Siswa</label>
                    <p class="mb-0">
                        <span class="badge bg-secondary badge-large">{{ $class->students->count() }} Siswa</span>
                    </p>
                </div>

                <hr>

                <div class="action-buttons">
                    <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-warning btn-sm w-50">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm w-50">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Siswa Kelas {{ $class->class }}</h5>
            </div>
            <div class="card-body">
                @if($class->students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Jenis Kelamin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($class->students as $key => $student)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><strong>{{ $student->nis }}</strong></td>
                                        <td>
                                            <a href="{{ route('admin.students.show', $student->id) }}">{{ $student->name }}</a>
                                        </td>
                                        <td>
                                            @if ($student->gender === 'L')
                                                <span class="badge bg-primary">Laki-laki</span>
                                            @else
                                                <span class="badge bg-danger">Perempuan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info text-center mb-0">
                        <p class="mb-0">Belum ada siswa di kelas ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
