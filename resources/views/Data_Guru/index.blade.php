@extends('layouts.app')

@section('title', 'Data Guru')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Data Master</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Data Guru</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Data_Guru/style.css')) !!}</style>
@endpush

@section('content')
<div class="dg-page">

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

    @if(session('warning'))
    <div class="alert alert--warning">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <div style="white-space:pre-line">{{ session('warning') }}</div>
    </div>
    @endif

    {{-- ── Page Header ─────────────────────────────────── --}}
    <div class="dg-header">
        <div>
            <h1 class="dg-title">Data Guru</h1>
            <p class="dg-subtitle">Total <strong>{{ $teachers->total() }}</strong> guru terdaftar</p>
        </div>
        <div class="dg-header__actions">
            <button class="btn btn--secondary" data-open-modal="modalTambahExcel">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                Import Excel
            </button>
            <button class="btn btn--primary" data-open-modal="modalTambahManual">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Guru
            </button>
        </div>
    </div>

    {{-- ── Search & Filter ─────────────────────────────── --}}
    <div class="dg-toolbar">
        <form method="GET" action="{{ route('guru.index') }}" class="dg-search-form">
            <div class="dg-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search"
                       placeholder="Cari nama, NIP, atau email..."
                       value="{{ request('search') }}">
            </div>
            <select name="subject_id" class="dg-filter-select">
                <option value="">Semua Mapel</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn--primary btn--sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Cari
            </button>
            @if(request('search') || request('subject_id'))
            <a href="{{ route('guru.index') }}" class="btn btn--ghost btn--sm">Reset</a>
            @endif
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────── --}}
    <div class="data-card">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:48px;">No</th>
                        <th>Nama Guru</th>
                        <th style="width:150px;">NIP</th>
                        <th>Email</th>
                        <th>Mata Pelajaran</th>
                        <th style="width:90px;">Status</th>
                        <th style="width:56px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $i => $guru)
                    <tr>
                        <td style="color:var(--text-muted);font-size:0.82rem;">
                            {{ $teachers->firstItem() + $i }}
                        </td>
                        <td>
                            <span class="dg-teacher-name">{{ $guru->name }}</span>
                        </td>
                        <td style="font-family:monospace;font-size:0.84rem;letter-spacing:0.03em;">
                            {{ $guru->nip }}
                        </td>
                        <td style="color:var(--text-secondary);font-size:0.84rem;">
                            {{ optional($guru->user)->email ?? '–' }}
                        </td>
                        <td>
                            @if($guru->subjects->isNotEmpty())
                                <div class="subject-chips">
                                    @foreach($guru->subjects as $subject)
                                        <span class="subject-chip">{{ $subject->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:var(--text-muted);font-size:0.82rem;">–</span>
                            @endif
                        </td>
                        <td>
                            @if(optional($guru->user)->is_active)
                                <span class="badge badge--active">Aktif</span>
                            @else
                                <span class="badge badge--inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div class="dg-action">
                                <button class="dg-action__btn" aria-label="Aksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                                    </svg>
                                </button>
                                <div class="dg-dropdown">
                                    {{-- Detail --}}
                                    <a href="#" class="dg-dropdown__item"
                                       data-action="detail" data-id="{{ $guru->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                        </svg>
                                        Detail Guru
                                    </a>
                                    {{-- Edit --}}
                                    <a href="{{ route('guru.edit', $guru->id) }}" class="dg-dropdown__item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        Edit Guru
                                    </a>
                                    <div class="dg-dropdown__separator"></div>
                                    {{-- Hapus --}}
                                    <button type="button" class="dg-dropdown__item dg-dropdown__item--danger"
                                            data-action="delete"
                                            data-id="{{ $guru->id }}"
                                            data-name="{{ $guru->name }}"
                                            data-nip="{{ $guru->nip }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        Hapus Guru
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="data-empty">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                    <polyline points="16 11 18 13 22 9"/>
                                </svg>
                                <p>Tidak ada data guru ditemukan.</p>
                                @if(request('search') || request('subject_id'))
                                    <a href="{{ route('guru.index') }}" class="btn btn--ghost btn--sm" style="margin-top:10px;">Reset filter</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($teachers->hasPages())
        <div class="data-pagination">
            {{ $teachers->onEachSide(1)->links('vendor.pagination.prescientia') }}
        </div>
        @endif
    </div>

</div>

{{-- ── Modals ─────────────────────────────────────────── --}}
@include('Data_Guru.tambah_guru')
@include('Data_Guru.tambah_guru_excel')
@include('Data_Guru.detail')
@include('Data_Guru.delete')

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Data_Guru/main.js')) !!}</script>
@endpush
