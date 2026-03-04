@extends('layouts.app')

@section('title', 'Kehadiran Guru')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Kehadiran</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Kehadiran Guru</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Kehadiran_Guru/style.css')) !!}</style>
<style>
/* ── Mode Tabs ── */
.mode-tabs{display:flex;gap:0;border-bottom:2px solid var(--card-border);margin-bottom:20px}
.mode-tab{padding:10px 24px;font-size:.875rem;font-weight:600;color:var(--text-muted);text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:color .15s,border-color .15s;display:flex;align-items:center;gap:8px}
.mode-tab:hover{color:var(--text-primary)}
.mode-tab.active{color:var(--accent);border-bottom-color:var(--accent)}
</style>
@endpush

@section('content')
<div class="kg-page" style="display:flex;flex-direction:column;gap:20px;">

    {{-- ── Flash Messages ─────────────────────────────── --}}
    @if(session('success'))
    <div class="alert alert--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Mode Tabs ───────────────────────────────────── --}}
    <div class="mode-tabs">
        <a href="{{ route('attendance.teacher.index') }}" class="mode-tab active">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Per Hari
        </a>
        <a href="{{ route('attendance.teacher.period') }}" class="mode-tab">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            Per Jam Pelajaran
        </a>
    </div>

    {{-- ── Page Header ─────────────────────────────────── --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">
                Data absensi guru
                &mdash;
                <span class="date-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    {{ \Carbon\Carbon::parse($dateFrom)->locale('id')->isoFormat('D MMM YYYY') }}
                    &ndash;
                    {{ \Carbon\Carbon::parse($dateTo)->locale('id')->isoFormat('D MMM YYYY') }}
                </span>
            </p>
        </div>
        <button type="button" class="btn btn--primary" data-open-modal="modalManualInput">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Input Manual
        </button>
    </div>

    {{-- ── Filter Toolbar ──────────────────────────────── --}}
    <form method="GET" action="{{ route('attendance.teacher.index') }}"
          style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">

        {{-- Date Range --}}
        <div style="display:flex;align-items:center;gap:6px;">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control" style="width:auto;">
            <span style="color:var(--text-muted);font-size:0.85rem;white-space:nowrap;">s/d</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control" style="width:auto;">
        </div>

        {{-- Status --}}
        <select name="status" class="dg-filter-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach(['hadir','sakit','izin','dinas','alpa','terlambat'] as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        {{-- Search --}}
        <div class="dg-search" style="flex:1;min-width:220px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Cari nama guru..."
                   value="{{ $search }}" autocomplete="off">
        </div>

        <button type="submit" class="btn btn--primary btn--sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Cari
        </button>

        @if($hasFilters)
            <a href="{{ route('attendance.teacher.index') }}" class="btn btn--ghost btn--sm">Reset</a>
        @endif
    </form>

    {{-- ── Summary Cards ───────────────────────────────── --}}
    <div class="stat-grid-guru">
        @php
            $statConfig = [
                'hadir'     => ['label' => 'Hadir'],
                'izin'      => ['label' => 'Izin'],
                'sakit'     => ['label' => 'Sakit'],
                'dinas'     => ['label' => 'Dinas'],
                'alpa'      => ['label' => 'Alpa'],
                'terlambat' => ['label' => 'Terlambat'],
            ];
        @endphp
        @foreach($statConfig as $key => $cfg)
        <div class="stat-card">
            <div class="stat-icon stat-icon--{{ $key }}">
                @if($key === 'hadir')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                @elseif($key === 'izin')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                @elseif($key === 'sakit')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                @elseif($key === 'dinas')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                @elseif($key === 'alpa')
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                @endif
            </div>
            <div>
                <div class="stat-val">{{ $summary[$key] ?? 0 }}</div>
                <div class="stat-lbl">{{ $cfg['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Data Table ───────────────────────────────────── --}}
    <div class="data-card">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px 0;gap:10px;">
            <span style="font-size:0.875rem;font-weight:600;color:var(--text-secondary);">
                {{ $attendances->total() }} data ditemukan
            </span>
            <a href="{{ route('attendance.teacher.export', array_filter(['date_from'=>$dateFrom,'date_to'=>$dateTo,'status'=>$status])) }}"
               class="btn btn--secondary btn--sm" title="Export ke Excel">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export Excel
            </a>
        </div>
        <div class="table-scroll">
        <table class="data-table" style="min-width:720px;">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Nama Guru</th>
                    <th>NIP</th>
                    <th>Tanggal</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th style="width:52px;text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $i => $att)
                <tr id="row-{{ $att->id }}">
                    <td style="color:var(--text-muted);font-size:0.82rem;">
                        {{ $attendances->firstItem() + $i }}
                    </td>
                    <td>
                        <span style="font-weight:600;color:var(--text-primary);">
                            {{ $att->teacher->name ?? '–' }}
                        </span>
                        @if($att->teacher?->department)
                            <span style="display:block;font-size:0.76rem;color:var(--text-muted);">
                                {{ is_array($att->teacher->department)
                                    ? implode(', ', $att->teacher->department)
                                    : $att->teacher->department }}
                            </span>
                        @endif
                    </td>
                    <td style="font-family:monospace;font-size:0.82rem;color:var(--text-muted);">
                        {{ $att->teacher->nip ?? '–' }}
                    </td>
                    <td style="font-size:0.83rem;color:var(--text-secondary);white-space:nowrap;">
                        {{ optional($att->calendar)->date?->locale('id')->isoFormat('D MMM YY') ?? '–' }}
                    </td>
                    <td>
                        <span id="ci-{{ $att->id }}" class="att-time {{ $att->check_in_time ? '' : 'att-time--empty' }}">
                            {{ $att->check_in_time?->format('H:i') ?? '–' }}
                        </span>
                    </td>
                    <td>
                        <span id="co-{{ $att->id }}" class="att-time {{ $att->check_out_time ? '' : 'att-time--empty' }}">
                            {{ $att->check_out_time?->format('H:i') ?? '–' }}
                        </span>
                    </td>
                    <td>
                        <span id="badge-{{ $att->id }}" class="att-badge att-badge--{{ $att->status }}">
                            {{ ucfirst($att->status) }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div class="kg-action">
                            <button type="button" class="kg-action__btn" title="Aksi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                     fill="currentColor">
                                    <circle cx="12" cy="5" r="1.5"/>
                                    <circle cx="12" cy="12" r="1.5"/>
                                    <circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div class="kg-dropdown">
                                {{-- Detail --}}
                                <button type="button" class="kg-dropdown__item" data-detail-btn
                                        data-att-id="{{ $att->id }}"
                                        data-name="{{ addslashes($att->teacher->name ?? '–') }}"
                                        data-nip="{{ $att->teacher->nip ?? '–' }}"
                                        data-department="{{ is_array($att->teacher->department ?? null) ? implode(', ', $att->teacher->department) : ($att->teacher->department ?? '–') }}"
                                        data-tanggal="{{ optional($att->calendar)->date?->locale('id')->isoFormat('D MMMM YYYY') ?? '–' }}"
                                        data-status="{{ $att->status }}"
                                        data-check-in="{{ $att->check_in_time?->format('H:i') ?? '' }}"
                                        data-check-out="{{ $att->check_out_time?->format('H:i') ?? '' }}"
                                        data-source="{{ $att->source ?? 'manual' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    Detail
                                </button>

                                <div class="kg-dropdown__separator"></div>

                                {{-- Edit --}}
                                <button type="button" class="kg-dropdown__item" data-edit-btn
                                        data-att-id="{{ $att->id }}"
                                        data-name="{{ addslashes($att->teacher->name ?? '–') }}"
                                        data-status="{{ $att->status }}"
                                        data-check-in="{{ $att->check_in_time?->format('H:i') ?? '' }}"
                                        data-check-out="{{ $att->check_out_time?->format('H:i') ?? '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </button>

                                <div class="kg-dropdown__separator"></div>

                                {{-- Hapus --}}
                                <button type="button" class="kg-dropdown__item kg-dropdown__item--danger" data-delete-btn
                                        data-att-id="{{ $att->id }}"
                                        data-name="{{ addslashes($att->teacher->name ?? '–') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14H6L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                        <path d="M9 6V4h6v2"/>
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="data-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                            Tidak ada data absensi untuk periode
                                <strong>{{ \Carbon\Carbon::parse($dateFrom)->locale('id')->isoFormat('D MMM YYYY') }}</strong>
                                &ndash;
                                <strong>{{ \Carbon\Carbon::parse($dateTo)->locale('id')->isoFormat('D MMM YYYY') }}</strong>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if($attendances->hasPages())
        <div class="data-pagination">
            <nav class="psc-pagination">
                @if($attendances->onFirstPage())
                    <span class="psc-page psc-page--disabled">&laquo;</span>
                @else
                    <a class="psc-page" href="{{ $attendances->previousPageUrl() }}">&laquo;</a>
                @endif

                @foreach($attendances->getUrlRange(1, $attendances->lastPage()) as $page => $url)
                    @if($page == $attendances->currentPage())
                        <span class="psc-page psc-page--active">{{ $page }}</span>
                    @elseif(abs($page - $attendances->currentPage()) <= 2 || $page == 1 || $page == $attendances->lastPage())
                        <a class="psc-page" href="{{ $url }}">{{ $page }}</a>
                    @elseif(abs($page - $attendances->currentPage()) == 3)
                        <span class="psc-page psc-page--dots">…</span>
                    @endif
                @endforeach

                @if($attendances->hasMorePages())
                    <a class="psc-page" href="{{ $attendances->nextPageUrl() }}">&raquo;</a>
                @else
                    <span class="psc-page psc-page--disabled">&raquo;</span>
                @endif
            </nav>
        </div>
        @endif
    </div>

</div>{{-- /kg-page --}}

{{-- ═══════════════════════════════════════════════════════════
     MODAL: INPUT MANUAL
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalManualInput">
    <div class="modal modal--lg">
        <div class="modal-header">
            <h2 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="color:var(--accent);">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Input Absensi Manual
            </h2>
            <button type="button" class="modal-close" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('attendance.teacher.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                @if($errors->any())
                <div class="alert alert--error" style="margin-bottom:14px;">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="form-grid-2">
                    {{-- Nama Guru (Autocomplete) --}}
                    <div class="form-group form-col-full">
                        <label class="form-label">Nama Guru <span class="req">*</span></label>
                        <div class="autocomplete-wrap">
                            <input type="text" id="manualNama" class="form-control"
                                   placeholder="Ketik nama guru..." autocomplete="off" required
                                   value="{{ old('manual_name') }}">
                            <div class="autocomplete-list" id="autocompleteList"></div>
                        </div>
                        <input type="hidden" name="teacher_id" id="manualTeacherId"
                               value="{{ old('teacher_id') }}">
                        <p class="form-hint" style="margin-top:5px;">
                            Mapel/Dept: <strong id="manualDeptDisplay">–</strong>
                        </p>
                    </div>

                    {{-- Tanggal --}}
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="req">*</span></label>
                        <input type="date" name="date" class="form-control"
                               value="{{ old('date', today()->toDateString()) }}" required>
                    </div>

                    {{-- Status --}}
                    <div class="form-group">
                        <label class="form-label">Status Kehadiran <span class="req">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="">— Pilih Status —</option>
                            @foreach(['hadir','sakit','izin','dinas','alpa','terlambat'] as $s)
                                <option value="{{ $s }}" {{ old('status') === $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Check In --}}
                    <div class="form-group">
                        <label class="form-label">Jam Masuk (Check In)</label>
                        <input type="time" name="check_in_time" class="form-control"
                               value="{{ old('check_in_time') }}">
                        <p class="form-hint">Kosongkan jika tidak ada</p>
                    </div>

                    {{-- Check Out --}}
                    <div class="form-group">
                        <label class="form-label">Jam Keluar (Check Out)</label>
                        <input type="time" name="check_out_time" class="form-control"
                               value="{{ old('check_out_time') }}">
                        <p class="form-hint">Kosongkan jika tidak ada</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn--ghost modal-close">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Absensi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MODAL: DETAIL ABSENSI
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDetail">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h2 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="color:var(--accent);">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Detail Absensi
            </h2>
            <button type="button" class="modal-close" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <dl class="detail-dl">
                <div class="detail-dl__row">
                    <dt>Nama Guru</dt>
                    <dd id="detail-name">–</dd>
                </div>
                <div class="detail-dl__row">
                    <dt>NIP</dt>
                    <dd id="detail-nip" style="font-family:monospace;">–</dd>
                </div>
                <div class="detail-dl__row">
                    <dt>Mapel/Dept</dt>
                    <dd id="detail-dept">–</dd>
                </div>
                <div class="detail-dl__row">
                    <dt>Tanggal</dt>
                    <dd id="detail-tanggal">–</dd>
                </div>
                <div class="detail-dl__row">
                    <dt>Status</dt>
                    <dd id="detail-status">–</dd>
                </div>
                <div class="detail-dl__row">
                    <dt>Jam Masuk</dt>
                    <dd id="detail-check-in" style="font-family:monospace;">–</dd>
                </div>
                <div class="detail-dl__row">
                    <dt>Jam Keluar</dt>
                    <dd id="detail-check-out" style="font-family:monospace;">–</dd>
                </div>
                <div class="detail-dl__row">
                    <dt>Metode Input</dt>
                    <dd id="detail-source">–</dd>
                </div>
            </dl>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost modal-close">Tutup</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MODAL: EDIT ABSENSI (status + waktu)
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditAbsensi">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h2 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="color:var(--accent);">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Absensi
            </h2>
            <button type="button" class="modal-close" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form id="formEditAbsensi">
            <div class="modal-body" style="display:flex;flex-direction:column;gap:16px;">
                <p style="font-size:0.84rem;color:var(--text-muted);margin-bottom:-4px;">
                    Guru: <strong id="editModalName" style="color:var(--text-primary);"></strong>
                </p>
                <div class="form-group">
                    <label class="form-label">Status Kehadiran <span class="req">*</span></label>
                    <select id="editStatus" class="form-control" required>
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                        <option value="dinas">Dinas</option>
                        <option value="alpa">Alpa</option>
                    </select>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Jam Masuk (Check In)</label>
                        <input type="time" id="editCheckIn" class="form-control">
                        <p class="form-hint">Kosongkan untuk menghapus</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Keluar (Check Out)</label>
                        <input type="time" id="editCheckOut" class="form-control">
                        <p class="form-hint">Kosongkan untuk menghapus</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost modal-close">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MODAL: KONFIRMASI HAPUS
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDeleteConfirm">
    <div class="modal modal--xs">
        <div class="modal-header">
            <h2 class="modal-title modal-title--danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4h6v2"/>
                </svg>
                Hapus Absensi
            </h2>
            <button type="button" class="modal-close" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:0.9rem;color:var(--text-secondary);line-height:1.6;">
                Hapus data absensi
                <strong id="deleteTeacherName" style="color:var(--text-primary);"></strong>?<br>
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost modal-close">Batal</button>
            <button type="button" class="btn btn--danger" id="confirmDeleteBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                </svg>
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Kehadiran_Guru/main.js')) !!}</script>
@endpush
