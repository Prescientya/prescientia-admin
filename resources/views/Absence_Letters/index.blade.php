@extends('layouts.app')

@section('title', 'Surat Izin / Sakit')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Kehadiran</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
    <span>Surat Izin / Sakit</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Absence_Letters/style.css')) !!}</style>
@endpush

@section('content')
<div class="al-page">

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
    <div class="al-header">
        <div>
            <p class="al-subtitle">
                <span class="al-badge al-badge--pending">{{ $counts['pending'] ?? 0 }} Menunggu</span>
                <span class="al-badge al-badge--approved">{{ $counts['approved'] ?? 0 }} Disetujui</span>
                <span class="al-badge al-badge--rejected">{{ $counts['rejected'] ?? 0 }} Ditolak</span>
            </p>
        </div>
    </div>

    {{-- ── Primary Type Tabs (Semua / Siswa / Guru) ────── --}}
    <div class="al-type-tabs">
        <a href="{{ route('absence-letters.index', ['type' => 'all', 'status' => $status]) }}"
           class="al-type-tab {{ $type === 'all' ? 'al-type-tab--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="9" rx="1"/>
                <rect x="14" y="3" width="7" height="5" rx="1"/>
                <rect x="14" y="12" width="7" height="9" rx="1"/>
                <rect x="3" y="16" width="7" height="5" rx="1"/>
            </svg>
            Semua
        </a>
        <a href="{{ route('absence-letters.index', ['type' => 'siswa', 'status' => $status]) }}"
           class="al-type-tab {{ $type === 'siswa' ? 'al-type-tab--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Siswa
        </a>
        <a href="{{ route('absence-letters.index', ['type' => 'guru', 'status' => $status]) }}"
           class="al-type-tab {{ $type === 'guru' ? 'al-type-tab--active' : '' }}">
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
    <form method="GET" action="{{ route('absence-letters.index') }}" class="al-filters">
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- Status tabs --}}
        <div class="al-tabs">
            @foreach([
                'pending'      => 'Menunggu',
                'approved'     => 'Disetujui',
                'rejected'     => 'Ditolak',
                'all'          => 'Semua',
            ] as $val => $label)
            <a href="{{ route('absence-letters.index', ['type' => $type, 'status' => $val]) }}"
               class="al-tab {{ $status === $val ? 'al-tab--active' : '' }}">
                {{ $label }}
                @if($val === 'pending' && ($counts['pending'] ?? 0) > 0)
                <span class="al-tab-count">{{ $counts['pending'] }}</span>
                @endif
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="al-filter-right">
            <div class="search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="search-icon">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIS/NIP, keterangan…"
                       class="form-input form-input--sm search-input">
            </div>
            <input type="hidden" name="status" value="{{ $status }}">
        </div>
    </form>

    {{-- ── Data Card ────────────────────────────────────── --}}
    <div class="data-card">
        <div class="data-card__toolbar">
            <span class="data-card__count">{{ $letters->total() }} surat ditemukan</span>
        </div>

        <div class="table-wrap">
            <table class="data-table" style="min-width:900px;">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Alasan</th>
                        <th>Keterangan</th>
                        <th style="width:120px;">Status</th>
                        <th style="width:120px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($letters as $i => $r)
                <tr id="row-{{ $r->id }}" class="{{ $r->status === 'pending' ? 'tr--pending' : '' }}">
                    <td class="td-no">{{ $letters->firstItem() + $i }}</td>

                    <td>
                        <div class="requester-name">{{ $r->requester_name ?? '—' }}</div>
                        <div class="requester-sub">{{ $r->requester_no ?? '—' }}</div>
                    </td>

                    <td>
                        <span class="type-badge type-badge--{{ $r->user_type === 'student' ? 'siswa' : 'guru' }}">
                            {{ $r->user_type === 'student' ? 'Siswa' : 'Guru' }}
                        </span>
                    </td>

                    <td class="td-muted">{{ $r->class_name ?? '—' }}</td>

                    <td class="td-muted" style="white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($r->date)->locale('id')->isoFormat('D MMM YYYY') }}
                    </td>

                    <td>
                        <span class="reason-badge reason-badge--{{ $r->reason }}">
                            {{ $r->reason === 'sakit' ? 'Sakit' : 'Izin' }}
                        </span>
                    </td>

                    <td>
                        <div class="td-desc" title="{{ $r->description }}">{{ $r->description ?? '—' }}</div>
                    </td>

                    <td>
                        <span class="status-badge status-badge--{{ $r->status }}">
                            {{ match($r->status) {
                                'pending'       => 'Menunggu',
                                'approved'      => 'Disetujui',
                                'rejected'      => 'Ditolak',
                                default         => $r->status
                            } }}
                        </span>
                    </td>

                    <td>
                        @if($r->status === 'pending')
                        <div class="action-btns">
                            <form method="POST" action="{{ route('absence-letters.approve', $r->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-action btn-action--approve"
                                        title="Setujui"
                                        onclick="return confirm('Setujui surat izin dari {{ $r->requester_name }}?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Setujui
                                </button>
                            </form>
                            <form method="POST" action="{{ route('absence-letters.reject', $r->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-action btn-action--reject"
                                        title="Tolak"
                                        onclick="return confirm('Tolak surat izin dari {{ $r->requester_name }}?')">
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
                            @if($r->approved_by_admin_at)
                                {{ \Carbon\Carbon::parse($r->approved_by_admin_at)->locale('id')->isoFormat('D MMM YY, H:mm') }}
                            @elseif($r->rejected_at)
                                {{ \Carbon\Carbon::parse($r->rejected_at)->locale('id')->isoFormat('D MMM YY, H:mm') }}
                            @else
                                —
                            @endif
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="td-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                             style="color:var(--text-muted)">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <p>Tidak ada surat izin ditemukan</p>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($letters->hasPages())
        <div class="pagination-wrap">
            {{ $letters->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
