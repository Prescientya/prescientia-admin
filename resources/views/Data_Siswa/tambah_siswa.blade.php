{{-- =====================================================
     MODAL: Tambah Siswa (Manual)
     @include('Data_Siswa.tambah_siswa')
     ===================================================== --}}
<div class="modal-overlay" id="modalTambahManual">
    <div class="modal modal--lg">

        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:-3px;margin-right:6px;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Tambah Siswa Manual
            </h3>
            <button class="modal-close" data-close-modal="modalTambahManual" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('siswa.store') }}" method="POST" enctype="multipart/form-data" data-loading>
            @csrf
            <div class="modal-body">

                {{-- Photo --}}
                <div class="form-group form-col-full" style="margin-bottom:16px;">
                    <label class="form-label">Foto Profil</label>
                    <div class="photo-upload">
                        <div class="photo-preview" id="addPhotoPreview">A</div>
                        <div>
                            <label class="photo-upload-label" for="addPhotoFile">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                Pilih Foto
                            </label>
                            <input type="file" id="addPhotoFile" name="photo_profile"
                                   accept="image/*" data-preview="addPhotoPreview">
                            <p class="photo-upload-hint">JPG/PNG, maks 2MB</p>
                        </div>
                    </div>
                </div>

                {{-- Data Pribadi --}}
                <div class="form-section-title">Data Pribadi</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Nama lengkap siswa" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIS <span class="req">*</span></label>
                        <input type="text" name="nis" class="form-control"
                               placeholder="Nomor Induk Siswa" value="{{ old('nis') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                        <select name="gender" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir <span class="req">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control"
                               value="{{ old('date_of_birth') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="phone_number" class="form-control"
                               placeholder="08xxxxxxxxxx" value="{{ old('phone_number') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <select name="class_id" class="form-control">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $kelas)
                                <option value="{{ $kelas->id }}" {{ old('class_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->class }}{{ $kelas->major ? ' - ' . $kelas->major : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-col-full">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control"
                                  placeholder="Alamat lengkap siswa" rows="2">{{ old('address') }}</textarea>
                    </div>
                </div>

                {{-- Akun --}}
                <div class="form-section">
                    <div class="form-section-title">Akun Login</div>
                    <div class="form-grid-2">
                        <div class="form-group form-col-full">
                            <label class="form-label">Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="email@siswa.com" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password <span class="req">*</span></label>
                            <div class="input-password">
                                <input type="password" name="password" class="form-control"
                                       placeholder="Minimal 8 karakter" required>
                                <button type="button" class="input-password__toggle" tabindex="-1">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password <span class="req">*</span></label>
                            <div class="input-password">
                                <input type="password" name="password_confirmation" class="form-control"
                                       placeholder="Ulangi password" required>
                                <button type="button" class="input-password__toggle" tabindex="-1">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalTambahManual">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Simpan Siswa</span>
                </button>
            </div>
        </form>

    </div>
</div>

<style>
.hidden { display: none !important; }
</style>
