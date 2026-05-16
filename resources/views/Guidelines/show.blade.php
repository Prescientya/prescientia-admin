@extends('layouts.app')

@section('title', 'Kelola Panduan ' . ucfirst($guideline->user_type))

@section('breadcrumb')
    <span style="color:var(--text-muted);">Informasi</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <a href="{{ route('guidelines.index') }}" style="color:var(--text-muted);text-decoration:none;">Panduan Aplikasi</a>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Panduan {{ ucfirst($guideline->user_type) }}</span>
@endsection

@push('styles')
<style>{!! file_get_contents(resource_path('views/Guidelines/style.css')) !!}</style>
@endpush

@section('content')
<div class="gl-page">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="gl-show-header">
        <div>
            <a href="{{ route('guidelines.index') }}" class="gl-show-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Kembali ke daftar panduan
            </a>
            <div class="gl-show-title">{{ $guideline->title }}</div>
            @if($guideline->subtitle)
            <div class="gl-show-subtitle">{{ $guideline->subtitle }}</div>
            @endif
        </div>
        <div class="gl-show-actions">
            <span class="gl-pub-badge {{ $guideline->is_published ? 'gl-pub-badge--on' : 'gl-pub-badge--off' }}">
                <span class="gl-pub-dot"></span>
                {{ $guideline->is_published ? 'Dipublikasikan' : 'Disembunyikan' }}
            </span>
            <form action="{{ route('guidelines.toggle-publish', $guideline) }}" method="POST" style="margin:0;">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn--ghost btn--sm">
                    {{ $guideline->is_published ? 'Sembunyikan' : 'Publikasikan' }}
                </button>
            </form>
            <a href="{{ route('panduan.show', $guideline->user_type) }}" target="_blank" class="btn btn--ghost btn--sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Pratinjau
            </a>
        </div>
    </div>

    {{-- Edit Page Info --}}
    <div class="gl-info-card">
        <div class="gl-info-card__header" id="infoToggle">
            <span class="gl-info-card__header-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:-3px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Informasi Halaman
            </span>
            <svg id="infoChevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition:transform .2s;"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="gl-info-card__body" id="infoBody">
            <form action="{{ route('guidelines.update', $guideline) }}" method="POST">
                @csrf @method('PUT')
                <div class="gl-form-row">
                    <div class="gl-form-field">
                        <label for="pageTitle">Judul Halaman <span class="req-star">*</span></label>
                        <input type="text" id="pageTitle" name="title" value="{{ old('title', $guideline->title) }}" required maxlength="20" placeholder="cth. Panduan Aplikasi Siswa">
                        @error('title')<span style="font-size:.78rem;color:#dc2626;">{{ $message }}</span>@enderror
                    </div>
                    <div class="gl-form-field">
                        <label for="pageSubtitle">Subjudul / Deskripsi Singkat</label>
                        <textarea id="pageSubtitle" name="subtitle" rows="2" maxlength="1000" placeholder="Deskripsi singkat halaman panduan ini...">{{ old('subtitle', $guideline->subtitle) }}</textarea>
                    </div>
                    <div>
                        <button type="submit" class="btn btn--primary btn--sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Sections --}}
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--text-primary);">
                Seksi Panduan
                <span style="font-size:.8rem;font-weight:400;color:var(--text-muted);margin-left:6px;">({{ $guideline->sections->count() }} seksi)</span>
            </h3>
            <button type="button" class="btn btn--primary btn--sm" id="btnAddSection">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Seksi
            </button>
        </div>

        @if($guideline->sections->isEmpty())
        <div class="gl-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            <p>Belum ada seksi panduan. Mulai tambahkan seksi pertama.</p>
            <button type="button" class="btn btn--primary btn--sm" id="btnAddSectionEmpty">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Seksi Pertama
            </button>
        </div>
        @else
        <div class="gl-section-list">
            @foreach($guideline->sections as $section)
            <div class="gl-section-card">
                <div class="gl-section-card__header">
                    <div class="gl-section-order">{{ $loop->iteration }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="gl-section-card__title">{{ $section->title }}</div>
                        @if($section->description)
                        <div class="gl-section-card__desc">{{ $section->description }}</div>
                        @endif
                    </div>
                    <div class="gl-section-card__actions">
                        <button type="button" class="gl-icon-btn btn-edit-section"
                                data-id="{{ $section->id }}"
                                data-title="{{ $section->title }}"
                                data-description="{{ $section->description }}"
                                data-order="{{ $section->order }}"
                                title="Edit seksi">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button type="button" class="gl-icon-btn gl-icon-btn--danger btn-del-section"
                                data-id="{{ $section->id }}"
                                data-title="{{ $section->title }}"
                                data-url="{{ route('guidelines.sections.destroy', $section) }}"
                                title="Hapus seksi">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="gl-section-card__body">
                    {{-- Items --}}
                    @if($section->items->isNotEmpty())
                    <div class="gl-item-list">
                        @foreach($section->items as $item)
                        <div class="gl-item">
                            <div class="gl-item__order">{{ $loop->iteration }}</div>
                            <div class="gl-item__content">
                                <div class="gl-item__title">{{ $item->title }}</div>
                                @if($item->content)
                                <div class="gl-item__text">{{ Str::limit($item->content, 200) }}</div>
                                @endif
                                @if($item->image_path)
                                <div class="gl-item__image-preview">
                                    <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}">
                                </div>
                                @endif
                            </div>
                            <div class="gl-item__actions">
                                <button type="button" class="gl-icon-btn btn-edit-item"
                                        data-id="{{ $item->id }}"
                                        data-title="{{ $item->title }}"
                                        data-content="{{ $item->content }}"
                                        data-order="{{ $item->order }}"
                                        data-image="{{ $item->image_path ? Storage::url($item->image_path) : '' }}"
                                        data-url="{{ route('guidelines.items.update', $item) }}"
                                        title="Edit langkah">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button type="button" class="gl-icon-btn gl-icon-btn--danger btn-del-item"
                                        data-id="{{ $item->id }}"
                                        data-title="{{ $item->title }}"
                                        data-url="{{ route('guidelines.items.destroy', $item) }}"
                                        title="Hapus langkah">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Add item button --}}
                    <button type="button" class="gl-add-item-btn btn-add-item"
                            data-section-id="{{ $section->id }}"
                            data-section-title="{{ $section->title }}"
                            data-url="{{ route('guidelines.items.store', $section) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Langkah ke Seksi Ini
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: Tambah / Edit Seksi
     ══════════════════════════════════════════════════════ --}}
<div class="gl-modal-overlay" id="modalSection">
    <div class="gl-modal">
        <div class="gl-modal__header">
            <span class="gl-modal__title" id="modalSectionTitle">Tambah Seksi</span>
            <button type="button" class="gl-modal__close" data-close-modal="modalSection">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="formSection" method="POST" action="">
            @csrf
            <span id="formSectionMethod"></span>
            <div class="gl-modal__body">
                <div class="gl-form-field">
                    <label for="secTitle">Judul Seksi <span class="req-star">*</span></label>
                    <input type="text" id="secTitle" name="title" required maxlength="20" placeholder="cth. Cara Absensi Masuk">
                </div>
                <div class="gl-form-field">
                    <label for="secDesc">Deskripsi Seksi</label>
                    <textarea id="secDesc" name="description" rows="3" maxlength="2000" placeholder="Penjelasan singkat tentang seksi ini..."></textarea>
                </div>
                <div class="gl-form-field">
                    <label for="secOrder">Nomor Urutan</label>
                    <input type="number" id="secOrder" name="order" min="0" max="999" placeholder="0" style="max-width:100px;">
                    <span style="font-size:.75rem;color:var(--text-muted);">Kosongkan untuk otomatis ditambahkan di akhir.</span>
                </div>
            </div>
            <div class="gl-modal__footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalSection">Batal</button>
                <button type="submit" class="btn btn--primary" id="btnSectionSubmit">Simpan Seksi</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: Tambah / Edit Item
     ══════════════════════════════════════════════════════ --}}
<div class="gl-modal-overlay" id="modalItem">
    <div class="gl-modal">
        <div class="gl-modal__header">
            <span class="gl-modal__title" id="modalItemTitle">Tambah Langkah</span>
            <button type="button" class="gl-modal__close" data-close-modal="modalItem">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="formItem" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formItemMethod" value="POST">
            <div class="gl-modal__body">
                <div class="gl-form-field">
                    <label for="itemTitle">Judul Langkah <span class="req-star">*</span></label>
                    <input type="text" id="itemTitle" name="title" required maxlength="20" placeholder="cth. Buka aplikasi">
                </div>
                <div class="gl-form-field">
                    <label for="itemContent">Penjelasan</label>
                    <textarea id="itemContent" name="content" rows="4" maxlength="5000" placeholder="Jelaskan langkah ini secara detail..."></textarea>
                </div>
                <div class="gl-form-field">
                    <label>Screenshot / Gambar</label>
                    <div id="currentImageWrap" class="gl-current-image" style="display:none;">
                        <p style="font-size:.78rem;color:var(--text-muted);margin:0 0 4px;">Gambar saat ini:</p>
                        <img id="currentImage" src="" alt="Gambar saat ini">
                        <br>
                        <button type="button" class="gl-remove-image" id="btnRemoveImage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            Hapus gambar ini
                        </button>
                        <input type="hidden" name="remove_image" id="removeImageInput" value="0">
                    </div>
                    <div class="gl-upload-area" id="uploadArea">
                        <input type="file" name="image" id="itemImage" accept="image/jpg,image/jpeg,image/png,image/webp">
                        <div class="gl-upload-area__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <div class="gl-upload-area__text">
                            <strong>Klik untuk pilih gambar</strong> atau seret ke sini<br>
                            <span style="font-size:.75rem;">JPG, PNG, WebP — maks. 3 MB</span>
                        </div>
                    </div>
                    <div class="gl-upload-preview" id="uploadPreview">
                        <img id="previewImg" src="" alt="Pratinjau">
                    </div>
                </div>
                <div class="gl-form-field">
                    <label for="itemOrder">Nomor Urutan</label>
                    <input type="number" id="itemOrder" name="order" min="0" max="999" placeholder="0" style="max-width:100px;">
                    <span style="font-size:.75rem;color:var(--text-muted);">Kosongkan untuk otomatis ditambahkan di akhir.</span>
                </div>
            </div>
            <div class="gl-modal__footer">
                <button type="button" class="btn btn--ghost" data-close-modal="modalItem">Batal</button>
                <button type="submit" class="btn btn--primary" id="btnItemSubmit">Simpan Langkah</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: Konfirmasi Hapus
     ══════════════════════════════════════════════════════ --}}
<div class="gl-modal-overlay" id="modalDelete">
    <div class="gl-modal" style="max-width:420px;">
        <div class="gl-modal__header">
            <span class="gl-modal__title" style="color:#dc2626;">Konfirmasi Hapus</span>
            <button type="button" class="gl-modal__close" data-close-modal="modalDelete">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="gl-modal__body" style="gap:10px;">
            <p style="margin:0;color:var(--text-primary);" id="deleteConfirmText">Yakin ingin menghapus ini?</p>
            <p style="margin:0;font-size:.82rem;color:var(--text-muted);">Tindakan ini tidak dapat dibatalkan. Seluruh data di dalamnya juga akan terhapus.</p>
        </div>
        <div class="gl-modal__footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDelete">Batal</button>
            <form id="formDelete" method="POST" action="" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn--danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>{!! file_get_contents(resource_path('views/Guidelines/main.js')) !!}</script>
@endpush
