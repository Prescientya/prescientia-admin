@extends('layouts.app')

@section('title', 'Jam Pelajaran')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Akademik</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Jam Pelajaran</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Jam_Pelajaran/style.css')) !!}</style>
@endpush

@section('content')
<div class="jp-page">

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="jp-header">
        <p class="jp-subtitle">
            Kelola jadwal jam pelajaran per hari &mdash; <strong>1 JP = 40 menit</strong>, masuk 06:30 s.d. 15:00
        </p>
        <button class="btn btn--primary" id="btnTambahJam">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Jam
        </button>
    </div>

    {{-- Day Tabs --}}
    <div class="jp-tabs" id="jpTabs">
        @foreach(App\Models\ClassPeriod::DAYS as $day)
        @php $label = App\Models\ClassPeriod::DAY_LABELS[$day]; @endphp
        <button class="jp-tab-btn {{ $loop->first ? 'active' : '' }}" data-day="{{ $day }}">
            {{ $label }}
            <span class="jp-tab-count" id="count-{{ $day }}">
                {{ $grouped[$day]->where('activity_type', 'lesson')->count() }} JP
            </span>
        </button>
        @endforeach
    </div>

    {{-- Day Panels --}}
    @foreach(App\Models\ClassPeriod::DAYS as $day)
    @php $label = App\Models\ClassPeriod::DAY_LABELS[$day]; @endphp
    <div class="jp-panel {{ $loop->first ? 'active' : '' }}" id="panel-{{ $day }}">

        <div class="jp-panel-header">
            <span class="jp-panel-title">Jadwal {{ $label }}</span>
            <button class="btn btn--ghost btn--sm jp-reset-btn" data-day="{{ $day }}"
                    title="Reset jadwal {{ $label }} ke default">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 .49-3.68"/>
                </svg>
                Reset ke Default
            </button>
        </div>

        <div class="jp-table-wrap" id="table-{{ $day }}">
            @include('Jam_Pelajaran._table', ['periods' => $grouped[$day], 'day' => $day])
        </div>

    </div>
    @endforeach

    {{-- ── Legenda ─────────────────────────────────────── --}}
    <div class="jp-legend">
        <span class="jp-legend-title">Keterangan Warna:</span>
        <span class="jp-badge jp-badge--lesson">Jam Pelajaran</span>
        <span class="jp-badge jp-badge--break">Istirahat</span>
        <span class="jp-badge jp-badge--ceremony">Upacara</span>
        <span class="jp-badge jp-badge--prayer">Ibadah / Sholat</span>
        <span class="jp-badge jp-badge--cleaning">Kebersihan</span>
        <span class="jp-badge jp-badge--other">Lainnya</span>
    </div>

</div>

{{-- ═══════════════════════════════════════
     MODAL: Tambah / Edit Jam
═════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalJam" aria-hidden="true">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalJamTitle">Tambah Jam Pelajaran</h3>
            <button class="modal-close" data-close-modal="modalJam">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formJam" novalidate>
                @csrf
                <input type="hidden" id="jamId" value="">
                <input type="hidden" id="jamMethod" value="POST">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Hari <span class="req">*</span></label>
                        <select id="jam_day" name="day" class="form-control" required>
                            @foreach(App\Models\ClassPeriod::DAYS as $d)
                            <option value="{{ $d }}">{{ App\Models\ClassPeriod::DAY_LABELS[$d] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutan (Sequence) <span class="req">*</span></label>
                        <input type="number" id="jam_sequence" name="sequence" class="form-control"
                               min="0" max="30" required placeholder="0, 1, 2 ...">
                        <span class="form-hint">Urutan slot dalam hari (0 = paling pagi)</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Mulai <span class="req">*</span></label>
                        <input type="time" id="jam_start" name="start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Selesai <span class="req">*</span></label>
                        <input type="time" id="jam_end" name="end_time" class="form-control" required>
                        <span class="form-hint" id="durasiHint" style="color:var(--accent)"></span>
                    </div>
                    <div class="form-group form-col-full">
                        <label class="form-label">Jenis Aktivitas <span class="req">*</span></label>
                        <select id="jam_type" name="activity_type" class="form-control" required>
                            @foreach(App\Models\ClassPeriod::ACTIVITY_TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-col-full">
                        <label class="form-label">Keterangan</label>
                        <input type="text" id="jam_note" name="note" class="form-control"
                               maxlength="200" placeholder="Opsional, contoh: Upacara Bendera, MBG, ...">
                    </div>
                </div>

                <div id="formJamError" class="alert alert--error" style="display:none;margin-top:12px;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalJam">Batal</button>
            <button type="button" class="btn btn--primary" id="btnSimpanJam">
                <span class="spinner"></span>
                <span class="btn-text">Simpan</span>
            </button>
        </div>
    </div>
</div>

{{-- ── Modal Konfirmasi Hapus ─────────────────────────── --}}
<div class="modal-overlay" id="modalHapusJam" aria-hidden="true">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <h3 class="modal-title">Hapus Jam Pelajaran</h3>
            <button class="modal-close" data-close-modal="modalHapusJam">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center;padding:24px;">
            <div style="font-size:2.5rem;margin-bottom:12px;">🗑</div>
            <p style="font-weight:600;margin-bottom:6px;">Hapus slot jam ini?</p>
            <p style="color:var(--text-secondary);font-size:0.88rem;" id="hapusJamDesc"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn--ghost" data-close-modal="modalHapusJam">Batal</button>
            <button class="btn btn--danger" id="btnKonfirmasiHapus">
                <span class="spinner"></span>
                <span class="btn-text">Ya, Hapus</span>
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
window.JP_ROUTES = {
    store:  '{{ route('jam-pelajaran.store') }}',
    base:   '/jam-pelajaran',
    csrf:   '{{ csrf_token() }}'
};
</script>
<script>{!! file_get_contents(resource_path('views/Jam_Pelajaran/main.js')) !!}</script>
@endpush
