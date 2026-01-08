@extends('layouts.app')

@section('title', 'Tambah Siswa - SekolahKu Admin')

@section('page-title', 'Tambah Siswa')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form-pages.css') }}">
@endsection

@section('content')
<div class="card">
    <div class="card-header card-header-form">
        <h4>Form Tambah Data Siswa</h4>
    </div>
    <div class="card-body card-body-form">
        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <x-forms.section title="Informasi Akun">
                <x-forms.row-2>
                    <x-forms.field-input 
                        label="Email" 
                        name="email" 
                        type="email"
                        required 
                        :error="$errors->first('email')"
                    />
                </x-forms.row-2>
            </x-forms.section>

            <x-forms.section title="Informasi Pribadi">
                <x-forms.row-2>
                    <x-forms.field-input 
                        label="NISH" 
                        name="nish" 
                        required 
                        placeholder="Nomor Identitas Siswa"
                        help="NISH akan otomatis digunakan sebagai password"
                        :error="$errors->first('nish')"
                    />
                    <x-forms.field-input 
                        label="Nama Lengkap" 
                        name="name" 
                        required 
                        :error="$errors->first('name')"
                    />
                </x-forms.row-2>

                <x-forms.row-2>
                    <x-forms.field-select 
                        label="Jenis Kelamin" 
                        name="gender" 
                        required 
                        :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                        :error="$errors->first('gender')"
                    />
                    <x-forms.field-input 
                        label="Tanggal Lahir" 
                        name="date_of_birth" 
                        type="date"
                        required 
                        :error="$errors->first('date_of_birth')"
                    />
                </x-forms.row-2>

                <x-forms.row-2>
                    <x-forms.field-input 
                        label="No. Telepon" 
                        name="phone_number" 
                        type="tel"
                        :error="$errors->first('phone_number')"
                    />
                    <x-forms.field-select 
                        label="Kelas" 
                        name="class_id" 
                        required 
                        :options="$classes->pluck('class', 'id')->mapWithKeys(function($class, $id) {
                            $major = \App\Models\ClassModel::find($id)->major;
                            return [$id => $class . ($major ? ' - ' . $major : '')];
                        })->toArray()"
                        :error="$errors->first('class_id')"
                    />
                </x-forms.row-2>

                <x-forms.row-full>
                    <x-forms.field-textarea 
                        label="Alamat" 
                        name="address" 
                        rows="3"
                        :error="$errors->first('address')"
                    />
                </x-forms.row-full>

                <x-forms.row-full>
                    <x-forms.field-file 
                        label="Foto Profil" 
                        name="photo_profile" 
                        accept="image/*"
                        help="Format: JPG, PNG (Maksimal 2MB)"
                        :error="$errors->first('photo_profile')"
                    />
                </x-forms.row-full>
            </x-forms.section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
