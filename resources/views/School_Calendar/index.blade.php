@extends('layouts.app')

@section('title', 'Kalender Sekolah')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Sistem</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Kalender Sekolah</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/School_Calendar/style.css')) !!}</style>
@endpush

@section('content')
@php
    $today     = \Carbon\Carbon::today();
    $isToday   = ($today->year === $currentYear && $today->month === $currentMonth);
    $monthData = $days->count() > 0; // month has been generated

    $yearActiveTotal   = $yearSummary->get('aktif', 0);
    $yearHolidayTotal  = $yearSummary->get('libur', 0);
    $yearTotalDays     = $yearActiveTotal + $yearHolidayTotal;
@endphp

<div class="sc-page">

    {{-- ── Flash Messages ─────────────────────────────────── --}}
    @if(session('success'))
    <div class="alert alert--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Page Header ─────────────────────────────────────── --}}
    <div class="sc-header">
        <div>
            <h1 class="sc-title">Kalender Sekolah</h1>
            <p class="sc-subtitle">
                Tahun Ajaran <strong>{{ $currentYear }}/{{ $currentYear + 1 }}</strong>
                &mdash; Kelola hari aktif &amp; hari libur
            </p>
        </div>
        <div class="sc-header__actions">
            <button class="btn btn--secondary" id="btn-open-generate">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                Generate Kalender
            </button>
            @if($yearTotalDays > 0)
            <button class="btn btn--danger" id="btn-open-delete-year">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Hapus Tahun
            </button>
            @endif
        </div>
    </div>

    {{-- ── Monthly Stat Cards ───────────────────────────────── --}}
    <div class="sc-stats">
        <div class="sc-stat-card sc-stat-card--total">
            <span class="sc-stat-card__label">Total Hari</span>
            <span class="sc-stat-card__value">{{ $totalDays }}</span>
            <span class="sc-stat-card__sub">{{ $monthNames[$currentMonth] }}</span>
        </div>
        <div class="sc-stat-card sc-stat-card--active">
            <span class="sc-stat-card__label">Hari Aktif</span>
            <span class="sc-stat-card__value">{{ $schoolDays }}</span>
            <span class="sc-stat-card__sub">Hari sekolah</span>
        </div>
        <div class="sc-stat-card sc-stat-card--holiday">
            <span class="sc-stat-card__label">Hari Libur</span>
            <span class="sc-stat-card__value">{{ $holidayDays }}</span>
            <span class="sc-stat-card__sub">Tanggal merah</span>
        </div>
    </div>

    {{-- ── Navigation Form (hidden, submitted by JS) ─────────── --}}
    <form id="sc-nav-form" method="GET" action="{{ route('school-calendar.index') }}" style="display:none">
        <select id="sc-year-select"  name="year">
            @foreach($years as $yr)
                <option value="{{ $yr }}" {{ $yr === $currentYear ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>
        <select id="sc-month-select" name="month">
            @foreach($monthNames as $num => $name)
                <option value="{{ $num }}" {{ $num === $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </form>

    {{-- ── Calendar Panel ──────────────────────────────────────── --}}
    <div class="sc-panel">

        {{-- Nav bar --}}
        <div class="sc-nav">
            <div class="sc-nav__left">
                <button type="button" class="sc-nav-btn" id="sc-prev-month" title="Bulan sebelumnya">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
            </div>

            <div class="sc-nav__center" style="display:flex;align-items:center;gap:8px;flex:1;justify-content:center;">
                {{-- Year visually-driven selects (mirror hidden form) --}}
                <select class="sc-nav-select" onchange="document.getElementById('sc-year-select').value=this.value;document.getElementById('sc-nav-form').submit()">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $yr === $currentYear ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
                <span class="sc-nav__title" style="color:var(--text-muted);">/</span>
                <select class="sc-nav-select" onchange="document.getElementById('sc-month-select').value=this.value;document.getElementById('sc-nav-form').submit()">
                    @foreach($monthNames as $num => $name)
                        <option value="{{ $num }}" {{ $num === $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sc-nav__right">
                <button type="button" class="sc-nav-btn" id="sc-next-month" title="Bulan berikutnya">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Calendar grid or empty-state --}}
        @if(!$monthData)
        <div class="sc-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8"  y1="2" x2="8"  y2="6"/>
                <line x1="3"  y1="10" x2="21" y2="10"/>
                <line x1="8"  y1="14" x2="8"  y2="14"/>
                <line x1="12" y1="14" x2="12" y2="14"/>
                <line x1="16" y1="14" x2="16" y2="14"/>
            </svg>
            <p>Kalender bulan <strong>{{ $monthNames[$currentMonth] }} {{ $currentYear }}</strong> belum di-generate.</p>
            <button class="btn btn--primary btn--sm" id="btn-open-generate-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                Generate Sekarang
            </button>
        </div>
        @else

        <div class="sc-grid">
            {{-- Week day headers --}}
            <div class="sc-week-header">
                @foreach($dayNames as $dn)
                    <div class="sc-week-day">{{ $dn }}</div>
                @endforeach
            </div>

            {{-- Day cells --}}
            <div class="sc-days">
                {{-- Leading empty cells --}}
                @for($e = 0; $e < $firstDayOffset; $e++)
                    <div class="sc-day sc-day--empty"></div>
                @endfor

                {{-- Actual day cells --}}
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $entry      = $daysMap->get($d);
                        $status     = $entry ? $entry->status : null;
                        $notes      = $entry ? ($entry->notes ?? '') : '';
                        $dayName    = $entry ? $entry->day_name : '';
                        $dateStr    = $entry ? $entry->date->format('d F Y') : '';
                        $todayClass = ($isToday && $d === $today->day) ? 'sc-day--today' : '';

                        // Determine day-of-week for this cell
                        $cellDate   = \Carbon\Carbon::create($currentYear, $currentMonth, $d);
                        $dow        = $cellDate->dayOfWeek; // 0=Sun, 6=Sat
                        $weekendCls = $dow === 0 ? 'sc-day--sun' : ($dow === 6 ? 'sc-day--sat' : '');

                        $statusClass = '';
                        if ($status === 'aktif') $statusClass = 'sc-day--active';
                        elseif ($status === 'libur') $statusClass = 'sc-day--holiday';
                        elseif (!$entry) $statusClass = 'sc-day--nodata';
                    @endphp

                    @if($entry)
                    <div class="sc-day {{ $statusClass }} {{ $todayClass }} {{ $weekendCls }}"
                         data-id="{{ $entry->id }}"
                         data-day="{{ $d }}"
                         data-dayname="{{ $dayName }}"
                         data-full="{{ $dayName }}, {{ $dateStr }}"
                         data-status="{{ $status }}"
                         data-notes="{{ $notes }}"
                         title="{{ $dayName }}, {{ $dateStr }}{{ $notes ? ' – ' . $notes : '' }}">
                        <span class="sc-day__num">{{ $d }}</span>
                        <span class="sc-day__dot"></span>
                        @if($notes)
                            <span class="sc-day__note-dot" title="{{ $notes }}"></span>
                        @endif
                    </div>
                    @else
                    <div class="sc-day sc-day--nodata {{ $todayClass }} {{ $weekendCls }}">
                        <span class="sc-day__num">{{ $d }}</span>
                    </div>
                    @endif
                @endfor

                {{-- Trailing empty cells (fill last row) --}}
                @php
                    $totalCells = $firstDayOffset + $daysInMonth;
                    $trailing   = (7 - ($totalCells % 7)) % 7;
                @endphp
                @for($t = 0; $t < $trailing; $t++)
                    <div class="sc-day sc-day--empty"></div>
                @endfor
            </div>
        </div>

        {{-- Legend --}}
        <div class="sc-legend">
            <div class="sc-legend-item">
                <span class="sc-legend-dot sc-legend-dot--active"></span> Hari Aktif
            </div>
            <div class="sc-legend-item">
                <span class="sc-legend-dot sc-legend-dot--holiday"></span> Hari Libur
            </div>
            <div class="sc-legend-item">
                <span class="sc-legend-dot sc-legend-dot--today"></span> Hari Ini
            </div>
            <div class="sc-legend-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8"
                     style="color:var(--accent);flex-shrink:0;">
                    <circle cx="4" cy="4" r="3" fill="currentColor"/>
                </svg>
                Ada Catatan
            </div>
        </div>
        @endif

    </div>{{-- end sc-panel --}}

    {{-- ── Year Summary ─────────────────────────────────────── --}}
    @if($yearTotalDays > 0)
    <div class="sc-year-panel">
        <div class="sc-year-panel__header">
            <h3 class="sc-year-panel__title">
                Ringkasan Tahun {{ $currentYear }}
            </h3>
            <span style="font-size:0.78rem;color:var(--text-muted);">Data keseluruhan tahun</span>
        </div>
        <div class="sc-year-grid">
            <div class="sc-year-stat">
                <div class="sc-year-stat__icon sc-year-stat__icon--all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <div class="sc-year-stat__num">{{ $yearTotalDays }}</div>
                    <div class="sc-year-stat__label">Total hari dalam kalender</div>
                </div>
            </div>
            <div class="sc-year-stat">
                <div class="sc-year-stat__icon sc-year-stat__icon--active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 11 12 14 22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div>
                    <div class="sc-year-stat__num">{{ $yearActiveTotal }}</div>
                    <div class="sc-year-stat__label">Hari aktif / sekolah</div>
                </div>
            </div>
            <div class="sc-year-stat">
                <div class="sc-year-stat__icon sc-year-stat__icon--holiday">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>
                    <div class="sc-year-stat__num">{{ $yearHolidayTotal }}</div>
                    <div class="sc-year-stat__label">Hari libur total</div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>{{-- end sc-page --}}


{{-- ══════════════════════════════════════════════════════════
     MODAL – Edit Hari
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-edit-day" style="display:none">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 class="modal-title">Edit Tanggal</h3>
            <button class="modal-close" data-close-modal aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form id="form-edit-day" method="POST" data-base-url="{{ route('school-calendar.update', ['id' => '__ID__']) }}">
            @csrf
            @method('PUT')
            <div class="modal-body">

                {{-- Date display --}}
                <div class="sc-modal-date">
                    <span class="sc-modal-date__num" id="edit-date-num">1</span>
                    <div class="sc-modal-date__info">
                        <span class="sc-modal-date__day"  id="edit-date-day">Senin</span>
                        <span class="sc-modal-date__full" id="edit-date-full">1 Januari 2025</span>
                    </div>
                </div>

                {{-- Status choice --}}
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">Status Hari</label>
                    <div class="sc-status-choice">
                        <button type="button" id="btn-status-active" class="sc-status-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 11 12 14 22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            Hari Aktif
                        </button>
                        <button type="button" id="btn-status-holiday" class="sc-status-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Hari Libur
                        </button>
                    </div>
                    <input type="hidden" name="status" id="input-status" value="aktif">
                </div>

                {{-- Notes (shown only for holiday) --}}
                <div class="form-group" id="edit-notes-group" style="display:none">
                    <label class="form-label" for="edit-notes">
                        Keterangan Libur
                    </label>
                    <textarea id="edit-notes" name="notes" class="form-control"
                              placeholder="Contoh: Hari Raya Idul Fitri..." rows="3"></textarea>
                    <span class="form-hint">Opsional – keterangan tentang jenis libur.</span>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal>Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="btn-text">Simpan Perubahan</span>
                    <span class="spinner"></span>
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════
     MODAL – Generate Kalender
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-generate" style="display:none">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 class="modal-title">Generate Kalender</h3>
            <button class="modal-close" data-close-modal aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('school-calendar.generate') }}">
            @csrf
            <div class="modal-body">
                <div class="sc-gen-info">
                    <strong>Catatan:</strong> Generate kalender akan membuat seluruh 365/366 hari untuk tahun yang dipilih.
                    Jika kalender untuk tahun tersebut sudah ada, data yang sudah ada akan dipertahankan dan
                    hanya hari yang belum ada yang akan ditambahkan.
                </div>
                <div class="form-group">
                    <label class="form-label" for="gen-year">
                        Pilih Tahun <span class="req">*</span>
                    </label>
                    <select id="gen-year" name="year" class="form-control" required>
                        @foreach(range(date('Y') - 2, date('Y') + 3) as $yr)
                            <option value="{{ $yr }}" {{ $yr == $currentYear ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    <span class="form-hint">Kalender akan mencakup 1 Januari s.d. 31 Desember {{ $currentYear }}.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal>Batal</button>
                <button type="submit" class="btn btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    <span class="btn-text">Generate Sekarang</span>
                    <span class="spinner"></span>
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════
     MODAL – Hapus Seluruh Tahun
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-delete-year" style="display:none">
    <div class="modal modal--xs">
        <div class="modal-header">
            <h3 class="modal-title" style="color:#dc2626;">Hapus Kalender Tahun</h3>
            <button class="modal-close" data-close-modal aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="form-delete-year" method="POST" action="{{ route('school-calendar.destroy-year') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="year" value="{{ $currentYear }}">
            <div class="modal-body">
                <div class="delete-body">
                    <div class="delete-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </div>
                    <p style="font-size:0.9rem;color:var(--text-primary);font-weight:600;margin-bottom:8px;">
                        Hapus kalender tahun <span id="del-year-span">{{ $currentYear }}</span>?
                    </p>
                    <p style="font-size:0.82rem;color:var(--text-muted);line-height:1.6;margin:0;">
                        Seluruh <strong>{{ $yearTotalDays }} data hari</strong> untuk tahun <strong>{{ $currentYear }}</strong>
                        akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal>Batal</button>
                <button type="submit" class="btn btn--danger">
                    <span class="btn-text">Ya, Hapus Semua</span>
                    <span class="spinner"></span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Wire the "Generate Sekarang" button inside empty-state to open the same modal
    document.addEventListener('DOMContentLoaded', function() {
        var btn2 = document.getElementById('btn-open-generate-2');
        var btn1 = document.getElementById('btn-open-generate');
        if (btn2 && btn1) btn2.addEventListener('click', function(){ btn1.click(); });
    });
</script>
<script>{!! file_get_contents(resource_path('views/School_Calendar/main.js')) !!}</script>
@endpush
