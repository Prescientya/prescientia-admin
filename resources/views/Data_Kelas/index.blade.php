@extends('layouts.app')

@section('title', 'Data Kelas')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Data Master</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Data Kelas</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Data_Kelas/style.css')) !!}</style>
@endpush

@section('content')
<div class="dk-page">

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
    <div class="dk-header">
        <div>
            <h1 class="dk-title">Data Kelas</h1>
            <p class="dk-subtitle">Total <strong>{{ $classes->total() }}</strong> kelas terdaftar</p>
        </div>
        <button type="button" class="btn btn--primary" data-open-modal="modalTambahKelas">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Kelas
        </button>
    </div>

    {{-- ── Toolbar ─────────────────────────────────────── --}}
    <form method="GET" action="{{ route('kelas.index') }}" class="dk-toolbar">
        <div class="dk-search">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Cari jurusan atau tingkat..."
                   value="{{ request('search') }}">
        </div>
        <select name="tingkat" class="dk-filter-select" onchange="this.form.submit()">
            <option value="">Semua Tingkat</option>
            <option value="10" {{ request('tingkat') == '10' ? 'selected' : '' }}>Kelas 10</option>
            <option value="11" {{ request('tingkat') == '11' ? 'selected' : '' }}>Kelas 11</option>
            <option value="12" {{ request('tingkat') == '12' ? 'selected' : '' }}>Kelas 12</option>
        </select>
        <button type="submit" class="btn btn--primary btn--sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Cari
        </button>
        @if(request('search') || request('tingkat'))
        <a href="{{ route('kelas.index') }}" class="btn btn--ghost btn--sm">Reset</a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────── --}}
    <div class="data-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th style="width:90px;">Tingkat</th>
                    <th>Jurusan</th>
                    <th>Kelas Lengkap</th>
                    <th style="width:130px;">Jumlah Siswa</th>
                    <th style="width:60px;text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $i => $kelas)
                <tr>
                    <td style="color:var(--text-muted);">{{ $classes->firstItem() + $i }}</td>
                    <td>
                        <div class="kelas-tingkat">{{ $kelas->class }}</div>
                    </td>
                    <td>
                        <span class="kelas-major">{{ $kelas->major ?? '–' }}</span>
                    </td>
                    <td style="font-weight:600;">
                        Kelas {{ $kelas->class }}{{ $kelas->major ? ' – ' . $kelas->major : '' }}
                    </td>
                    <td>
                        <span class="kelas-count">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <strong>{{ $kelas->students_count }}</strong> siswa
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div class="dk-action">
                            <button type="button" class="dk-action__btn" aria-label="Aksi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                                </svg>
                            </button>
                            <div class="dk-dropdown">
                                <button type="button" class="dk-dropdown__item"
                                        data-action="edit"
                                        data-id="{{ $kelas->id }}"
                                        data-kelas="{{ $kelas->class }}"
                                        data-major="{{ $kelas->major }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit Kelas
                                </button>
                                <div class="dk-dropdown__separator"></div>
                                <button type="button" class="dk-dropdown__item dk-dropdown__item--danger"
                                        data-action="delete"
                                        data-id="{{ $kelas->id }}"
                                        data-kelas="{{ $kelas->class }}"
                                        data-major="{{ $kelas->major }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    Hapus Kelas
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
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                            </svg>
                            <p>Belum ada data kelas.</p>
                            @if(request('search') || request('tingkat'))
                                <a href="{{ route('kelas.index') }}" class="btn btn--ghost btn--sm" style="margin-top:8px;">Hapus Filter</a>
                            @else
                                <button type="button" class="btn btn--primary btn--sm" data-open-modal="modalTambahKelas" style="margin-top:8px;">Tambah Kelas Pertama</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($classes->hasPages())
        <div class="data-pagination">
            {{ $classes->onEachSide(1)->links('vendor.pagination.prescientia') }}
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Tambah Kelas
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalTambahKelas">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px;">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Kelas
            </h3>
            <button class="modal-close" data-close-modal="modalTambahKelas">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('kelas.store') }}" method="POST" data-loading>
            @csrf
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Tingkat <span class="req">*</span></label>
                        <select name="class" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <option value="10" {{ old('class') == '10' ? 'selected' : '' }}>10</option>
                            <option value="11" {{ old('class') == '11' ? 'selected' : '' }}>11</option>
                            <option value="12" {{ old('class') == '12' ? 'selected' : '' }}>12</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jurusan <span class="req">*</span></label>
                        <input type="text" name="major" class="form-control"
                               placeholder="Contoh: RPL, AKL 1"
                               value="{{ old('major') }}" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalTambahKelas">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Edit Kelas
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditKelas">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Kelas
            </h3>
            <button class="modal-close" data-close-modal="modalEditKelas">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="editForm"
              action=""
              data-base-action="{{ route('kelas.update', '__ID__') }}"
              method="POST" data-loading>
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Tingkat <span class="req">*</span></label>
                        <select name="class" class="form-control" required>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jurusan <span class="req">*</span></label>
                        <input type="text" name="major" class="form-control"
                               placeholder="Contoh: RPL, AKL 1" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalEditKelas">Batal</button>
                <button type="submit" class="btn btn--primary">
                    <span class="spinner"></span>
                    <span class="btn-text">Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: Hapus Kelas
     ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalDeleteKelas">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Hapus Kelas</h3>
            <button class="modal-close" data-close-modal="modalDeleteKelas">
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
                <p style="font-weight:700;font-size:1rem;margin:0 0 4px;">Yakin menghapus kelas ini?</p>
                <p style="font-size:0.85rem;color:var(--text-muted);margin:0 0 14px;">Data yang dihapus tidak dapat dikembalikan.</p>

                <div class="delete-kelas-card">
                    <div class="delete-kelas-initial" id="deleteKelasInitial">–</div>
                    <div style="text-align:left;">
                        <p id="deleteKelasName" style="font-weight:700;margin:0;font-size:0.95rem;"></p>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:2px 0 0;">Kelas ini akan dihapus permanen</p>
                    </div>
                </div>

                <p class="delete-warn">
                    Kelas yang masih memiliki siswa <strong>tidak dapat dihapus</strong>. Pindahkan siswa terlebih dahulu.
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDeleteKelas">Batal</button>
            <form id="deleteForm"
                  action=""
                  data-base-action="{{ route('kelas.destroy', '__ID__') }}"
                  method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger">
                    Ya, Hapus Kelas
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Data_Kelas/main.js')) !!}</script>
@endpush
