@extends('layouts.app')

@section('title', 'Data Siswa')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Data Master</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Data Siswa</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Data_Siswa/style.css')) !!}</style>
@endpush

@section('content')
@php
    $todayJkt = now()->timezone('Asia/Jakarta');
    $isAfterPromotion = ($todayJkt->month > 7) || ($todayJkt->month === 7 && $todayJkt->day >= 19);
    $graduationFlagExists = file_exists(storage_path('app/graduates_deleted_' . $todayJkt->year . '.flag'));
    $showDeleteGraduatesBtn = $isAfterPromotion && !$graduationFlagExists;
@endphp
<div class="ds-page">

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
                        {{ session('import_success_count') }} siswa berhasil diimpor
                    </span>
                    @endif
                    <span class="irc irc--fail">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        {{ count(session('import_failed')) }} siswa gagal diimpor
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
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Alasan Gagal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('import_failed') as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><code>{{ $row['nis'] ?? '-' }}</code></td>
                            <td>{{ $row['nama'] ?? '-' }}</td>
                            <td>{{ $row['reason'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>
    @endif

    {{-- ── Page Header ─────────────────────────────────── --}}
    <div class="ds-header">
        <div>
            <p class="ds-subtitle">Total <strong>{{ $students->total() }}</strong> siswa terdaftar</p>
        </div>
        <div class="ds-header__actions">
            {{-- Nonaktifkan Siswa (Bulk) --}}
            <button class="btn btn--warning" data-open-modal="modalBulkDeactivate">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                </svg>
                Nonaktifkan
            </button>
            {{-- Aktifkan Semua (enabled only if inactiveCount > 0) --}}
            <button class="btn btn--success" data-open-modal="modalBulkActivate" {{ $inactiveCount < 1 ? 'disabled' : '' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Aktifkan Semua ({{ $inactiveCount }})
            </button>
            {{-- Import Excel --}}
            <button class="btn btn--secondary" data-open-modal="modalTambahExcel">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                Import Excel
            </button>
            {{-- Tambah Manual --}}
            <button class="btn btn--primary" data-open-modal="modalTambahManual">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Siswa
            </button>
            {{-- Hapus Siswa Lulus (visible from July 19 if not already done) --}}
            @if($showDeleteGraduatesBtn)
            <button type="button" class="btn btn--danger" data-open-modal="modalDeleteGraduates">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07"/>
                    <path d="M11 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h7"/>
                    <polyline points="16 17 21 12 16 7"/>
                </svg>
                Hapus Siswa Lulus
            </button>
            @endif
        </div>
    </div>

    {{-- ── Search & Filter ─────────────────────────────── --}}
    <div class="ds-toolbar">
        <form method="GET" action="{{ route('siswa.index') }}" class="ds-search-form">
            <div class="ds-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search"
                       placeholder="Cari nama, NIS, atau email..."
                       value="{{ request('search') }}">
            </div>
            <select name="class_id" class="ds-filter-select">
                <option value="">Semua Kelas</option>
                @foreach($classes as $kelas)
                    <option value="{{ $kelas->id }}" {{ request('class_id') == $kelas->id ? 'selected' : '' }}>
                        Kelas {{ $kelas->class }}{{ $kelas->major ? ' – ' . $kelas->major : '' }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="ds-filter-select">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn--primary btn--sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Cari
            </button>
            @if(request('search') || request('class_id') || request('status'))
            <a href="{{ route('siswa.index') }}" class="btn btn--ghost btn--sm">Reset</a>
            @endif
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────── --}}
    <div class="data-card">
        <div class="table-scroll">
            <table class="data-table" style="min-width:700px;">
                <thead>
                    <tr>
                        <th style="width:48px;">No</th>
                        <th>Nama Siswa</th>
                        <th style="width:120px;">NIS</th>
                        <th>Email</th>
                        <th style="width:80px;">Kelas</th>
                        <th style="width:110px;">Jurusan</th>
                        <th style="width:90px;">Status</th>
                        <th style="width:56px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $i => $siswa)
                    <tr>
                        <td style="color:var(--text-muted);font-size:0.82rem;">
                            {{ $students->firstItem() + $i }}
                        </td>
                        <td>
                            <span class="ds-student-name">{{ $siswa->name }}</span>
                        </td>
                        <td style="font-family:monospace;font-size:0.85rem;letter-spacing:0.03em;">
                            {{ $siswa->nis }}
                        </td>
                        <td style="color:var(--text-secondary);font-size:0.84rem;">
                            {{ optional($siswa->user)->email ?? '–' }}
                        </td>
                        <td style="text-align:center;font-weight:600;">
                            {{ $siswa->schoolClass?->class ?? '–' }}
                        </td>
                        <td>
                            @if($siswa->schoolClass?->major)
                                <span style="font-size:0.82rem;padding:2px 8px;border-radius:5px;background:var(--accent-light);color:var(--accent);font-weight:600;">
                                    {{ $siswa->schoolClass->major }}
                                </span>
                            @else
                                <span style="color:var(--text-muted);">–</span>
                            @endif
                        </td>
                        <td>
                            @if(optional($siswa->user)->is_active)
                                <span class="badge badge--active">Aktif</span>
                            @else
                                <span class="badge badge--inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div class="ds-action">
                                <button class="ds-action__btn" aria-label="Aksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                                    </svg>
                                </button>
                                <div class="ds-dropdown">
                                    {{-- Detail --}}
                                    <a href="#" class="ds-dropdown__item"
                                       data-action="detail" data-id="{{ $siswa->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                        </svg>
                                        Detail Siswa
                                    </a>
                                    {{-- Edit --}}
                                    <a href="{{ route('siswa.edit', $siswa->id) }}" class="ds-dropdown__item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        Edit Siswa
                                    </a>
                                    <div class="ds-dropdown__separator"></div>
                                    {{-- Hapus --}}
                                    <button type="button" class="ds-dropdown__item ds-dropdown__item--danger"
                                            data-action="delete"
                                            data-id="{{ $siswa->id }}"
                                            data-name="{{ $siswa->name }}"
                                            data-nis="{{ $siswa->nis }}"
                                            data-email="{{ optional($siswa->user)->email }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        Hapus Siswa
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
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <p>Tidak ada data siswa ditemukan.</p>
                                @if(request('search') || request('class_id'))
                                    <a href="{{ route('siswa.index') }}" class="btn btn--ghost btn--sm" style="margin-top:10px;">Reset filter</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
        <div class="data-pagination">
            {{ $students->onEachSide(1)->links('vendor.pagination.prescientia') }}
        </div>
        @endif
    </div>

</div>

{{-- ── Modals ─────────────────────────────────────────── --}}
@include('Data_Siswa.tambah_siswa')
@include('Data_Siswa.tambah_siswa_excel')
@include('Data_Siswa.detail')
@include('Data_Siswa.delete')
@include('Data_Siswa.bulk_status')

{{-- ═══════════════════════════════════════════════════════════
     MODAL: HAPUS SISWA LULUS
     ═══════════════════════════════════════════════════════════ --}}
@if($showDeleteGraduatesBtn)
<div class="modal-overlay" id="modalDeleteGraduates">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h2 class="modal-title modal-title--danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4h6v2"/>
                </svg>
                Hapus Siswa Lulus
            </h2>
            <button type="button" class="modal-close-btn modal-close" aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus semua siswa yang sudah lulus di angkatan
                <strong>{{ $todayJkt->year - 1 }}/{{ $todayJkt->year }}</strong>?</p>
            <p class="text-danger" style="margin-top:.5rem;font-size:.875rem;">
                Tindakan ini <strong>tidak bisa dibatalkan</strong>. Semua data siswa lulus beserta riwayat absensinya akan terhapus permanen.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost modal-close">Batal</button>
            <form method="POST" action="{{ route('siswa.delete-graduates') }}" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14H6L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/>
                        <path d="M9 6V4h6v2"/>
                    </svg>
                    Ya, Hapus Siswa Lulus
                </button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Data_Siswa/main.js')) !!}</script>
@endpush
