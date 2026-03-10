@extends('layouts.app')

@section('title', 'Jaringan WiFi')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Sistem</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Jaringan WiFi</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Wifi_Networks/style.css')) !!}</style>
@endpush

@section('content')
<div class="page-wrap" style="display:flex;flex-direction:column;gap:20px;">

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
        <button type="button" class="btn btn--primary" data-open-modal="modalTambahWifi">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Jaringan
        </button>
    </div>

    {{-- ── Toolbar ─────────────────────────────────────── --}}
    <form method="GET" action="{{ route('wifi-networks.index') }}" class="dk-toolbar">
        <div class="dk-search">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Cari SSID, BSSID, IP..."
                   value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn--primary btn--sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Cari
        </button>
        @if(request('search'))
        <a href="{{ route('wifi-networks.index') }}" class="btn btn--ghost btn--sm">Reset</a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────── --}}
    <div class="data-card">
        <div class="table-scroll">
        <table class="data-table" style="min-width:580px;">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>SSID</th>
                    <th>BSSID</th>
                    <th>IP Address</th>
                    <th>Ditambahkan</th>
                    <th style="width:60px;text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($networks as $i => $network)
                <tr>
                    <td style="color:var(--text-muted);">{{ $networks->firstItem() + $i }}</td>

                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="wn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                                    <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                                    <line x1="12" y1="20" x2="12.01" y2="20"/>
                                </svg>
                            </div>
                            <span class="wn-ssid">{{ $network->ssid }}</span>
                        </div>
                    </td>

                    <td><span class="wn-bssid">{{ $network->bssid }}</span></td>

                    <td>
                        @if($network->ip_address)
                            <span class="wn-ip">{{ $network->ip_address }}</span>
                        @else
                            <span class="wn-ip--none">—</span>
                        @endif
                    </td>

                    <td style="color:var(--text-muted);font-size:0.83rem;white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($network->created_at)->locale('id')->isoFormat('D MMM YYYY') }}
                    </td>

                    <td style="text-align:center;">
                        <div class="dk-action">
                            <button type="button" class="wn-action__btn dk-action__btn" aria-label="Aksi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                                </svg>
                            </button>
                            <div class="wn-dropdown dk-dropdown">
                                <button type="button" class="dk-dropdown__item"
                                        data-action="edit"
                                        data-id="{{ $network->id }}"
                                        data-ssid="{{ $network->ssid }}"
                                        data-bssid="{{ $network->bssid }}"
                                        data-ip="{{ $network->ip_address }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </button>
                                <div class="dk-dropdown__separator"></div>
                                <button type="button" class="dk-dropdown__item dk-dropdown__item--danger"
                                        data-action="delete"
                                        data-id="{{ $network->id }}"
                                        data-ssid="{{ $network->ssid }}"
                                        data-bssid="{{ $network->bssid }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
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
                    <td colspan="6">
                        <div class="data-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                                <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                                <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                                <line x1="12" y1="20" x2="12.01" y2="20"/>
                            </svg>
                            <p>Belum ada jaringan WiFi terdaftar.</p>
                            @if(request('search'))
                                <a href="{{ route('wifi-networks.index') }}" class="btn btn--ghost btn--sm" style="margin-top:8px;">Hapus Filter</a>
                            @else
                                <button type="button" class="btn btn--primary btn--sm" data-open-modal="modalTambahWifi" style="margin-top:8px;">Tambah Jaringan Pertama</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- end table-scroll --}}

        {{-- Pagination --}}
        @if($networks->hasPages())
        <div class="data-pagination">
            {{ $networks->onEachSide(1)->links('vendor.pagination.prescientia') }}
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Tambah Jaringan WiFi
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalTambahWifi">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px;">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Jaringan WiFi
            </h3>
            <button class="modal-close" data-close-modal="modalTambahWifi">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('wifi-networks.store') }}" method="POST" data-loading>
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">SSID <span class="req">*</span></label>
                    <input type="text" name="ssid" class="form-control"
                           placeholder="Nama jaringan WiFi"
                           value="{{ old('ssid') }}" required maxlength="100">
                    <p class="form-hint">Nama yang muncul saat scan WiFi.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">BSSID <span class="req">*</span></label>
                    <input type="text" name="bssid" class="form-control"
                           placeholder="Contoh: AA:BB:CC:DD:EE:FF"
                           value="{{ old('bssid') }}" required maxlength="50"
                           style="font-family:monospace;letter-spacing:0.05em;">
                    <p class="form-hint">MAC address access point. Harus unik.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip_address" class="form-control"
                           placeholder="Contoh: 192.168.1.1"
                           value="{{ old('ip_address') }}" maxlength="45"
                           style="font-family:monospace;">
                    <p class="form-hint">Opsional. IP address access point.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalTambahWifi">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Edit Jaringan WiFi
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditWifi">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Jaringan WiFi
            </h3>
            <button class="modal-close" data-close-modal="modalEditWifi">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="editForm"
              action=""
              data-base-action="{{ route('wifi-networks.update', '__ID__') }}"
              method="POST" data-loading>
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">SSID <span class="req">*</span></label>
                    <input type="text" name="ssid" class="form-control"
                           required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">BSSID <span class="req">*</span></label>
                    <input type="text" name="bssid" class="form-control"
                           required maxlength="50"
                           style="font-family:monospace;letter-spacing:0.05em;">
                </div>
                <div class="form-group">
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip_address" class="form-control"
                           placeholder="Kosongkan jika tidak ada" maxlength="45"
                           style="font-family:monospace;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalEditWifi">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Hapus Jaringan WiFi
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDeleteWifi">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Hapus Jaringan WiFi</h3>
            <button class="modal-close" data-close-modal="modalDeleteWifi">
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
                <p style="font-weight:700;font-size:1rem;margin:0 0 4px;">Yakin menghapus jaringan ini?</p>
                <p style="font-size:0.85rem;color:var(--text-muted);margin:0 0 14px;">Semua log kehadiran WiFi yang terhubung ke jaringan ini juga akan ikut terhapus.</p>

                <div class="delete-wn-card">
                    <div class="wn-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                            <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                            <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                            <line x1="12" y1="20" x2="12.01" y2="20"/>
                        </svg>
                    </div>
                    <div style="text-align:left;">
                        <p id="deleteWifiName" style="font-weight:700;margin:0;font-size:0.95rem;"></p>
                        <p id="deleteWifiBssid" style="font-family:monospace;font-size:0.8rem;color:var(--text-muted);margin:2px 0 0;"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDeleteWifi">Batal</button>
            <form id="deleteForm"
                  action=""
                  data-base-action="{{ route('wifi-networks.destroy', '__ID__') }}"
                  method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Wifi_Networks/main.js')) !!}</script>
@endpush
