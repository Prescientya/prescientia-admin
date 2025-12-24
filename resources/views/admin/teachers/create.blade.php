@extends('layouts.app')

@section('title', 'Tambah Guru - SekolahKu Admin')

@section('page-title', 'Tambah Guru')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Tambah Data Guru</h4>
    </div>
    <div class="card-body card-body-form">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.teachers.store') }}" method="POST">
            @csrf
            
            <div class="form-section">
                <h5 class="form-section-title">Informasi Akun</h5>
                
                <div class="form-group">
                    <label for="email">Email <span class="required-field">*</span></label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


            </div>

            <div class="form-section">
                <h5 class="form-section-title-spacing">Informasi Pribadi</h5>
                
                <div class="form-group">
                    <label for="nip">NIP <span class="required-field">*</span></label>
                    <input type="text" id="nip" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai" required>
                    @error('nip')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">NIP akan otomatis digunakan sebagai password</small>
                </div>

                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="required-field">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin <span class="required-field">*</span></label>
                    <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Tanggal Lahir <span class="required-field">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}" required>
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="role">Role <span class="required-field">*</span></label>
                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                        <option value="">Pilih Role</option>
                        <option value="Pengajar" {{ old('role') == 'Pengajar' ? 'selected' : '' }}>Pengajar</option>
                        <option value="Walikelas" {{ old('role') == 'Walikelas' ? 'selected' : '' }}>Walikelas</option>
                    </select>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="department">Bidang Studi</label>
                    <input type="text" id="department" name="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department') }}" placeholder="Contoh: Matematika, Bahasa Indonesia">
                    @error('department')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="subject">Mata Pelajaran</label>
                    <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="Contoh: Matematika, Bahasa Indonesia">
                    @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_number">No. Telepon</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number') }}">
                    @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="photo_profile">Foto Profil</label>
                    <input type="file" id="photo_profile" name="photo_profile" class="form-control @error('photo_profile') is-invalid @enderror" accept="image/*">
                    @error('photo_profile')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Format: JPG, PNG (Maksimal 2MB)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
