@extends('layouts.app')

@section('title', 'Edit Guru - SekolahKu Admin')

@section('page-title', 'Edit Guru')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Edit Data Guru</h4>
    </div>
    <div class="card-body card-body-form">
        <form action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h5 class="form-section-title">Informasi Akun</h5>
                
                <div class="form-group">
                    <label for="email">Email <span class="required-field">*</span></label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $teacher->user->email) }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


            </div>

            <div class="form-section">
                <h5 class="form-section-title-spacing">Informasi Pribadi</h5>
                
                <div class="form-group">
                    <label for="nip">NIP <span class="required-field">*</span></label>
                    <input type="text" id="nip" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $teacher->nip) }}" required>
                    @error('nip')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mengubah NIP akan mengubah password guru menjadi NIP baru</small>
                </div>

                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="required-field">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $teacher->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin <span class="required-field">*</span></label>
                    <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" {{ old('gender', $teacher->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $teacher->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Tanggal Lahir <span class="required-field">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $teacher->date_of_birth) }}" required>
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @php
                    $currentRoleRaw = optional($teacher->classRoles->first())->role;
                    $currentRoleLabel = $currentRoleRaw === 'pengajar' ? 'Pengajar' : ($currentRoleRaw === 'wali_kelas' ? 'Walikelas' : '');
                @endphp
                <div class="form-group">
                    <label for="role">Role <span class="required-field">*</span></label>
                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                        <option value="">Pilih Role</option>
                        <option value="Pengajar" {{ old('role', $currentRoleLabel) == 'Pengajar' ? 'selected' : '' }}>Pengajar</option>
                        <option value="Walikelas" {{ old('role', $currentRoleLabel) == 'Walikelas' ? 'selected' : '' }}>Walikelas</option>
                    </select>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="department">Bidang Studi</label>
                    <input type="text" id="department" name="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department', $teacher->department) }}" placeholder="Contoh: Matematika, Bahasa Indonesia">
                    @error('department')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_number">No. Telepon</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $teacher->phone_number) }}">
                    @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $teacher->address) }}</textarea>
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="photo_profile">Foto Profil</label>
                    @if($teacher->photo_profile)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $teacher->photo_profile) }}" alt="Foto Profil" class="photo-preview">
                            <p class="text-muted small mt-2">Foto saat ini</p>
                        </div>
                    @endif
                    <input type="file" id="photo_profile" name="photo_profile" class="form-control @error('photo_profile') is-invalid @enderror" accept="image/*">
                    @error('photo_profile')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Format: JPG, PNG (Maksimal 2MB)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
