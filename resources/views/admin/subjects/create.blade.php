@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran - SekolahKu Admin')

@section('page-title', 'Tambah Mata Pelajaran')

@section('css')
<link rel="stylesheet" href="{{ asset('css/classes.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Form Tambah Mata Pelajaran</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.subjects.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label required">Nama Mata Pelajaran</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kelas" class="form-label">Kelas</label>
                        <select class="form-select @error('kelas') is-invalid @enderror" id="kelas" name="kelas">
                            <option value="">-- Berlaku Semua Kelas --</option>
                            @foreach($kelases as $kls)
                                <option value="{{ $kls }}" {{ old('kelas') == $kls ? 'selected' : '' }}>
                                    Kelas {{ $kls }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilih kelas spesifik atau kosongkan untuk semua kelas</div>
                        @error('kelas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="major" class="form-label required">Jurusan</label>
                        <select class="form-select @error('major') is-invalid @enderror" id="major" name="major" required>
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach($majors as $major)
                                <option value="{{ $major }}" {{ old('major') == $major ? 'selected' : '' }}>
                                    {{ $major }}
                                </option>
                            @endforeach
                        </select>
                        @error('major')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                       {{ old('is_active', '1') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Aktifkan mata pelajaran ini
                </label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
