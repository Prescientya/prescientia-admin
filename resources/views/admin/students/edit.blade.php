@extends('layouts.app')

@section('title', 'Edit Siswa - SekolahKu Admin')

@section('page-title', 'Edit Siswa')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Edit Data Siswa</h4>
    </div>
    <div class="card-body card-body-form">
        <form action="{{ route('admin.students.update', $student->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h5 class="form-section-title">Informasi Akun</h5>
                
                <div class="form-group">
                    <label for="email">Email <span class="required-field">*</span></label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->user->email) }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


            </div>

            <div class="form-section">
                <h5 class="form-section-title-spacing">Informasi Pribadi</h5>
                
                <div class="form-group">
                    <label for="nish">NISH <span class="required-field">*</span></label>
                    <input type="text" id="nish" name="nish" class="form-control @error('nish') is-invalid @enderror" value="{{ old('nish', $student->nis) }}" required>
                    @error('nish')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mengubah NISH akan mengubah password siswa menjadi NISH baru</small>
                </div>

                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="required-field">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin <span class="required-field">*</span></label>
                    <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" {{ old('gender', $student->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $student->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Tanggal Lahir <span class="required-field">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $student->date_of_birth) }}" required>
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_number">No. Telepon</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $student->phone_number) }}">
                    @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $student->address) }}</textarea>
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="class_id">Kelas <span class="required-field">*</span></label>
                    <select id="class_id" name="class_id" class="form-control @error('class_id') is-invalid @enderror" required>
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->class }} {{ $class->major ? '- ' . $class->major : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('class_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="photo_profile">Foto Profil</label>
                    @if($student->photo_profile)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $student->photo_profile) }}" alt="Foto Profil" class="photo-preview">
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
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
