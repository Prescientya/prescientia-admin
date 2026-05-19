{{-- =====================================================
     MODAL: Tambah Guru (Manual)
     @include('Data_Guru.tambah_guru')
     ===================================================== --}}
<div class="modal-overlay" id="modalTambahManual">
    <div class="modal modal--lg">

        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:-3px;margin-right:6px;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Tambah Guru Manual
            </h3>
            <button class="modal-close" data-close-modal="modalTambahManual" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('guru.store') }}" method="POST" enctype="multipart/form-data" data-loading>
            @csrf
            <div class="modal-body">

                @if($errors->any() && !$errors->has('file'))
                <div class="alert alert--error" style="margin-bottom:14px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        <strong>Gagal menyimpan data guru:</strong>
                        <ul style="margin:4px 0 0 18px;padding:0;font-size:0.85rem;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif


                {{-- Photo --}}
                <div class="form-group form-col-full" style="margin-bottom:16px;">
                    <label class="form-label">Foto Profil</label>
                    <div class="photo-upload">
                        <div class="photo-preview" id="addPhotoPreview">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div>
                            <label class="photo-upload-label" for="addPhotoFile">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
                               placeholder="Nama lengkap guru" value="{{ old('name') }}" maxlength="50" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIP <span class="req">*</span></label>
                        <input type="text" name="nip" class="form-control"
                               placeholder="Nomor Induk Pegawai" value="{{ old('nip') }}" maxlength="25" required>
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
                               placeholder="08xxxxxxxxxx" value="{{ old('phone_number') }}" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mata Pelajaran</label>
                        <div class="mapel-input-wrap" id="addMapelWrap">
                            <input type="text" class="mapel-text-input" id="addMapelInput"
                                   placeholder="Ketik nama mapel, tekan Enter atau koma...">
                        </div>
                        <input type="hidden" name="mapel_text" id="addMapelHidden" value="{{ old('mapel_text') }}">
                        <p class="mapel-hint">Contoh: Matematika &rarr; tekan Enter, lalu Fisika &rarr; Enter</p>
                        @error('mapel_text')
                        <p class="mapel-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group form-col-full">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control"
                                  placeholder="Alamat lengkap guru" rows="2" maxlength="500">{{ old('address') }}</textarea>
                    </div>
                </div>

                {{-- Akun Login --}}
                <div class="form-section-title" style="margin-top:8px;">Akun Login</div>
                <div class="form-grid-2">
                    <div class="form-group form-col-full">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" class="form-control"
                               placeholder="email@domain.com" value="{{ old('email') }}" maxlength="50" required>
                    </div>
                </div>
                <p style="font-size:0.78rem;color:var(--text-muted);margin-top:-8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         style="display:inline;vertical-align:-2px;margin-right:3px;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Password default = NIP. Guru dapat mengubahnya setelah login pertama.
                </p>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalTambahManual">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Guru
                </button>
            </div>
        </form>

    </div>
</div>
