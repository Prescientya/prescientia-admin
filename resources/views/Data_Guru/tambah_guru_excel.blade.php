{{-- =====================================================
     MODAL: Import Guru via Excel
     @include('Data_Guru.tambah_guru_excel')
     ===================================================== --}}
<div class="modal-overlay" id="modalTambahExcel">
    <div class="modal modal--lg">

        <div class="modal-header">
            <h3 class="modal-title">
                <svg class="modal-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Import Guru via Excel
            </h3>
            <button class="modal-close" data-close-modal="modalTambahExcel" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data" data-loading id="dg-importExcelForm">
            @csrf
            <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">

                {{-- ── Dropzone ─────────────────────────────── --}}
                <div class="excel-dropzone" id="dg-excelDropzone">
                    <div class="excel-dropzone__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </div>
                    <p class="excel-dropzone__title">Klik atau seret file Excel ke sini</p>
                    <p class="excel-dropzone__sub">Format: .xlsx, .xls, .csv &mdash; Maks 5MB</p>
                    <button type="button" class="excel-dropzone__btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Pilih File
                    </button>
                    <input type="file" id="dg-excelFileInput" name="file"
                           accept=".xlsx,.xls,.csv" style="display:none;">
                </div>

                {{-- ── Chosen file display ─────────────────── --}}
                <div class="excel-file-chosen" id="dg-excelFileChosen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         style="color:var(--accent);flex-shrink:0;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span class="excel-file-name" id="dg-excelFileName">–</span>
                    <button type="button" class="excel-file-clear" id="dg-excelFileClear" aria-label="Hapus file">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                {{-- ── Reading progress ─────────────────────────── --}}
                <div class="import-reading-section" id="dg-importReadingSection" style="display:none;">
                    <div class="import-reading-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                             style="color:var(--accent);">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span id="dg-importProgressLabel" style="font-size:0.85rem;font-weight:600;color:var(--text-primary);">Membaca file… 0%</span>
                    </div>
                    <div class="import-progress-bar">
                        <div class="import-progress-fill" id="dg-importProgressFill" style="width:0%;"></div>
                    </div>
                </div>

                {{-- ── Missing subjects section ──────────────────── --}}
                <div class="import-missing-section" id="dg-importMissingSection" style="display:none;">
                    <div class="import-missing-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                             style="color:#f59e0b;flex-shrink:0;">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <span class="import-missing-title">Mapel belum ada di database</span>
                        <span class="import-missing-subtitle">Centang mapel yang ingin dibuat otomatis</span>
                    </div>
                    <div class="import-missing-list" id="dg-importMissingList"></div>
                </div>

                {{-- ── Template download ─────────────────────── --}}
                <div class="excel-template-row">
                    <a href="{{ route('guru.template') }}" class="btn btn--ghost btn--sm" target="_blank" style="flex-shrink:0;white-space:nowrap;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Template
                    </a>
                </div>

                {{-- ── Master auto-create checkbox ─────────────── --}}
                <label class="import-autocreate-label" id="dg-autoCreateLabel">
                    <input type="checkbox" id="dg-autoCreateMaster"
                           style="margin-top:2px;flex-shrink:0;accent-color:var(--accent);">
                    <span>
                        <strong style="color:var(--text-primary);">Buat mapel otomatis jika belum ada</strong><br>
                        <span id="dg-autoCreateDesc" style="color:var(--text-muted);font-size:0.82rem;">
                            Jika mapel di file tidak ditemukan di database, mapel baru akan dibuat secara otomatis.
                            Jika tidak dicentang, baris dengan mapel tidak dikenal akan di-skip.
                        </span>
                    </span>
                </label>

            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalTambahExcel">Batal</button>
                <button type="submit" class="btn btn--primary" id="dg-importSubmitBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Mulai Import
                    </span>
                </button>
            </div>
        </form>

    </div>
</div>

{{-- SheetJS for client-side Excel parsing --}}
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
