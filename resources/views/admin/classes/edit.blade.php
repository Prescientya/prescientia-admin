@extends('layouts.app')

@section('title', 'Edit Kelas - SekolahKu Admin')

@section('page-title', 'Edit Kelas')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Edit Data Kelas</h4>
    </div>
    <div class="card-body card-body-form">
        <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h5 class="form-section-title">Informasi Kelas</h5>
                
                <div class="form-group">
                    <label for="class">Kelas <span class="required-field">*</span></label>
                    <select id="class" name="class" class="form-control @error('class') is-invalid @enderror" required>
                        <option value="">Pilih Kelas</option>
                        <option value="10" {{ old('class', $class->class) == 10 ? 'selected' : '' }}>10</option>
                        <option value="11" {{ old('class', $class->class) == 11 ? 'selected' : '' }}>11</option>
                        <option value="12" {{ old('class', $class->class) == 12 ? 'selected' : '' }}>12</option>
                    </select>
                    @error('class')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="major">Jurusan/Program Keahlian</label>
                    <select id="major" name="major" class="form-control @error('major') is-invalid @enderror">
                        <option value="">Pilih Jurusan (opsional)</option>
                        <option value="Kuliner 1" {{ old('major', $class->major) == 'Kuliner 1' ? 'selected' : '' }}>Kuliner 1</option>
                        <option value="Kuliner 2" {{ old('major', $class->major) == 'Kuliner 2' ? 'selected' : '' }}>Kuliner 2</option>
                        <option value="Kuliner 3" {{ old('major', $class->major) == 'Kuliner 3' ? 'selected' : '' }}>Kuliner 3</option>
                        <option value="Kuliner 4" {{ old('major', $class->major) == 'Kuliner 4' ? 'selected' : '' }}>Kuliner 4</option>
                        <option value="Kuliner 5" {{ old('major', $class->major) == 'Kuliner 5' ? 'selected' : '' }}>Kuliner 5</option>
                    </select>
                    @error('major')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Bidang keahlian atau program studi (opsional)</small>
                </div>

                <div class="form-group">
                    <label for="homeroom_teacher_id">Wali Kelas</label>
                    <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="form-control @error('homeroom_teacher_id') is-invalid @enderror">
                        <option value="">Belum ditentukan</option>
                        @foreach($teachers as $teacher)
                            @php
                                $homeroomCount = $teacher->homeroomClasses->count();
                                $hasClass = $homeroomCount > 0;
                                $className = $hasClass ? $teacher->homeroomClasses->first()->class : '';
                                
                                if ($hasClass) {
                                    $label = $teacher->name . ' ' . $teacher->nip . ' (sudah memiliki Kelas ' . $className . ')';
                                } else {
                                    $label = $teacher->name . ' ' . $teacher->nip . ' (Belum Memiliki Kelas)';
                                }
                            @endphp
                            <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('homeroom_teacher_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Guru yang bertugas sebagai wali kelas</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
