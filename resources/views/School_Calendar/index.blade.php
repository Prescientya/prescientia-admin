@extends('layouts.app')

@section('title', 'Kalender Sekolah')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Sistem</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Kalender Sekolah</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/School_Calendar/style.css')) !!}</style>
@endpush

@section('content')
<div class="page-wrap">

    {{-- ── Flash Messages ─────────────────────────────── --}}
    @if(session('success'))
    <div class="alert alert--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Page Header ─────────────────────────────────── --}}
    <div class="page-header">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @if($daysMap->isNotEmpty())
            <button type="button" class="btn btn--ghost btn--sm" data-open-modal="modalDeleteYear"
                    style="color:#ef4444;border-color:rgba(239,68,68,0.3);">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
                Hapus Tahun {{ $currentYear }}
            </button>
            @endif
            <button type="button" class="btn btn--primary" data-open-modal="modalGenerate">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Buat Kalender
            </button>
        </div>
    </div>

    {{-- ── Year + Month Nav Card ─────────────────────────── --}}
    <div class="sc-nav-card">
        <div class="sc-year-row">
            <div class="sc-tabs">
                @foreach($years as $y)
                <a href="{{ route('school-calendar.index', ['year' => $y, 'month' => $currentMonth]) }}"
                   class="sc-tab {{ $y == $currentYear ? 'sc-tab--active' : '' }}">{{ $y }}</a>
                @endforeach
            </div>
            @if($yearSummary->isNotEmpty())
            <div class="sc-year-summary">
                <span class="sc-year-badge sc-year-badge--school">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    {{ $yearSummary->get('aktif', 0) }} hari sekolah
                </span>
                <span class="sc-year-badge sc-year-badge--holiday">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    {{ $yearSummary->get('libur', 0) }} hari libur
                </span>
            </div>
            @endif
        </div>
        <div class="sc-divider"></div>
        <div class="sc-months">
            @foreach($monthNames as $num => $name)
            <a href="{{ route('school-calendar.index', ['year' => $currentYear, 'month' => $num]) }}"
               class="sc-month-tab {{ $num == $currentMonth ? 'sc-month-tab--active' : '' }}">{{ $name }}</a>
            @endforeach
        </div>
    </div>

    {{-- ── Monthly stats ───────────────────────────────── --}}
    @if($totalDays > 0)
    <div class="sc-stats" style="margin-top:12px;">
        <div class="sc-stat-card sc-stat-card--total">
            <div class="sc-stat-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="sc-stat-card__body">
                <div class="sc-stat-card__value">{{ $totalDays }}</div>
                <div class="sc-stat-card__label">Total Hari</div>
            </div>
        </div>
        <div class="sc-stat-card sc-stat-card--school">
            <div class="sc-stat-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="sc-stat-card__body">
                <div class="sc-stat-card__value">{{ $schoolDays }}</div>
                <div class="sc-stat-card__label">Hari Sekolah</div>
            </div>
        </div>
        <div class="sc-stat-card sc-stat-card--holiday">
            <div class="sc-stat-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="sc-stat-card__body">
                <div class="sc-stat-card__value">{{ $holidayDays }}</div>
                <div class="sc-stat-card__label">Hari Libur</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Calendar Grid ────────────────────────────────── --}}
    <div class="sc-calendar-wrap">

        {{-- Header --}}
        <div class="sc-cal-header">
            <p class="sc-cal-header__title">{{ $monthNames[$currentMonth] }} {{ $currentYear }}</p>
            <p class="sc-cal-header__range">
                {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->locale('id')->isoFormat('D MMMM') }}
                &ndash;
                {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->locale('id')->isoFormat('D MMMM YYYY') }}
            </p>
            <div style="margin-left:auto;">
                <div class="sc-legend">
                    <div class="sc-legend__item"><span class="sc-legend__dot sc-legend__dot--school"></span><span>Hari Sekolah</span></div>
                    <div class="sc-legend__item"><span class="sc-legend__dot sc-legend__dot--holiday"></span><span>Hari Libur</span></div>
                    <div class="sc-legend__item"><span class="sc-legend__dot sc-legend__dot--today"></span><span>Hari Ini</span></div>
                </div>
            </div>
        </div>

        @if($daysMap->isEmpty())
        <div class="sc-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <div>
                <strong>Kalender belum dibuat untuk tahun {{ $currentYear }}</strong>
                <p>Klik <strong>Buat Kalender</strong> untuk mengisi otomatis berdasarkan hari libur Indonesia.</p>
            </div>
            <button type="button" class="btn btn--primary" data-open-modal="modalGenerate">Buat Kalender Sekarang</button>
        </div>
        @else

        {{-- Day-of-week header --}}
        <div class="sc-cal-dow">
            @foreach($dayNames as $dn)
            <div class="sc-cal-dow__cell">{{ $dn }}</div>
            @endforeach
        </div>

        {{-- Day cells --}}
        @php
            $today      = now()->format('Y-m-d');
            $totalCells = $firstDayOffset + $daysInMonth;
            $rows       = (int) ceil($totalCells / 7);
        @endphp
        <div class="sc-cal-grid">
            {{-- Leading empty cells --}}
            @for($e = 0; $e < $firstDayOffset; $e++)
            <div class="sc-cal-day sc-cal-day--empty"></div>
            @endfor

            @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $entry         = $daysMap->get($d);
                $isHoliday     = $entry && $entry->status === 'libur';
                $isToday       = $entry && $entry->date->format('Y-m-d') === $today;
                $notes         = $entry ? ($entry->notes ?? '') : '';
                $isWeekendNote = in_array($notes, ['Sabtu', 'Minggu']);
                $cellClass     = 'sc-cal-day';
                if ($entry) {
                    $cellClass .= $isHoliday ? ' sc-cal-day--holiday' : ' sc-cal-day--school';
                    $cellClass .= ' sc-cal-day--clickable';
                }
                if ($isToday) $cellClass .= ' sc-cal-day--today';
            @endphp
            @if($entry)
            <div class="{{ $cellClass }}"
                 data-id="{{ $entry->id }}"
                 data-day="{{ $d }}"
                 data-status="{{ $entry->status }}"
                 data-notes="{{ $notes }}"
                 data-date-str="{{ $entry->day_name }}, {{ $d }} {{ $monthNames[$currentMonth] }} {{ $currentYear }}"
                 title="{{ $entry->day_name }}, {{ $d }} {{ $monthNames[$currentMonth] }} — Klik untuk edit">
                <span class="sc-cal-day__num">{{ $d }}</span>
                @if($notes)
                <span class="sc-cal-day__note {{ $isWeekendNote ? 'sc-cal-day__note--weekend' : '' }}">{{ $notes }}</span>
                @endif
                <span class="sc-cal-day__edit-hint">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </span>
            </div>
            @else
            <div class="sc-cal-day sc-cal-day--empty" style="opacity:0.35;">
                <span class="sc-cal-day__num" style="background:transparent;color:var(--text-muted);">{{ $d }}</span>
            </div>
            @endif
            @endfor

            {{-- Trailing empty cells --}}
            @php $trailing = $rows * 7 - ($firstDayOffset + $daysInMonth); @endphp
            @for($e = 0; $e < $trailing; $e++)
            <div class="sc-cal-day sc-cal-day--empty"></div>
            @endfor
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Buat Kalender
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalGenerate">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px;">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Buat Kalender
            </h3>
            <button class="modal-close" data-close-modal="modalGenerate">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('school-calendar.generate') }}" method="POST" data-loading>
            @csrf
            <div class="modal-body">

                <div class="sc-gen-card">
                    <div class="sc-gen-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <p class="sc-gen-card__title">Generator Kalender Otomatis</p>
                    <p class="sc-gen-card__sub">Mengisi semua hari dalam satu tahun beserta hari libur nasional Indonesia secara otomatis.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Tahun <span class="req">*</span></label>
                    <select name="year" id="generateYear" class="form-control" required>
                        @foreach(range(2024, 2030) as $y)
                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint" id="generateYearDesc" style="margin-top:6px;color:var(--text-muted);font-size:0.82rem;">
                        Kalender tahun {{ $currentYear }} akan dibuat ulang jika sudah ada.
                    </p>
                </div>

                <div style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.3);border-radius:8px;padding:10px 12px;font-size:0.82rem;color:#b45309;line-height:1.5;">
                    <strong>⚠ Perhatian:</strong> Data kalender yang sudah ada untuk tahun tersebut akan <strong>dihapus dan diganti</strong>. Proses ini mungkin membutuhkan beberapa saat.
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalGenerate">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Buat Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Edit Tanggal (klik tanggal di kalender)
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditEntry">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px;">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Edit Tanggal
            </h3>
            <button class="modal-close" data-close-modal="modalEditEntry">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="editEntryForm"
              action=""
              data-base-action="{{ route('school-calendar.update', '__ID__') }}"
              method="POST" data-loading>
            @csrf
            @method('PUT')
            <div class="modal-body">
                {{-- Date info card --}}
                <div class="sc-edit-date-card">
                    <div id="editDateIcon" class="sc-edit-date-icon sc-edit-date-icon--school">1</div>
                    <div class="sc-edit-date-info">
                        <p id="editDateFull" class="sc-edit-date-info__full">—</p>
                        <p id="editDateStatus" class="sc-edit-date-info__status">—</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Ubah Status <span class="req">*</span></label>
                    <select name="status" id="editEntryStatus" class="form-control" required>
                        <option value="aktif">Hari Sekolah</option>
                        <option value="libur">Hari Libur</option>
                    </select>
                </div>
                <div class="form-group" id="editNotesWrap" style="display:none;">
                    <label class="form-label">Keterangan Libur</label>
                    <input type="text" name="notes" id="editEntryNotes" class="form-control"
                           placeholder="Contoh: Idul Fitri 1447 H" maxlength="255">
                    <p class="form-hint">Nama hari libur atau kegiatan sekolah.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalEditEntry">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Hapus Seluruh Tahun
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDeleteYear">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3 class="modal-title">Hapus Kalender {{ $currentYear }}</h3>
            <button class="modal-close" data-close-modal="modalDeleteYear">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="delete-body">
                <div class="delete-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </div>
                <p style="font-weight:700;font-size:1rem;margin:0 0 8px;">Hapus semua data kalender tahun {{ $currentYear }}?</p>
                <p style="font-size:0.85rem;color:var(--text-muted);margin:0;">
                    Seluruh <strong>{{ $yearSummary->sum() }} entri</strong> akan dihapus permanen dan tidak bisa dikembalikan.
                </p>
                <div class="sc-del-year-card">
                    <div class="sc-del-year-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <p style="font-weight:700;margin:0;">Tahun {{ $currentYear }}</p>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:2px 0 0;">
                            {{ $yearSummary->get('aktif', 0) }} hari sekolah &middot; {{ $yearSummary->get('libur', 0) }} hari libur
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDeleteYear">Batal</button>
            <form action="{{ route('school-calendar.destroy-year') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="year" value="{{ $currentYear }}">
                <button type="submit" class="btn btn--danger">Ya, Hapus Semua</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/School_Calendar/main.js')) !!}</script>
@endpush
