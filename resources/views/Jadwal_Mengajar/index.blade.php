@extends('layouts.app')

@section('title', 'Jadwal Mengajar')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Akademik</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Jadwal Mengajar</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Jadwal_Mengajar/style.css')) !!}</style>
@endpush

@section('content')
<div class="jm-page">

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    @if(session('import_failed'))
    <div class="import-report">
        <div class="import-report__header">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div class="import-report__meta">
                <div class="import-report__title">Import selesai dengan peringatan</div>
                <div class="import-report__counts">
                    @if(session('import_success_count', 0) > 0)
                    <span class="irc irc--ok">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ session('import_success_count') }} jadwal berhasil diimpor
                    </span>
                    @endif
                    <span class="irc irc--fail">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        {{ count(session('import_failed')) }} jadwal gagal diimpor
                    </span>
                </div>
            </div>
        </div>
        <details class="import-report__body">
            <summary>Lihat detail kegagalan ({{ count(session('import_failed')) }} data)</summary>
            <div class="import-report__table-wrap">
                <table class="import-report__table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data</th>
                            <th>Alasan Gagal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('import_failed') as $i => $failRow)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><code>{{ $failRow['row'] ?? '-' }}</code></td>
                            <td>{{ $failRow['reason'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>
    @endif

    {{-- Header --}}
    <div class="jm-header">
        <p class="jm-subtitle">
            Jadwal mengajar guru &mdash; pilih tampilan per <strong>Guru</strong> atau per <strong>Kelas</strong>
        </p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="btn btn--secondary" data-open-modal="modalImportJadwal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                Import Excel
            </button>
            <button class="btn btn--primary" id="btnTambahJadwal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Jadwal
            </button>
        </div>
    </div>

    {{-- Mode bar (Per Guru / Per Kelas) --}}
    <div class="jm-mode-bar">
        <div class="jm-mode-tabs">
            <button class="jm-mode-btn active" data-mode="teacher">Per Guru</button>
            <button class="jm-mode-btn" data-mode="class">Per Kelas</button>
        </div>

        <div class="jm-select-wrap">
            <label id="jmPickerLabel">Pilih Guru:</label>
            <select class="form-control jm-picker" id="jmPicker">
                <option value="">— Pilih Guru —</option>
                @foreach($teachers as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Summary (shown after a selection) --}}
    <div class="jm-summary" id="jmSummary" style="display:none;"></div>

    {{-- Grid container --}}
    <div id="jmGridWrap">
        <div class="jm-empty">
            <div class="jm-empty-icon">📅</div>
            <div class="jm-empty-title">Belum ada pilihan</div>
            <div class="jm-empty-sub">Pilih guru atau kelas di atas untuk melihat jadwal mengajar</div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════
     MODAL: Tambah / Edit Jadwal
═══════════════════════════════════ --}}
<div class="modal-overlay" id="modalJadwal" aria-hidden="true">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalJadwalTitle">Tambah Jadwal Mengajar</h3>
            <button class="modal-close" data-close-modal="modalJadwal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formJadwal" novalidate>
                @csrf
                <input type="hidden" id="jfId">
                <input type="hidden" id="jfMethod" value="POST">

                <div class="form-grid-2">

                    <div class="form-group form-col-full">
                        <label class="form-label">Guru <span class="req">*</span></label>
                        <select id="jfTeacher" name="teacher_id" class="form-control" required>
                            <option value="">— Pilih Guru —</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group form-col-full">
                        <label class="form-label">Mata Pelajaran <span class="req">*</span></label>
                        <select id="jfSubject" name="subject_id" class="form-control" required>
                            <option value="">— Pilih Mapel —</option>
                            @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group form-col-full">
                        <label class="form-label">Kelas <span class="req">*</span></label>
                        <select id="jfClass" name="class_id" class="form-control" required>
                            <option value="">— Pilih Kelas —</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->class }} {{ $c->major }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Day + Period pickers — populated via AJAX --}}
                    <div class="form-group">
                        <label class="form-label">Hari <span class="req">*</span></label>
                        <select id="jfDay" class="form-control" required>
                            <option value="">— Pilih Hari —</option>
                        </select>
        <span class="form-hint" id="jfDayHint" style="display:none;">Pilih guru &amp; kelas dulu</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jam Ke <span class="req">*</span></label>
                        <select id="jfPeriod" name="class_period_id" class="form-control" required>
                            <option value="">— Pilih Jam —</option>
                        </select>
                    </div>

                </div>

                <div id="formJadwalError" class="alert alert--error" style="display:none;margin-top:12px;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn--ghost" data-close-modal="modalJadwal">Batal</button>
            <button class="btn btn--primary" id="btnSimpanJadwal">
                <span class="spinner"></span>
                <span class="btn-text">Simpan</span>
            </button>
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div class="modal-overlay" id="modalHapusJadwal" aria-hidden="true">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <h3 class="modal-title">Hapus Jadwal</h3>
            <button class="modal-close" data-close-modal="modalHapusJadwal">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center;padding:24px;">
            <div style="font-size:2.5rem;margin-bottom:12px;">🗑</div>
            <p style="font-weight:600;">Hapus jadwal mengajar ini?</p>
            <p style="color:var(--text-secondary);font-size:0.88rem;" id="hapusJadwalDesc"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn--ghost" data-close-modal="modalHapusJadwal">Batal</button>
            <button class="btn btn--danger" id="btnKonfirmasiHapusJadwal">
                <span class="spinner"></span>
                <span class="btn-text">Ya, Hapus</span>
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════
     MODAL: Import Jadwal via Excel
═══════════════════════════════════ --}}
<div class="modal-overlay" id="modalImportJadwal" aria-hidden="true">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg class="modal-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Import Jadwal via Excel
            </h3>
            <button class="modal-close" data-close-modal="modalImportJadwal">&times;</button>
        </div>
        <form action="{{ route('jadwal-mengajar.import') }}" method="POST"
              enctype="multipart/form-data" data-loading>
            @csrf
            <div class="modal-body">

                {{-- Dropzone --}}
                <div class="excel-dropzone" id="jm-excelDropzone">
                    <div class="excel-dropzone__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </div>
                    <p class="excel-dropzone__title">Klik atau seret file Excel ke sini</p>
                    <p class="excel-dropzone__sub">Format: .xlsx, .xls, .csv &mdash; Maks 5MB</p>
                    <button type="button" class="excel-dropzone__btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Pilih File
                    </button>
                    <input type="file" id="jm-excelFileInput" name="file"
                           accept=".xlsx,.xls,.csv" style="display:none;">
                </div>

                {{-- Chosen file display --}}
                <div class="excel-file-chosen" id="jm-excelFileChosen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         style="color:var(--accent);flex-shrink:0;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span class="excel-file-name" id="jm-excelFileName">–</span>
                    <button type="button" class="excel-file-clear" id="jm-excelFileClear" aria-label="Hapus file">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                {{-- Template download --}}
                <div class="excel-template-row">
                    <a href="{{ route('jadwal-mengajar.template') }}" class="excel-template-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                             style="display:inline;vertical-align:-2px;margin-right:4px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Unduh Template Excel
                    </a>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalImportJadwal">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Upload &amp; Import
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
window.JM_ROUTES = {
    base:      '/jadwal-mengajar',
    schedule:  '{{ route('jadwal-mengajar.schedule') }}',
    store:     '{{ route('jadwal-mengajar.store') }}',
    available: '{{ route('jadwal-mengajar.available-periods') }}',
    import:    '{{ route('jadwal-mengajar.import') }}',
    template:  '{{ route('jadwal-mengajar.template') }}',
    csrf:      '{{ csrf_token() }}'
};
window.JM_DAYS     = @json(App\Models\ClassPeriod::DAY_LABELS);
window.JM_TEACHERS         = @json($teachers->map(fn($t) => ['id'=>$t->id,'label'=>$t->name])->values());
window.JM_CLASSES          = @json($classes->map(fn($c) => ['id'=>$c->id,'label'=>$c->class.' '.$c->major])->values());
window.JM_ALL_SUBJECTS     = @json($subjects->map(fn($s) => ['id'=>$s->id,'name'=>$s->name])->values());
window.JM_TEACHER_SUBJECTS = @json($teachers->keyBy('id')->map(fn($t) => $t->subjects->map(fn($s) => ['id'=>$s->id,'name'=>$s->name])->values())->all());
window.JM_ALL_PERIODS      = {!! json_encode(
    $periodsByDay->map(fn($g) => $g->map(fn($p) => [
        'id'       => $p->id,
        'sequence' => $p->sequence,
        'label'    => 'Jam '.$p->sequence.' · '.substr($p->start_time,0,5).'–'.substr($p->end_time,0,5),
    ])->values())->all()
) !!};
</script>
<script>{!! file_get_contents(resource_path('views/Jadwal_Mengajar/main.js')) !!}</script>
@endpush
