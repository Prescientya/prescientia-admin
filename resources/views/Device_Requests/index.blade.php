@extends('layouts.app')

@section('title', 'Permintaan Ganti Device')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Sistem</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Permintaan Ganti Device</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Device_Requests/style.css')) !!}</style>
@endpush

@section('content')
<div class="dr-page">

    {{-- ── Flash Messages ─────────────────────────────── --}}
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

    {{-- ── Page Header ─────────────────────────────────── --}}
    <div class="dr-header">
        <div>
            <p class="dr-subtitle">
                <span class="dr-badge dr-badge--pending">{{ $counts['pending'] ?? 0 }} Menunggu</span>
                <span class="dr-badge dr-badge--approved">{{ $counts['approved'] ?? 0 }} Disetujui</span>
                <span class="dr-badge dr-badge--rejected">{{ $counts['rejected'] ?? 0 }} Ditolak</span>
            </p>
        </div>
    </div>

    {{-- ── Primary Type Tabs (Siswa / Guru) ────────────── --}}
    <div class="dr-type-tabs">
        <a href="{{ route('device-requests.index', ['type' => 'siswa', 'status' => $status]) }}"
           class="dr-type-tab {{ $type === 'siswa' ? 'dr-type-tab--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Siswa
        </a>
        <a href="{{ route('device-requests.index', ['type' => 'guru', 'status' => $status]) }}"
           class="dr-type-tab {{ $type === 'guru' ? 'dr-type-tab--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            Guru
        </a>
    </div>

    {{-- ── Secondary Status Tabs + Search ─────────────── --}}
    <form method="GET" action="{{ route('device-requests.index') }}" class="dr-filters">
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- Status tabs --}}
        <div class="dr-tabs">
            @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $label)
            <a href="{{ route('device-requests.index', ['type' => $type, 'status' => $val]) }}"
               class="dr-tab {{ $status === $val ? 'dr-tab--active' : '' }}">
                {{ $label }}
                @if($val === 'pending' && ($counts['pending'] ?? 0) > 0)
                <span class="dr-tab-count">{{ $counts['pending'] }}</span>
                @endif
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="dr-filter-right">
            <div class="search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIS, device ID…"
                       class="form-input form-input--sm search-input">
            </div>
            <input type="hidden" name="status" value="{{ $status }}">
        </div>
    </form>

    {{-- ── Data Card ────────────────────────────────────── --}}
    <div class="data-card">
        <div class="data-card__toolbar">
            <span class="data-card__count">{{ $requests->total() }} permintaan ditemukan</span>
        </div>

        <div class="table-wrap">
            <table class="data-table" style="min-width:820px;">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama</th>
                        <th>NIS / NIP</th>
                        <th>Tanggal</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:100px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($requests as $i => $r)
                <tr id="row-{{ $r->id }}" class="{{ $r->status === 'pending' ? 'tr--pending' : '' }}">
                    <td class="td-no">{{ $requests->firstItem() + $i }}</td>

                    <td>
                        <div class="requester-name">{{ $r->requester_name ?? '—' }}</div>
                        <div class="requester-email">{{ $r->email }}</div>
                    </td>

                    <td class="td-mono">{{ $r->requester_no ?? '—' }}</td>

                    <td class="td-muted" style="white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($r->created_at)->locale('id')->isoFormat('D MMM YYYY') }}
                        <br>
                        <small>{{ \Carbon\Carbon::parse($r->created_at)->format('H:i') }}</small>
                    </td>

                    <td>
                        <span class="status-badge status-badge--{{ $r->status }}">
                            {{ match($r->status) { 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => $r->status } }}
                        </span>
                    </td>

                    <td>
                        @if($r->status === 'pending')
                        <div class="action-btns">
                            <form method="POST" action="{{ route('device-requests.approve', $r->id) }}" class="form-approve">
                                @csrf @method('PATCH')
                                <button type="button" class="btn-action btn-action--approve btn-confirm"
                                        title="Setujui" data-action="approve" 
                                        data-old="{{ $r->device_id_old }}" data-new="{{ $r->device_id_new }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Setujui
                                </button>
                            </form>
                            <form method="POST" action="{{ route('device-requests.reject', $r->id) }}" class="form-reject">
                                @csrf @method('PATCH')
                                <button type="button" class="btn-action btn-action--reject btn-confirm"
                                        title="Tolak" data-action="reject" 
                                        data-old="{{ $r->device_id_old }}" data-new="{{ $r->device_id_new }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                    Tolak
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="td-muted" style="font-size:0.78rem;">
                            {{ \Carbon\Carbon::parse($r->updated_at)->locale('id')->isoFormat('D MMM YY, H:mm') }}
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="td-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                             style="color:var(--text-muted)">
                            <rect x="5" y="2" width="14" height="20" rx="2"/>
                            <line x1="9" y1="9" x2="15" y2="9"/>
                            <line x1="9" y1="13" x2="15" y2="13"/>
                            <line x1="9" y1="17" x2="11" y2="17"/>
                        </svg>
                        <p>Tidak ada permintaan ditemukan</p>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($requests->hasPages())
        <div class="pagination-wrap">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Konfirmasi --}}
    <style>
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal {
            background-color: var(--card-bg, #1a1e28);
            border: 1px solid var(--card-border, rgba(255, 255, 255, 0.1));
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .modal__header {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--card-border, rgba(255, 255, 255, 0.1));
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal__header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-color, #e0e6ed);
        }
        .btn-close {
            background: none; border: none;
            color: var(--text-muted, #94a3b8);
            cursor: pointer;
            padding: 0;
        }
        .btn-close:hover { color: var(--text-color, #e0e6ed); }
        .modal__body {
            padding: 1.2rem;
        }
        .modal__footer {
            padding: 1rem 1.2rem;
            border-top: 1px solid var(--card-border, rgba(255, 255, 255, 0.1));
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
    </style>
    <div id="confirmModal" class="modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal__header">
                <h3 id="modalTitle">Konfirmasi Permintaan</h3>
                <button type="button" class="btn-close js-modal-close">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal__body">
                <p id="modalMessage" style="margin-bottom: 1rem; color: var(--text-color);">Apakah Anda yakin?</p>
                <div style="background-color: var(--sidebar-bg, rgba(0,0,0,0.1)); padding: 1rem; border-radius: 6px; border: 1px solid var(--card-border, rgba(255, 255, 255, 0.1));">
                    <div style="margin-bottom: 0.5rem;">
                        <span style="display:block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Device ID Lama</span>
                        <code id="modalOldId" style="color: var(--text-color); word-break: break-all;">-</code>
                    </div>
                    <div>
                        <span style="display:block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Device ID Baru</span>
                        <code id="modalNewId" style="color: var(--success-color, #10b981); word-break: break-all;">-</code>
                    </div>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline js-modal-close">Batal</button>
                <button type="button" class="btn btn--primary" id="btnConfirmAction">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Device_Requests/main.js')) !!}</script>
@endpush
