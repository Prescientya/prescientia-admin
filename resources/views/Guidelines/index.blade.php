@extends('layouts.app')

@section('title', 'Panduan Aplikasi')

@section('breadcrumb')
    <span style="color:var(--text-muted);">Informasi</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color:var(--text-muted);"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Panduan Aplikasi</span>
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
    <div>
        <p style="font-size:.84rem;color:var(--text-muted);margin:2px 0 0;">
            Kelola halaman panduan penggunaan aplikasi Prescientia untuk siswa dan guru.
        </p>
    </div>

    {{-- Cards --}}
    <div class="gl-cards">

        @foreach(['siswa', 'guru'] as $type)
        @php
            $page = $pages->get($type);
            $isPublished = $page && $page->is_published;
            $sectionCount = $page ? ($page->sections_count ?? 0) : 0;
        @endphp
        <div class="gl-card">
            <div class="gl-card__header">
                <div class="gl-card__icon gl-card__icon--{{ $type }}">
                    @if($type === 'siswa')
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @endif
                </div>
                <div class="gl-card__meta">
                    <div class="gl-card__type">Panduan {{ ucfirst($type) }}</div>
                    <div class="gl-card__title">{{ $page ? $page->title : 'Belum dikonfigurasi' }}</div>
                    @if($page && $page->subtitle)
                    <div class="gl-card__subtitle">{{ $page->subtitle }}</div>
                    @endif
                </div>
            </div>

            <div class="gl-card__body">
                <div>
                    <div class="gl-card__stat">
                        <strong>{{ $sectionCount }}</strong> seksi panduan
                    </div>
                    <div style="margin-top:6px;">
                        <span class="gl-pub-badge {{ $isPublished ? 'gl-pub-badge--on' : 'gl-pub-badge--off' }}">
                            <span class="gl-pub-dot"></span>
                            {{ $isPublished ? 'Dipublikasikan' : 'Disembunyikan' }}
                        </span>
                    </div>
                </div>

                <div class="gl-card__actions">
                    @if($page)
                    {{-- Toggle Publish --}}
                    <form action="{{ route('guidelines.toggle-publish', $page) }}" method="POST" style="margin:0;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn--ghost btn--sm" title="{{ $isPublished ? 'Sembunyikan halaman' : 'Publikasikan halaman' }}">
                            @if($isPublished)
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            Sembunyikan
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Publikasikan
                            @endif
                        </button>
                    </form>

                    {{-- Lihat publik --}}
                    <a href="{{ route('panduan.show', $type) }}" target="_blank" class="btn btn--ghost btn--sm" title="Lihat halaman publik">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Lihat
                    </a>

                    {{-- Kelola --}}
                    <a href="{{ route('guidelines.show', $page) }}" class="btn btn--primary btn--sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Kelola
                    </a>
                    @else
                    <span style="font-size:.8rem;color:var(--text-muted);">Belum ada data</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

    </div>

    {{-- URL Info --}}
    <div style="border:1px solid var(--card-border);border-radius:12px;background:var(--card-bg);padding:18px 22px;">
        <p style="font-size:.82rem;font-weight:600;color:var(--text-secondary);margin:0 0 10px;">URL Halaman Panduan (Publik)</p>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <code style="font-size:.8rem;background:var(--body-bg);padding:5px 10px;border-radius:6px;border:1px solid var(--card-border);">{{ url('/panduan/siswa') }}</code>
                <a href="{{ route('panduan.show', 'siswa') }}" target="_blank" class="btn btn--ghost btn--sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Buka
                </a>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <code style="font-size:.8rem;background:var(--body-bg);padding:5px 10px;border-radius:6px;border:1px solid var(--card-border);">{{ url('/panduan/guru') }}</code>
                <a href="{{ route('panduan.show', 'guru') }}" target="_blank" class="btn btn--ghost btn--sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Buka
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
