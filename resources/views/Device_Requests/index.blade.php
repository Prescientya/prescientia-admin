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
            <h1 class="dr-title">Permintaan Ganti Device</h1>
            <p class="dr-subtitle">
                <span class="dr-badge dr-badge--pending">{{ $counts['pending'] ?? 0 }} Menunggu</span>
                <span class="dr-badge dr-badge--approved">{{ $counts['approved'] ?? 0 }} Disetujui</span>
                <span class="dr-badge dr-badge--rejected">{{ $counts['rejected'] ?? 0 }} Ditolak</span>
            </p>
        </div>
    </div>

    {{-- ── Filter Bar ───────────────────────────────────── --}}
    <form method="GET" action="{{ route('device-requests.index') }}" class="dr-filters">

        {{-- Status tabs --}}
        <div class="dr-tabs">
            @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $label)
            <a href="{{ route('device-requests.index', array_merge(request()->except('status','page'), ['status' => $val])) }}"
               class="dr-tab {{ $status === $val ? 'dr-tab--active' : '' }}">
                {{ $label }}
                @if($val === 'pending' && ($counts['pending'] ?? 0) > 0)
                <span class="dr-tab-count">{{ $counts['pending'] }}</span>
                @endif
            </a>
            @endforeach
        </div>

        {{-- Right-side filters --}}
        <div class="dr-filter-right">
            <select name="type" class="form-input form-input--sm" onchange="this.form.submit()">
                <option value="all"  {{ $type === 'all'  ? 'selected' : '' }}>Semua Tipe</option>
                <option value="siswa"{{ $type === 'siswa'? 'selected' : '' }}>Siswa</option>
                <option value="guru" {{ $type === 'guru' ? 'selected' : '' }}>Guru</option>
            </select>
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
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama</th>
                        <th style="width:80px;">Tipe</th>
                        <th>NIS / NIP</th>
                        <th>Device ID Lama</th>
                        <th>Device ID Baru</th>
                        <th>Diajukan Oleh</th>
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

                    <td>
                        <span class="type-badge type-badge--{{ $r->requester_type }}">
                            {{ $r->requester_type === 'siswa' ? 'Siswa' : 'Guru' }}
                        </span>
                    </td>

                    <td class="td-mono">{{ $r->requester_no ?? '—' }}</td>

                    <td>
                        <div class="device-id">{{ $r->device_id_old }}</div>
                    </td>

                    <td>
                        <div class="device-id device-id--new">{{ $r->device_id_new }}</div>
                    </td>

                    <td class="td-muted">{{ $r->submitted_by ?? '—' }}</td>

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
                                <button type="submit" class="btn-action btn-action--approve"
                                        title="Setujui">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Setujui
                                </button>
                            </form>
                            <form method="POST" action="{{ route('device-requests.reject', $r->id) }}" class="form-reject">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-action btn-action--reject"
                                        title="Tolak">
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
                    <td colspan="10" class="td-empty">
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

</div>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Device_Requests/main.js')) !!}</script>
@endpush
