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
            
            <x-forms.section title="Informasi Akun">
                <x-forms.row-2>
                    <x-forms.field-input 
                        label="Email" 
                        name="email" 
                        type="email"
                        required 
                        :value="old('email', $teacher->user->email)"
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
                        :value="old('nip', $teacher->nip)"
                        help="Mengubah NIP akan mengubah password guru menjadi NIP baru"
                        :error="$errors->first('nip')"
                    />
                    <x-forms.field-input 
                        label="Nama Lengkap" 
                        name="name" 
                        required 
                        :value="old('name', $teacher->name)"
                        :error="$errors->first('name')"
                    />
                </x-forms.row-2>

                <x-forms.row-2>
                    <x-forms.field-select 
                        label="Jenis Kelamin" 
                        name="gender" 
                        required 
                        :value="old('gender', $teacher->gender)"
                        :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                        :error="$errors->first('gender')"
                    />
                    <x-forms.field-input 
                        label="Tanggal Lahir" 
                        name="date_of_birth" 
                        type="date"
                        required 
                        :value="old('date_of_birth', optional($teacher->date_of_birth)->format('Y-m-d'))"
                        :error="$errors->first('date_of_birth')"
                    />
                </x-forms.row-2>

                @php
                    $currentRoleRaw = optional($teacher->classRoles->first())->role;
                    $mapped = $currentRoleRaw === 'pengajar' ? 'Pengajar' : ($currentRoleRaw === 'wali_kelas' ? 'Walikelas' : null);
                    $currentRoleLabel = old('role', $mapped ?? 'Pengajar');
                @endphp
                <x-forms.row-2>
                    <x-forms.field-select 
                        label="Role" 
                        name="role" 
                        required 
                        :value="$currentRoleLabel"
                        :options="['Pengajar' => 'Pengajar', 'Walikelas' => 'Walikelas']"
                        :error="$errors->first('role')"
                    />
                    <x-forms.field-input 
                        label="No. Telepon" 
                        name="phone_number" 
                        type="tel"
                        :value="old('phone_number', $teacher->phone_number)"
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
                                $deptOld = old('departments');
                                if (is_array($deptOld) && count($deptOld) > 0) {
                                    $departmentsToShow = $deptOld;
                                } elseif ($teacher->department && is_array($teacher->department)) {
                                    $departmentsToShow = $teacher->department;
                                } else {
                                    $departmentsToShow = [''];
                                }
                            @endphp
                            @foreach($departmentsToShow as $index => $dept)
                            <div class="field-group">
                                <div class="input-group">
                                    <input type="text" name="departments[]" class="form-control department-input @error('departments.*') is-invalid @enderror" placeholder="Contoh: Matematika" value="{{ old('departments.' . $index, $dept) }}">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-department" style="display: none;">Hapus</button>
                                </div>
                            </div>
                            @endforeach
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
                        :value="old('address', $teacher->address)"
                        :error="$errors->first('address')"
                    />
                </x-forms.row-full>

                <x-forms.row-full>
                    <x-forms.field-file 
                        label="Foto Profil" 
                        name="photo_profile" 
                        accept="image/*"
                        help="Format: JPG, PNG (Maksimal 2MB)"
                        :preview="$teacher->photo_profile ? asset('storage/' . $teacher->photo_profile) : null"
                        previewLabel="Foto saat ini"
                        :error="$errors->first('photo_profile')"
                    />
                </x-forms.row-full>
            </x-forms.section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
