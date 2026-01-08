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
        <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <x-forms.section title="Informasi Akun">
                <x-forms.row-2>
                    <x-forms.field-input 
                        label="Email" 
                        name="email" 
                        type="email"
                        required 
                        :value="old('email', $student->user->email)"
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
                        :value="old('nish', $student->nis)"
                        help="Mengubah NISH akan mengubah password siswa menjadi NISH baru"
                        :error="$errors->first('nish')"
                    />
                    <x-forms.field-input 
                        label="Nama Lengkap" 
                        name="name" 
                        required 
                        :value="old('name', $student->name)"
                        :error="$errors->first('name')"
                    />
                </x-forms.row-2>

                <x-forms.row-2>
                    <x-forms.field-select 
                        label="Jenis Kelamin" 
                        name="gender" 
                        required 
                        :value="old('gender', $student->gender)"
                        :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                        :error="$errors->first('gender')"
                    />
                    <x-forms.field-input 
                        label="Tanggal Lahir" 
                        name="date_of_birth" 
                        type="date"
                        required 
                        :value="old('date_of_birth', $student->date_of_birth)"
                        :error="$errors->first('date_of_birth')"
                    />
                </x-forms.row-2>

                <x-forms.row-2>
                    <x-forms.field-input 
                        label="No. Telepon" 
                        name="phone_number" 
                        type="tel"
                        :value="old('phone_number', $student->phone_number)"
                        :error="$errors->first('phone_number')"
                    />
                    <x-forms.field-select 
                        label="Kelas" 
                        name="class_id" 
                        required 
                        :value="old('class_id', $student->class_id)"
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
                        :value="old('address', $student->address)"
                        :error="$errors->first('address')"
                    />
                </x-forms.row-full>

                <x-forms.row-2>
                    <x-forms.field-select 
                        label="Peran Siswa" 
                        name="role" 
                        :value="old('role', $currentRole)"
                        :options="array_combine($roles, $roles)"
                        help="Ubah peran siswa di kelas saat ini. Kuota peran akan divalidasi."
                        :error="$errors->first('role')"
                    />
                </x-forms.row-2>

                <x-forms.row-full>
                    <x-forms.field-file 
                        label="Foto Profil" 
                        name="photo_profile" 
                        accept="image/*"
                        help="Format: JPG, PNG (Maksimal 2MB)"
                        :preview="$student->photo_profile ? asset('storage/' . $student->photo_profile) : null"
                        previewLabel="Foto saat ini"
                        :error="$errors->first('photo_profile')"
                    />
                </x-forms.row-full>
            </x-forms.section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
