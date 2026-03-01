@extends('layouts.app')

@section('title', 'Event / Acara')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Informasi</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Event / Acara</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Events/style.css')) !!}</style>
@endpush

@section('content')
<div class="ev-page">

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
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Page Header ─────────────────────────────────── --}}
    <div class="ev-header">
        <div>
            <p class="ev-subtitle">Total <strong>{{ $events->total() }}</strong> event terdaftar</p>
        </div>
        <a href="{{ route('events.create') }}" class="btn btn--primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Event
        </a>
    </div>

    {{-- ── Toolbar ─────────────────────────────────────── --}}
    <form method="GET" action="{{ route('events.index') }}" class="ev-toolbar">
        <div class="ev-search">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Cari judul event..."
                   value="{{ request('search') }}">
        </div>
        <select name="audience" class="ev-filter-select" onchange="this.form.submit()">
            <option value="">Semua Target</option>
            <option value="semua"  {{ request('audience') === 'semua'  ? 'selected' : '' }}>Semua Users</option>
            <option value="guru"   {{ request('audience') === 'guru'   ? 'selected' : '' }}>Guru</option>
            <option value="siswa"  {{ request('audience') === 'siswa'  ? 'selected' : '' }}>Siswa</option>
            <option value="kelas"  {{ request('audience') === 'kelas'  ? 'selected' : '' }}>Kelas Tertentu</option>
        </select>
        <select name="status" class="ev-filter-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="aktif"     {{ request('status') === 'aktif'     ? 'selected' : '' }}>Aktif</option>
            <option value="terjadwal" {{ request('status') === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
            <option value="selesai"   {{ request('status') === 'selesai'   ? 'selected' : '' }}>Selesai</option>
        </select>
        <button type="submit" class="btn btn--primary btn--sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Cari
        </button>
        @if(request('search') || request('audience') || request('status'))
        <a href="{{ route('events.index') }}" class="btn btn--ghost btn--sm">Reset</a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────── --}}
    <div class="data-card">
        <div class="table-scroll">
        <table class="data-table" style="min-width:780px;">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Judul</th>
                    <th style="width:120px;">Target</th>
                    <th style="width:120px;">Tanggal Rilis</th>
                    <th style="width:120px;">Tanggal Selesai</th>
                    <th style="width:100px;">Status</th>
                    <th style="width:60px;text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $i => $event)
                <tr>
                    <td style="color:var(--text-muted);">{{ $events->firstItem() + $i }}</td>
                    <td>
                        <div class="ev-title-cell">
                            <strong>{{ $event->title }}</strong>
                            @if($event->link)
                            <span class="ev-link-badge" title="{{ $event->link }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                                </svg>
                                Link
                            </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="ev-audience ev-audience--{{ $event->target_audience }}">
                            {{ $event->audience_label }}
                        </span>
                    </td>
                    <td>{{ $event->release_date->format('d M Y') }}</td>
                    <td>{{ $event->end_date->format('d M Y') }}</td>
                    <td>
                        <span class="ev-status ev-status--{{ $event->status_color }}">
                            {{ $event->status_label }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div class="ev-action">
                            <button type="button" class="ev-action__btn" aria-label="Aksi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                                </svg>
                            </button>
                            <div class="ev-dropdown">
                                <button type="button" class="ev-dropdown__item"
                                        data-action="detail"
                                        data-id="{{ $event->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Detail
                                </button>
                                <a href="{{ route('events.edit', $event) }}" class="ev-dropdown__item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </a>
                                <div class="ev-dropdown__separator"></div>
                                <button type="button" class="ev-dropdown__item ev-dropdown__item--danger"
                                        data-action="delete"
                                        data-id="{{ $event->id }}"
                                        data-title="{{ $event->title }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="data-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <p>Belum ada event.</p>
                            @if(request('search') || request('audience') || request('status'))
                                <a href="{{ route('events.index') }}" class="btn btn--ghost btn--sm" style="margin-top:8px;">Hapus Filter</a>
                            @else
                                <a href="{{ route('events.create') }}" class="btn btn--primary btn--sm" style="margin-top:8px;">Tambah Event Pertama</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- end table-scroll --}}

        {{-- Pagination --}}
        @if($events->hasPages())
        <div class="data-pagination">
            {{ $events->onEachSide(1)->links('vendor.pagination.prescientia') }}
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Detail Event
     ══════════════════════════════════════════════════════════ --}}
@include('Events.detail')

{{-- ══════════════════════════════════════════════════════════
     MODAL: Hapus Event
     ══════════════════════════════════════════════════════════ --}}
@include('Events.delete')

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Events/main.js')) !!}</script>
@endpush
