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

        <form action="{{ route('admin.teachers.store') }}" method="POST" enctype="multipart/form-data">
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
                        label="NIP" 
                        name="nip" 
                        required 
                        placeholder="Nomor Induk Pegawai"
                        help="NIP akan otomatis digunakan sebagai password"
                        :error="$errors->first('nip')"
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
                    <x-forms.field-select 
                        label="Role" 
                        name="role" 
                        required 
                        :options="['Pengajar' => 'Pengajar', 'Walikelas' => 'Walikelas']"
                        :error="$errors->first('role')"
                    />
                    <x-forms.field-input 
                        label="No. Telepon" 
                        name="phone_number" 
                        type="tel"
                        :error="$errors->first('phone_number')"
                    />
                </x-forms.row-2>

                <x-forms.row-full>
                    <div class="form-group">
                        <label for="departments" class="form-label">
                            Bidang Studi
                            <small class="text-muted">(Dapat menambah lebih dari satu)</small>
                        </label>
                        <div id="departments-container">
                            @php
                                $oldDepartments = old('departments', []);
                            @endphp
                            @if(!empty($oldDepartments) && is_array($oldDepartments))
                                @foreach($oldDepartments as $idx => $val)
                                    <div class="field-group">
                                        <div class="input-group">
                                            <input type="text" name="departments[]" class="form-control department-input @error('departments.*') is-invalid @enderror" placeholder="Contoh: Matematika" value="{{ $val }}">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-department" style="display: none;">Hapus</button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="field-group">
                                    <div class="input-group">
                                        <input type="text" name="departments[]" class="form-control department-input @error('departments.*') is-invalid @enderror" placeholder="Contoh: Matematika" value="">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-department" style="display: none;">Hapus</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" id="add-department" class="btn btn-sm btn-success add-field-btn">+ Tambah Bidang Studi</button>
                        @error('departments.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </x-forms.row-full>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.getElementById('departments-container');
                    const addBtn = document.getElementById('add-department');
                    
                    function updateRemoveButtons() {
                        const groups = container.querySelectorAll('.field-group');
                        groups.forEach(group => {
                            const removeBtn = group.querySelector('.remove-department');
                            removeBtn.style.display = groups.length > 1 ? 'block' : 'none';
                        });
                    }

                    addBtn.addEventListener('click', function() {
                        const newGroup = document.createElement('div');
                        newGroup.className = 'field-group';
                        newGroup.innerHTML = `
                            <div class="input-group">
                                <input type="text" name="departments[]" class="form-control department-input" placeholder="Contoh: Bahasa Indonesia">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-department">Hapus</button>
                            </div>
                        `;
                        container.appendChild(newGroup);
                        updateRemoveButtons();

                        newGroup.querySelector('.remove-department').addEventListener('click', function(e) {
                            e.preventDefault();
                            newGroup.remove();
                            updateRemoveButtons();
                        });
                    });

                    container.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-department')) {
                            e.preventDefault();
                            e.target.closest('.field-group').remove();
                            updateRemoveButtons();
                        }
                    });

                    updateRemoveButtons();
                });
                </script>

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
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
