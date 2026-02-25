@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Data Master</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <a href="{{ route('siswa.index') }}" style="color:var(--text-muted);text-decoration:none;">Data Siswa</a>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Edit {{ $siswa->name }}</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Data_Siswa/style.css')) !!}</style>
@endpush

@section('content')
<div class="ds-page edit-page-wrap">

    {{-- Flash errors --}}
    @if($errors->any())
    <div class="alert alert--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div>
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <form action="{{ route('siswa.update', $siswa->id) }}" method="POST"
          enctype="multipart/form-data" data-loading>
        @csrf
        @method('PUT')

        <div class="edit-card">

            {{-- Card Header --}}
            <div class="edit-card-header">
                <div class="ds-avatar" style="width:44px;height:44px;font-size:1rem;">
                    @if($siswa->photo_profile)
                        <img src="{{ Storage::url($siswa->photo_profile) }}" alt="{{ $siswa->name }}">
                    @else
                        {{ strtoupper(substr($siswa->name,0,1)) }}
                    @endif
                </div>
                <div>
                    <h2>Edit Data Siswa</h2>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-top:2px;">
                        {{ $siswa->name }} &mdash; NIS {{ $siswa->nis }}
                    </p>
                </div>
            </div>

            <div class="edit-card-body">

                {{-- ── Foto ───────────────────── --}}
                <div>
                    <div class="form-section-title">Foto Profil</div>
                    <div class="photo-upload">
                        <div class="photo-preview" id="editPhotoPreview">
                            @if($siswa->photo_profile)
                                <img src="{{ Storage::url($siswa->photo_profile) }}" alt="">
                            @else
                                {{ strtoupper(substr($siswa->name,0,1)) }}
                            @endif
                        </div>
                        <div>
                            <label class="photo-upload-label" for="editPhotoFile">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                Ganti Foto
                            </label>
                            <input type="file" id="editPhotoFile" name="photo_profile"
                                   accept="image/*" data-preview="editPhotoPreview">
                            <p class="photo-upload-hint">JPG/PNG, maks 2MB. Kosongkan jika tidak ingin mengubah foto.</p>
                        </div>
                    </div>
                </div>

                {{-- ── Data Pribadi ────────────── --}}
                <div>
                    <div class="form-section-title">Data Pribadi</div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $siswa->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIS <span class="req">*</span></label>
                            <input type="text" name="nis" class="form-control"
                                   value="{{ old('nis', $siswa->nis) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                            <select name="gender" class="form-control" required>
                                <option value="L" {{ old('gender', $siswa->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $siswa->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Lahir <span class="req">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control"
                                   value="{{ old('date_of_birth', $siswa->date_of_birth?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="phone_number" class="form-control"
                                   placeholder="08xxxxxxxxxx"
                                   value="{{ old('phone_number', $siswa->phone_number) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kelas</label>
                            <select name="class_id" id="classSelect" class="form-control">
                                <option value="">– Tidak ada –</option>
                                @foreach($classes as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ old('class_id', $siswa->class_id) == $kelas->id ? 'selected' : '' }}>
                                        Kelas {{ $kelas->class }}{{ $kelas->major ? ' – ' . $kelas->major : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group form-col-full" id="roleSection"
                             style="{{ !$siswa->class_id ? 'display:none' : '' }}">
                            <label class="form-label">Role di Kelas</label>
                            <div class="role-select-wrap">
                                <select name="role" id="roleSelect" class="form-control">
                                    <option value="pelajar" {{ old('role', $currentRole) === 'pelajar' ? 'selected' : '' }}>Pelajar</option>
                                    <option value="km"        id="opt-km"        {{ old('role', $currentRole) === 'km'        ? 'selected' : '' }}>KM (Ketua Murid)</option>
                                    <option value="wakil_km"  id="opt-wakil_km"  {{ old('role', $currentRole) === 'wakil_km'  ? 'selected' : '' }}>Wakil KM</option>
                                    <option value="sekretaris" id="opt-sekretaris" {{ old('role', $currentRole) === 'sekretaris' ? 'selected' : '' }}>Sekretaris</option>
                                </select>
                            </div>
                            <div id="roleHints" class="role-hints"></div>
                        </div>
                        <div class="form-group form-col-full">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2"
                                      placeholder="Alamat lengkap">{{ old('address', $siswa->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Akun Login ──────────────── --}}
                <div>
                    <div class="form-section-title">Akun Login</div>
                    <div class="form-grid-2">
                        <div class="form-group form-col-full">
                            <label class="form-label">Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', optional($siswa->user)->email) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <div class="input-password">
                                <input type="password" name="password" class="form-control"
                                       placeholder="Kosongkan jika tidak diubah">
                                <button type="button" class="input-password__toggle" tabindex="-1">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <span class="form-hint">Kosongkan jika tidak ingin mengubah. Password default awal siswa adalah NIS.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-password">
                                <input type="password" name="password_confirmation" class="form-control"
                                       placeholder="Ulangi password baru">
                                <button type="button" class="input-password__toggle" tabindex="-1">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Status Akun ─────────────── --}}
                <div>
                    <div class="form-section-title">Status Akun</div>
                    <div class="toggle-row">
                        <div>
                            <p class="toggle-label">Akun Aktif</p>
                            <p class="toggle-sub">Nonaktifkan untuk memblokir login siswa ini</p>
                        </div>
                        <label class="toggle">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', optional($siswa->user)->is_active) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

            </div>{{-- /.edit-card-body --}}

            <div class="edit-card-footer">
                <a href="{{ route('siswa.index') }}" class="btn btn--ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Kembali
                </a>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan Perubahan
                    </span>
                </button>
            </div>

        </div>{{-- /.edit-card --}}
    </form>
</div>
@endsection

@push('scripts')
<script>
window.SISWA_EDIT = {
    studentId:     {{ $siswa->id }},
    classRoleData: @json($classRoleData),
    currentRole:   '{{ old('role', $currentRole) }}'
};
</script>
<script>{!! file_get_contents(resource_path('views/Data_Siswa/main.js')) !!}</script>
@endpush
