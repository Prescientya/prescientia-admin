{{-- ═══════════════════════════════════════════════════════════
     MODAL: BULK DEACTIVATE (Non-aktifkan Siswa)
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalBulkDeactivate">
    <div class="modal modal--md">
        <div class="modal-header">
            <h2 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                </svg>
                Nonaktifkan Siswa
            </h2>
            <button type="button" class="modal-close-btn modal-close" aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" action="{{ route('siswa.bulk-deactivate') }}" id="formBulkDeactivate">
            @csrf
            <div class="modal-body">
                <p class="modal-desc" style="margin-bottom: 1rem; color: var(--text-secondary);">
                    Nonaktifkan akun siswa agar tidak terecap absen alpha otomatis (misal: untuk siswa yang sedang PKL).
                </p>

                {{-- Mode Selection --}}
                <div class="bulk-mode-tabs">
                    <label class="bulk-mode-tab">
                        <input type="radio" name="mode" value="class" checked>
                        <span>Per Kelas</span>
                    </label>
                    <label class="bulk-mode-tab">
                        <input type="radio" name="mode" value="grade">
                        <span>Per Tingkat</span>
                    </label>
                    <label class="bulk-mode-tab">
                        <input type="radio" name="mode" value="major">
                        <span>Per Jurusan</span>
                    </label>
                    <label class="bulk-mode-tab">
                        <input type="radio" name="mode" value="grade_major">
                        <span>Tingkat + Jurusan</span>
                    </label>
                </div>

                {{-- Per Kelas --}}
                <div class="bulk-mode-content" data-mode="class">
                    <label class="form-label">Pilih Kelas</label>
                    <select name="class_id" class="form-input" id="bulkClassSelect">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $kelas)
                            <option value="{{ $kelas->id }}">
                                Kelas {{ $kelas->class }}{{ $kelas->major ? ' – ' . $kelas->major : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Per Tingkat --}}
                <div class="bulk-mode-content" data-mode="grade" style="display:none;">
                    <label class="form-label">Pilih Tingkat</label>
                    <select name="grade" class="form-input" id="bulkGradeSelect">
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="10">Kelas 10 (Semua Jurusan)</option>
                        <option value="11">Kelas 11 (Semua Jurusan)</option>
                        <option value="12">Kelas 12 (Semua Jurusan)</option>
                    </select>
                </div>

                {{-- Per Jurusan --}}
                <div class="bulk-mode-content" data-mode="major" style="display:none;">
                    <label class="form-label">Pilih Jurusan</label>
                    <select name="major" class="form-input" id="bulkMajorSelect">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($majors as $major)
                            <option value="{{ $major }}">{{ $major }} (Semua Tingkat)</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tingkat + Jurusan --}}
                <div class="bulk-mode-content" data-mode="grade_major" style="display:none;">
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Tingkat</label>
                            <select name="grade_combo" class="form-input" id="bulkGradeCombo">
                                <option value="">-- Tingkat --</option>
                                <option value="10">Kelas 10</option>
                                <option value="11">Kelas 11</option>
                                <option value="12">Kelas 12</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Jurusan</label>
                            <select name="major_combo" class="form-input" id="bulkMajorCombo">
                                <option value="">-- Jurusan --</option>
                                @foreach($majors as $major)
                                    <option value="{{ $major }}">{{ $major }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Preview Box --}}
                <div class="bulk-preview" id="bulkPreview" style="display:none;">
                    <div class="bulk-preview__header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span id="bulkPreviewCount">0</span> siswa akan dinonaktifkan
                    </div>
                    <div class="bulk-preview__list" id="bulkPreviewList"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost modal-close">Batal</button>
                <button type="submit" class="btn btn--warning" id="btnSubmitDeactivate" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                    </svg>
                    Nonaktifkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MODAL: BULK ACTIVATE (Aktifkan Semua)
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalBulkActivate">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h2 class="modal-title modal-title--success">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Aktifkan Semua Siswa
            </h2>
            <button type="button" class="modal-close-btn modal-close" aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-body">
            <p>Apakah Anda yakin ingin <strong>mengaktifkan semua {{ $inactiveCount }} akun siswa</strong> yang saat ini nonaktif?</p>
            <p style="margin-top:.5rem;font-size:.875rem;color:var(--text-secondary);">
                Setelah diaktifkan, siswa akan kembali terecap absen otomatis.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost modal-close">Batal</button>
            <form method="POST" action="{{ route('siswa.bulk-activate') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn--success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Ya, Aktifkan Semua
                </button>
            </form>
        </div>
    </div>
</div>
