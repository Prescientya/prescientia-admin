@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran - SekolahKu Admin')

@section('page-title', 'Edit Mata Pelajaran')

@section('css')
<link rel="stylesheet" href="{{ asset('css/classes.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Form Edit Mata Pelajaran</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.subjects.update', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label required">Nama Mata Pelajaran</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name', $subject->name) }}" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label required">Kode Mata Pelajaran</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               id="code" name="code" value="{{ old('code', $subject->code) }}" maxlength="10" 
                               placeholder="Contoh: MAT, BIO, FIS" required style="text-transform: uppercase;">
                        <div class="form-text">Maksimal 10 karakter, akan otomatis uppercase</div>
                        @error('code')
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
                                <option value="{{ $major }}" {{ old('major', $subject->major) == $major ? 'selected' : '' }}>
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
                          id="description" name="description" rows="3">{{ old('description', $subject->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                       {{ old('is_active', $subject->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Aktifkan mata pelajaran ini
                </label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto uppercase kode
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
@endsection
