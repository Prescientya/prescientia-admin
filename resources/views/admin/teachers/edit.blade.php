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
                        <label class="form-label">Daftar Pelajaran</label>

                        @php
                            $selectedSubjectIds = old('subject_ids', $teacher->subjects->pluck('id')->toArray());
                        @endphp

                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2 existing-subject-badges" style="background:#f1f3f5; padding:12px; border-radius:8px;">
                                @forelse($teacher->subjects as $sub)
                                    <span class="badge bg-info text-white d-inline-flex align-items-center subject-badge" data-subject-id="{{ $sub->id }}" style="display:inline-flex; align-items:center; padding:8px 14px; border-radius:999px;">
                                        <span class="subject-name">{{ $sub->name }}</span>
                                        <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-subject" aria-label="Remove" style="opacity:0.9; margin-left:8px;"></button>
                                    </span>
                                    <input type="hidden" name="subject_ids[]" value="{{ $sub->id }}">
                                @empty
                                    <span class="text-muted">Belum ada mata pelajaran yang ditugaskan untuk guru ini.</span>
                                @endforelse
                            </div>
                        </div>

                        <button type="button" id="showAddSubject" class="btn btn-sm btn-primary mb-3">Tambahkan Mapel</button>

                        <div id="addSubjectRow" style="display: none; margin-bottom:12px;">
                            @if(count($subjects) > 0)
                                <div class="mb-3">
                                    <div class="d-flex gap-2 align-items-center" style="max-width:760px;">
                                        <select id="selectSubjectToAdd" class="form-select">
                                            <option value="">Pilih mata pelajaran...</option>
                                            @foreach($subjects as $major => $subjectList)
                                                <optgroup label="{{ $major }}">
                                                    @foreach($subjectList as $subject)
                                                        <option value="{{ $subject->id }}" data-name="{{ $subject->name }}">{{ $subject->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        <button type="button" id="btnAddSubject" class="btn btn-success" style="width:48px; height:48px; font-size:22px; display:flex; align-items:center; justify-content:center; padding:0;">+</button>
                                        <button type="button" id="btnCloseAddRow" class="btn btn-outline-secondary" style="height:48px;">Tutup</button>
                                    </div>
                                </div>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const showBtn = document.getElementById('showAddSubject');
                                    const addRow = document.getElementById('addSubjectRow');
                                    const select = document.getElementById('selectSubjectToAdd');
                                    const btnAdd = document.getElementById('btnAddSubject');
                                    const badgesContainer = document.querySelector('.existing-subject-badges');
                                    const form = document.querySelector('form');

                                    showBtn.addEventListener('click', function() {
                                        if (addRow.style.display === 'none') {
                                            addRow.style.display = 'block';
                                            showBtn.style.display = 'none';
                                        }
                                    });

                                    const btnCloseAdd = document.getElementById('btnCloseAddRow');
                                    if (btnCloseAdd) {
                                        btnCloseAdd.addEventListener('click', function() {
                                            addRow.style.display = 'none';
                                            showBtn.style.display = 'inline-block';
                                        });
                                    }

                                    function createHiddenInput(id) {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'subject_ids[]';
                                        input.value = id;
                                        return input;
                                    }

                                    btnAdd.addEventListener('click', function() {
                                        const id = select.value;
                                        if (!id) return;

                                        // prevent duplicates
                                        if (document.querySelector('input[name="subject_ids[]"][value="' + id + '"]')) {
                                            alert('Mapel sudah ditambahkan.');
                                            return;
                                        }

                                        const option = select.querySelector('option[value="' + id + '"]');
                                        const name = option ? option.dataset.name : select.options[select.selectedIndex].text;

                                        // add badge

                                        const span = document.createElement('span');
                                        span.className = 'badge bg-info text-white d-inline-flex align-items-center me-2 mb-2 subject-badge';
                                        span.dataset.subjectId = id;
                                        span.style.display = 'inline-flex';
                                        span.style.alignItems = 'center';
                                        span.style.padding = '8px 14px';
                                        span.style.borderRadius = '999px';

                                        const nameSpan = document.createElement('span');
                                        nameSpan.className = 'subject-name';
                                        nameSpan.innerText = name;

                                        const removeBtn = document.createElement('button');
                                        removeBtn.type = 'button';
                                        removeBtn.className = 'btn-close btn-close-white btn-sm ms-2 remove-subject';
                                        removeBtn.setAttribute('aria-label', 'Remove');
                                        removeBtn.style.opacity = '0.9';
                                        removeBtn.style.marginLeft = '8px';
                                        removeBtn.addEventListener('click', function() {
                                            const hidden = form.querySelector('input[name="subject_ids[]"][value="' + id + '"]');
                                            if (hidden) hidden.remove();
                                            span.remove();
                                        });

                                        span.appendChild(nameSpan);
                                        span.appendChild(removeBtn);
                                        badgesContainer.appendChild(span);

                                        // add hidden input to form
                                        form.appendChild(createHiddenInput(id));

                                        // reset select
                                        select.value = '';
                                    });

                                    // handle removal for existing badges
                                    document.querySelectorAll('.remove-subject').forEach(btn => {
                                        btn.addEventListener('click', function(e) {
                                            const span = e.currentTarget.closest('span[data-subject-id]');
                                            if (!span) return;
                                            const id = span.dataset.subjectId;
                                            const hidden = form.querySelector('input[name="subject_ids[]"][value="' + id + '"]');
                                            if (hidden) hidden.remove();
                                            span.remove();
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
                        </div>

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
