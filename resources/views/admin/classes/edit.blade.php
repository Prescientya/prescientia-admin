@extends('layouts.app')

@section('content')
<div class="content-header">
    <h1>Edit Kelas</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a> / 
        <a href="{{ route('admin.classes.index') }}">Data Kelas</a> / 
        Edit
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Form Edit Kelas</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.classes.update', $class->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Nama Kelas <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $class->name) }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="homeroom_teacher_id">Wali Kelas</label>
                <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="form-control @error('homeroom_teacher_id') is-invalid @enderror">
                    <option value="">Belum ada wali kelas</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }} ({{ $teacher->nip }})
                    </option>
                    @endforeach
                </select>
                @error('homeroom_teacher_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
