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
                        <label class="form-label">
                            Mata Pelajaran yang Diajar
                            <small class="text-muted">(Pilih satu atau lebih)</small>
                        </label>
                        
                        @if($subjects->count() > 0)
                            <!-- Live Search -->
                            <div class="mb-3">
                                <input 
                                    type="text" 
                                    id="searchSubjects" 
                                    class="form-control" 
                                    placeholder="🔍 Cari mata pelajaran..."
                                >
                            </div>

                            <div class="subjects-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; background: #f8f9fa;">
                                @foreach($subjects as $major => $subjectList)
                                    <div class="subject-major-group mb-4">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">📚 {{ $major }}</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($subjectList as $subject)
                                                <div class="subject-item" data-subject-name="{{ strtolower($subject->name) }}" data-subject-code="{{ strtolower($subject->code) }}">
                                                    <label class="d-flex align-items-center" for="subject_{{ $subject->id }}" style="background: white; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 14px; min-width: 220px; cursor: pointer; margin: 0; transition: all 0.2s;" onmouseover="this.style.borderColor='#0d6efd'; this.style.boxShadow='0 2px 4px rgba(13,110,253,0.2)';" onmouseout="this.style.borderColor='#dee2e6'; this.style.boxShadow='none';">
                                                        <input 
                                                            class="form-check-input" 
                                                            type="checkbox" 
                                                            name="subject_ids[]" 
                                                            value="{{ $subject->id }}" 
                                                            id="subject_{{ $subject->id }}"
                                                            {{ in_array($subject->id, old('subject_ids', [])) ? 'checked' : '' }}
                                                            style="margin-top: 0; margin-right: 10px; flex-shrink: 0;"
                                                        >
                                                        <span style="margin-right: 8px; font-size: 10px; flex-shrink: 0;">{{ $subject->code }}</span>
                                                        <span style="flex: 1; font-size: 14px;">{{ $subject->name }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const searchInput = document.getElementById('searchSubjects');
                                const subjectItems = document.querySelectorAll('.subject-item');
                                const majorGroups = document.querySelectorAll('.subject-major-group');

                                searchInput.addEventListener('input', function() {
                                    const searchTerm = this.value.toLowerCase().trim();

                                    majorGroups.forEach(group => {
                                        let hasVisibleItems = false;
                                        const items = group.querySelectorAll('.subject-item');
                                        
                                        items.forEach(item => {
                                            const name = item.dataset.subjectName;
                                            const code = item.dataset.subjectCode;
                                            const matches = name.includes(searchTerm) || code.includes(searchTerm);
                                            
                                            if (matches || searchTerm === '') {
                                                item.style.display = 'block';
                                                hasVisibleItems = true;
                                            } else {
                                                item.style.display = 'none';
                                            }
                                        });

                                        // Hide/show entire major group if no items match
                                        group.style.display = hasVisibleItems ? 'block' : 'none';
                                    });
                                });
                            });
                            </script>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Belum ada mata pelajaran aktif. Silakan tambah mata pelajaran terlebih dahulu.
                            </div>
                        @endif
                        
                        @error('subject_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </x-forms.row-full>

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
