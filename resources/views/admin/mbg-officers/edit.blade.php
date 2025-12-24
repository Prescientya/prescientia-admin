@extends('layouts.app')

@section('title', 'Edit Petugas MBG - SekolahKu Admin')

@section('page-title', 'Edit Petugas MBG')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Form Edit Petugas MBG</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.mbg-officers.update', $officer->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                       id="username" name="username" value="{{ old('username', $officer->username) }}" required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password</small>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" 
                       id="password_confirmation" name="password_confirmation" 
                       placeholder="Kosongkan jika tidak ingin mengubah password">
            </div>

            <hr>

            <div class="alert alert-info">
                <small><strong>Informasi:</strong></small>
                <ul class="mb-0" style="font-size: 0.9rem;">
                    <li>ID: <strong>{{ $officer->id }}</strong></li>
                    <li>Tanggal Dibuat: <strong>{{ $officer->created_at->format('d/m/Y H:i:s') }}</strong></li>
                    <li>Terakhir Diperbarui: <strong>{{ $officer->updated_at->format('d/m/Y H:i:s') }}</strong></li>
                </ul>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.mbg-officers.show', $officer->id) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
