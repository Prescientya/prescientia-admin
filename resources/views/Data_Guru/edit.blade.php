@extends('layouts.app')

@section('title', 'Edit Data Guru')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Data Master</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <a href="{{ route('guru.index') }}" style="color:var(--text-muted);text-decoration:none;">Data Guru</a>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Edit {{ $guru->name }}</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Data_Guru/style.css')) !!}</style>
@endpush

@section('content')
<div class="dg-page dg-edit-wrap">

    {{-- Flash errors --}}
    @if($errors->any())
    <div class="alert alert--error" style="margin-bottom:20px;">
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

    <form action="{{ route('guru.update', $guru->id) }}" method="POST"
          enctype="multipart/form-data" data-loading>
        @csrf
        @method('PUT')

        <div class="dg-edit-card">

            {{-- Card Header --}}
            <div class="dg-edit-card-header">
                <div class="dg-avatar" style="width:44px;height:44px;font-size:1rem;">
                    @if($guru->photo_profile)
                        <img src="{{ Storage::url($guru->photo_profile) }}" alt="{{ $guru->name }}">
                    @else
                        {{ strtoupper(substr($guru->name,0,1)) }}
                    @endif
                </div>
                <div>
                    <h2>Edit Data Guru</h2>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-top:2px;">
                        {{ $guru->name }} &mdash; NIP {{ $guru->nip }}
                    </p>
                </div>
            </div>

            <div class="dg-edit-card-body">

                {{-- ── Foto ────────────────────────────── --}}
                <div>
                    <div class="form-section-title">Foto Profil</div>
                    <div class="photo-upload">
                        <div class="photo-preview" id="editPhotoPreview">
                            @if($guru->photo_profile)
                                <img src="{{ Storage::url($guru->photo_profile) }}" alt="">
                            @else
                                {{ strtoupper(substr($guru->name,0,1)) }}
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

                {{-- ── Data Pribadi ─────────────────────── --}}
                <div>
                    <div class="form-section-title">Data Pribadi</div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $guru->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIP <span class="req">*</span></label>
                            <input type="text" name="nip" class="form-control"
                                   value="{{ old('nip', $guru->nip) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                            <select name="gender" class="form-control" required>
                                <option value="L" {{ old('gender', $guru->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $guru->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Lahir <span class="req">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control"
                                   value="{{ old('date_of_birth', $guru->date_of_birth?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="phone_number" class="form-control"
                                   placeholder="08xxxxxxxxxx"
                                   value="{{ old('phone_number', $guru->phone_number) }}">
                        </div>
                        <div class="form-group form-col-full">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2"
                                      placeholder="Alamat lengkap">{{ old('address', $guru->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Mata Pelajaran ────────────────────── --}}
                <div>
                    <div class="form-section-title">Mata Pelajaran</div>
                    <div class="form-group">
                        <label class="form-label">Mapel yang Diajar</label>
                        @php
                            $existingMapel = old('mapel_text',
                                $guru->subjects->pluck('name')->implode(',')
                            );
                        @endphp
                        <div class="mapel-input-wrap" id="editMapelWrap"
                             data-existing="{{ $existingMapel }}">
                            <input type="text" class="mapel-text-input" id="editMapelInput"
                                   placeholder="Ketik nama mapel, tekan Enter atau koma...">
                        </div>
                        <input type="hidden" name="mapel_text" id="editMapelHidden"
                               value="{{ $existingMapel }}">
                        <p class="mapel-hint">Tekan Enter atau koma untuk menambah mapel. Klik × untuk menghapus.</p>
                    </div>
                </div>

                {{-- ── Akun Login ────────────────────────── --}}
                <div>
                    <div class="form-section-title">Akun Login</div>
                    <div class="form-grid-2">
                        <div class="form-group form-col-full">
                            <label class="form-label">Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', optional($guru->user)->email) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <div class="input-password">
                                <input type="password" name="password" class="form-control"
                                       placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                                <button type="button" class="input-password__toggle" tabindex="-1">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                            </div>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">Min 8 karakter. Kosongkan jika tidak ingin mengubah password.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status Akun</label>
                            <div class="toggle-row">
                                <span class="toggle-label">
                                    {{ optional($guru->user)->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                                </span>
                                <label class="toggle">
                                    <input type="checkbox" name="is_active" value="1"
                                           {{ optional($guru->user)->is_active ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Card Footer --}}
            <div class="dg-edit-card-footer">
                <a href="{{ route('guru.index') }}" class="btn btn--ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Kembali
                </a>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Data_Guru/main.js')) !!}</script>
@endpush
