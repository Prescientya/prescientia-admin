<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($page)Panduan {{ $page->user_type === 'siswa' ? 'Siswa' : 'Guru' }} — Prescientia
        @else Panduan {{ $type === 'siswa' ? 'Siswa' : 'Guru' }} — Prescientia
        @endif
    </title>
    <link rel="icon" type="image/png" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <style>
        /* ── Reset & Base ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --cream:      #FFFBEB;
            --cream-dark: #FEF3C7;
            --gold:       #F59E0B;
            --gold-dark:  #D97706;
            --gold-light: #FEF9EE;
            --gold-soft:  #FDE68A;
            --ink:        #1F2937;
            --ink-soft:   #374151;
            --muted:      #6B7280;
            --border:     #FDE68A;
            --border-soft:#E5E7EB;
            --white:      #FFFFFF;
            --step-bg:    #FEF3C7;
            --step-color: #92400E;
            --shadow:     rgba(245,158,11,0.12);
            --shadow-lg:  rgba(0,0,0,0.08);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            background: var(--cream);
            color: var(--ink);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Progress Bar ─────────────────────────────── */
        #readProgress {
            position: fixed; top: 0; left: 0; height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-dark));
            width: 0%; z-index: 1000; transition: width .1s linear;
            border-radius: 0 2px 2px 0;
        }

        /* ── Back to top ──────────────────────────────── */
        #backToTop {
            position: fixed; bottom: 28px; right: 28px; z-index: 100;
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--gold); color: #fff; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(245,158,11,0.4);
            opacity: 0; pointer-events: none;
            transition: opacity .3s, transform .3s;
            transform: translateY(12px);
        }
        #backToTop.show { opacity: 1; pointer-events: all; transform: translateY(0); }
        #backToTop:hover { background: var(--gold-dark); }

        /* ── Nav bar ──────────────────────────────────── */
        .pand-nav {
            position: sticky; top: 0; z-index: 90;
            background: rgba(255,251,235,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            transition: box-shadow .35s ease;
        }
        .pand-nav.shrink {
            box-shadow: 0 3px 16px rgba(0,0,0,0.10);
        }
        .pand-nav__inner {
            max-width: 860px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            height: 90px; gap: 14px;
            transition: height .35s ease;
        }
        .pand-nav.shrink .pand-nav__inner {
            height: 56px;
        }
        .pand-nav__brand {
            display: flex; align-items: center; gap: 14px;
            font-weight: 700; font-size: 1.1rem; color: var(--ink);
            text-decoration: none;
        }
        .pand-nav__brand img {
            width: 64px; height: 64px; border-radius: 12px;
            transition: width .35s ease, height .35s ease, border-radius .35s ease;
        }
        .pand-nav.shrink .pand-nav__brand img {
            width: 38px; height: 38px; border-radius: 8px;
        }
        .pand-nav__brand-text {
            display: flex; flex-direction: column; gap: 2px;
            transition: gap .35s ease;
        }
        .pand-nav__brand-name {
            font-weight: 700; font-size: 1.1rem; color: var(--ink);
            transition: font-size .35s ease;
            line-height: 1.2;
        }
        .pand-nav.shrink .pand-nav__brand-name {
            font-size: .95rem;
        }
        .pand-nav__brand-label {
            font-size: .62rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: var(--gold-dark);
            line-height: 1;
            overflow: hidden; max-height: 20px;
            opacity: 1;
            transition: opacity .25s ease, max-height .35s ease;
        }
        .pand-nav.shrink .pand-nav__brand-label {
            opacity: 0; max-height: 0;
        }

        /* ── Hero ─────────────────────────────────────── */
        .pand-hero {
            background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 60%, #FDE68A22 100%);
            border-bottom: 1px solid var(--border);
            padding: 56px 24px 48px;
            text-align: center;
            position: relative; overflow: hidden;
            min-height: 95vh;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
        }
        .pand-hero::before {
            content: '';
            position: absolute; top: -80px; right: -80px;
            width: 320px; height: 320px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,158,11,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .pand-hero::after {
            content: '';
            position: absolute; bottom: -60px; left: -60px;
            width: 240px; height: 240px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,158,11,.08) 0%, transparent 70%);
            pointer-events: none;
        }
        .pand-hero__inner { max-width: 700px; width: 100%; margin: 0 auto; position: relative; }
        .pand-hero__badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--white); border: 1.5px solid var(--border);
            border-radius: 20px; padding: 5px 14px 5px 10px;
            font-size: .78rem; font-weight: 600; color: var(--gold-dark);
            margin-bottom: 20px; box-shadow: 0 2px 8px var(--shadow);
        }
        .pand-hero__badge-dot {
            width: 8px; height: 8px; border-radius: 50%; background: var(--gold);
            animation: pulse-dot 2s ease-in-out infinite;
        }
        .pand-hero__title {
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            font-weight: 800; color: var(--ink); line-height: 1.2;
            margin-bottom: 14px;
        }
        .pand-hero__title span { color: var(--gold-dark); }
        .pand-hero__subtitle {
            font-size: .95rem; color: var(--muted); max-width: 520px; margin: 0 auto 24px;
        }
        .pand-hero__meta {
            display: flex; align-items: center; justify-content: center; gap: 20px;
            font-size: .78rem; color: var(--muted); flex-wrap: wrap;
        }
        .pand-hero__meta-item { display: flex; align-items: center; gap: 5px; }

        /* ── Coming soon ──────────────────────────────── */
        .pand-coming {
            max-width: 500px; margin: 100px auto; text-align: center; padding: 0 24px 40px;
        }
        .pand-coming__title { font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; }
        .pand-coming__text { color: var(--muted); font-size: .9rem; }

        /* ── TOC ──────────────────────────────────────── */
        .pand-layout {
            max-width: 1080px; margin: 0 auto; padding: 40px 24px 80px;
            display: grid; grid-template-columns: 1fr; gap: 32px;
        }
        @media (min-width: 900px) {
            .pand-layout { grid-template-columns: 220px 1fr; align-items: start; }
        }

        .pand-toc {
            position: sticky; top: 90px;
        }
        .pand-toc__title {
            font-size: .72rem; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: var(--muted); margin-bottom: 10px;
        }
        .pand-toc__list { list-style: none; display: flex; flex-direction: column; gap: 2px; }
        .pand-toc__item a {
            display: block; padding: 6px 10px; border-radius: 8px;
            font-size: .82rem; color: var(--muted); text-decoration: none;
            transition: all .15s; border-left: 2px solid transparent;
        }
        .pand-toc__item a:hover { color: var(--gold-dark); background: var(--gold-light); border-left-color: var(--gold); }
        .pand-toc__item a.active { color: var(--gold-dark); background: var(--gold-light); border-left-color: var(--gold); font-weight: 600; }

        @media (max-width: 899px) {
            .pand-toc { display: none; }
        }

        /* ── Sections ─────────────────────────────────── */
        .pand-sections { display: flex; flex-direction: column; gap: 36px; }

        .pand-section {
            opacity: 0; transform: translateY(28px);
            transition: opacity .55s ease, transform .55s ease;
        }
        .pand-section.visible { opacity: 1; transform: translateY(0); }

        .pand-section__header {
            display: flex; align-items: flex-start; gap: 14px; margin-bottom: 20px;
        }
        .pand-section__num {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            background: var(--gold); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .9rem; box-shadow: 0 3px 10px rgba(245,158,11,.35);
        }
        .pand-section__title {
            font-size: 1.2rem; font-weight: 800; color: var(--ink); padding-top: 6px;
        }
        .pand-section__desc {
            font-size: .88rem; color: var(--muted); margin-top: 4px; line-height: 1.6;
        }
        .pand-divider {
            height: 2px; background: linear-gradient(90deg, var(--gold-soft), transparent);
            border-radius: 2px; margin-bottom: 20px;
        }

        /* ── Items ─────────────────────────────────────── */
        .pand-items { display: flex; flex-direction: column; gap: 18px; }

        .pand-item {
            background: var(--white); border: 1px solid var(--border-soft);
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 2px 12px var(--shadow-lg);
            opacity: 0; transform: translateY(16px);
            transition: opacity .45s ease, transform .45s ease, box-shadow .2s;
        }
        .pand-item.visible { opacity: 1; transform: translateY(0); }
        .pand-item:hover { box-shadow: 0 6px 24px rgba(245,158,11,.15); }

        .pand-item__content { padding: 18px 20px; display: flex; gap: 14px; }
        .pand-item__step {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            background: var(--step-bg); color: var(--step-color);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .8rem; margin-top: 1px;
        }
        .pand-item__body { flex: 1; min-width: 0; }
        .pand-item__title {
            font-weight: 700; font-size: .95rem; color: var(--ink); margin-bottom: 6px;
        }
        .pand-item__text {
            font-size: .875rem; color: var(--ink-soft); line-height: 1.7;
            white-space: pre-wrap;
        }
        .pand-item__image {
            border-top: 1px solid var(--border-soft);
            padding: 16px 20px; background: #FAFAFA;
            display: flex; justify-content: center;
        }
        .pand-item__image img {
            max-width: 100%; max-height: 420px;
            border-radius: 10px; border: 1px solid var(--border);
            object-fit: contain; box-shadow: 0 4px 16px var(--shadow-lg);
            cursor: zoom-in;
            transition: transform .2s;
        }
        .pand-item__image img:hover { transform: scale(1.01); }

        /* ── Lightbox ─────────────────────────────────── */
        #lightbox {
            position: fixed; inset: 0; background: rgba(0,0,0,.85);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999; padding: 20px;
            opacity: 0; pointer-events: none; transition: opacity .25s;
        }
        #lightbox.open { opacity: 1; pointer-events: all; }
        #lightbox img {
            max-width: 95vw; max-height: 90vh; border-radius: 10px;
            object-fit: contain; transition: transform .2s;
        }
        #lightbox__close {
            position: absolute; top: 16px; right: 16px;
            width: 40px; height: 40px; border-radius: 50%; border: none;
            background: rgba(255,255,255,.15); color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; transition: background .2s;
        }
        #lightbox__close:hover { background: rgba(255,255,255,.3); }

        /* ── Page content wrapper ──────────────────────── */
        .pand-content { flex: 1; }

        /* ── Footer ───────────────────────────────────── */
        .pand-footer {
            background: var(--ink); color: rgba(255,255,255,.6);
            text-align: center; padding: 28px 24px; font-size: .8rem;
            margin-top: auto;
        }
        .pand-footer a { color: var(--gold-soft); text-decoration: none; }
        .pand-footer a:hover { color: var(--gold); }

        /* ── Animations ───────────────────────────────── */
        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%       { transform: scale(1.3); opacity: .7; }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeDown {
            from { opacity: 1; transform: translateY(0); }
            to   { opacity: 0; transform: translateY(12px); }
        }
        /* Hero entrance — JS toggles .hero-fade-in to replay animation */
        .pand-hero__inner > * { opacity: 0; }
        .pand-hero__inner > *.hero-fade-in { animation: fadeUp .6s ease both; }
        .pand-hero__badge.hero-fade-in    { animation-delay: .05s; }
        .pand-hero__title.hero-fade-in    { animation-delay: .18s; }
        .pand-hero__subtitle.hero-fade-in { animation-delay: .30s; }
        .pand-hero__meta.hero-fade-in     { animation-delay: .42s; }
        /* TOC items — JS toggles classes to replay animation */
        .pand-toc__item                { opacity: 0; }
        .pand-toc__item.toc-fade-in   { animation: fadeUp  .38s ease both; }
        .pand-toc__item.toc-fade-out  { animation: fadeDown .28s ease both forwards; }

        /* ── Responsive ───────────────────────────────── */
        @media (max-width: 600px) {
            .pand-hero { padding: 40px 18px 36px; min-height: 95svh; }
            .pand-layout { padding: 28px 16px 60px; }
            .pand-item__content { padding: 14px 16px; }
            #backToTop { bottom: 16px; right: 16px; }
        }
    </style>
</head>
<body>

{{-- Read Progress Bar --}}
<div id="readProgress"></div>

{{-- Nav --}}
<nav class="pand-nav">
    <div class="pand-nav__inner">
        <a href="{{ url('/') }}" class="pand-nav__brand">
            <img src="{{ asset('storage/prescientia-logo-square.png') }}" alt="Prescientia" onerror="this.style.display='none'">
            <div class="pand-nav__brand-text">
                <span class="pand-nav__brand-name">Prescientia</span>
                <span class="pand-nav__brand-label">Guide Line</span>
            </div>
        </a>
    </div>
</nav>

<div class="pand-content">
@if(!$isPublished)
{{-- Not published or not found --}}
<div class="pand-coming">
    <h1 class="pand-coming__title">Panduan Segera Hadir</h1>
    <p class="pand-coming__text">
        Halaman panduan untuk
        <strong>{{ $type === 'siswa' ? 'Siswa' : 'Guru' }}</strong>
        sedang dalam tahap persiapan. Silakan cek kembali nanti.
    </p>
</div>

@else
{{-- Hero --}}
<section class="pand-hero">
    <div class="pand-hero__inner">
        <div class="pand-hero__badge">
            <span class="pand-hero__badge-dot"></span>
            Panduan Resmi Aplikasi {{ $type === 'siswa' ? 'Siswa' : 'Guru' }}
        </div>
        <h1 class="pand-hero__title">
            {!! nl2br(e($page->title)) !!}
        </h1>
        @if($page->subtitle)
        <p class="pand-hero__subtitle">{{ $page->subtitle }}</p>
        @endif
        <div class="pand-hero__meta">
            <span class="pand-hero__meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Untuk {{ $type === 'siswa' ? 'Siswa' : 'Guru' }} Prescientia
            </span>
            <span class="pand-hero__meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Diperbarui: {{ $page->updated_at->translatedFormat('d F Y') }}
            </span>
            <span class="pand-hero__meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                {{ $page->sections->count() }} Topik Panduan
            </span>
        </div>
    </div>
</section>

@if($page->sections->isEmpty())
<div class="pand-coming">
    <h2 class="pand-coming__title">Konten Segera Ditambahkan</h2>
    <p class="pand-coming__text">Panduan untuk bagian ini sedang disiapkan.</p>
</div>
@else

{{-- Layout: TOC + Content --}}
<div class="pand-layout">

    {{-- Table of Contents --}}
    <aside class="pand-toc">
        <div class="pand-toc__title">Daftar Isi</div>
        <ul class="pand-toc__list">
            @foreach($page->sections as $sec)
            <li class="pand-toc__item">
                <a href="#section-{{ $sec->id }}" data-toc-target="section-{{ $sec->id }}">
                    {{ $loop->iteration }}. {{ $sec->title }}
                </a>
            </li>
            @endforeach
        </ul>
    </aside>

    {{-- Sections --}}
    <main class="pand-sections">
        @foreach($page->sections as $sec)
        <article class="pand-section" id="section-{{ $sec->id }}">
            <div class="pand-section__header">
                <div class="pand-section__num">{{ $loop->iteration }}</div>
                <div>
                    <div class="pand-section__title">{{ $sec->title }}</div>
                    @if($sec->description)
                    <div class="pand-section__desc">{{ $sec->description }}</div>
                    @endif
                </div>
            </div>
            <div class="pand-divider"></div>

            @if($sec->items->isNotEmpty())
            <div class="pand-items">
                @foreach($sec->items as $item)
                <div class="pand-item">
                    <div class="pand-item__content">
                        <div class="pand-item__step">{{ $loop->iteration }}</div>
                        <div class="pand-item__body">
                            <div class="pand-item__title">{{ $item->title }}</div>
                            @if($item->content)
                            <div class="pand-item__text">{{ $item->content }}</div>
                            @endif
                        </div>
                    </div>
                    @if($item->image_path)
                    <div class="pand-item__image">
                        <img src="{{ Storage::url($item->image_path) }}"
                             alt="{{ $item->title }}"
                             loading="lazy"
                             data-lightbox="{{ Storage::url($item->image_path) }}">
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </article>
        @endforeach
    </main>

</div>
@endif
@endif
</div>{{-- /.pand-content --}}

{{-- Footer --}}
<footer class="pand-footer">
    <p>
        &copy; {{ date('Y') }} Prescientia.
        Halaman panduan ini disediakan oleh tim Prescientia untuk kemudahan pengguna.
    </p>
</footer>

{{-- Back to Top --}}
<button id="backToTop" aria-label="Kembali ke atas" title="Kembali ke atas">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
</button>

{{-- Lightbox --}}
<div id="lightbox">
    <button id="lightbox__close" aria-label="Tutup">&times;</button>
    <img id="lightboxImg" src="" alt="">
</div>

<script>
(function () {
    'use strict';

    /* ── Read progress bar ────────────────────────── */
    var progress = document.getElementById('readProgress');
    function updateProgress() {
        if (!progress) return;
        var scrolled = window.scrollY;
        var total    = document.documentElement.scrollHeight - window.innerHeight;
        progress.style.width = total > 0 ? ((scrolled / total) * 100) + '%' : '0%';
    }

    /* ── Nav shrink on scroll ─────────────────────── */
    var nav = document.querySelector('.pand-nav');
    function updateNavShrink() {
        if (!nav) return;
        if (window.scrollY > 80) nav.classList.add('shrink');
        else nav.classList.remove('shrink');
    }

    /* ── Back to top ──────────────────────────────── */
    var btn = document.getElementById('backToTop');
    function updateBackToTop() {
        if (!btn) return;
        if (window.scrollY > 300) btn.classList.add('show');
        else btn.classList.remove('show');
    }
    if (btn) btn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

    window.addEventListener('scroll', function () {
        updateProgress();
        updateBackToTop();
        highlightToc();
        updateNavShrink();
    }, { passive: true });

    updateProgress();
    updateNavShrink();

    /* ── Intersection Observer — sections & items ── */
    var observerOpts = { threshold: 0.12, rootMargin: '-40px 0px -60px 0px' };
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                var items = entry.target.querySelectorAll('.pand-item');
                items.forEach(function (item, i) {
                    setTimeout(function () { item.classList.add('visible'); }, i * 80);
                });
                observer.unobserve(entry.target);
            }
        });
    }, observerOpts);

    document.querySelectorAll('.pand-section').forEach(function (el) {
        observer.observe(el);
    });

    /* ── Hero re-entrance + TOC animation (unified, replay on scroll) ── */
    var tocItems = document.querySelectorAll('.pand-toc__item');
    var heroEl   = document.querySelector('.pand-hero');
    var heroKids = document.querySelectorAll('.pand-hero__inner > *');

    function playHero() {
        heroKids.forEach(function (el) {
            el.classList.remove('hero-fade-in');
            el.offsetHeight; // force reflow to restart animation
            el.classList.add('hero-fade-in');
        });
    }
    function stopHero() {
        heroKids.forEach(function (el) { el.classList.remove('hero-fade-in'); });
    }

    function showToc() {
        tocItems.forEach(function (item, i) {
            item.classList.remove('toc-fade-out', 'toc-fade-in');
            item.style.animationDelay = '';
            item.offsetHeight;
            item.style.animationDelay = (i * 90) + 'ms';
            item.classList.add('toc-fade-in');
        });
    }
    function hideToc() {
        var arr = Array.from(tocItems).reverse();
        arr.forEach(function (item, i) {
            item.classList.remove('toc-fade-in', 'toc-fade-out');
            item.style.animationDelay = '';
            item.offsetHeight;
            item.style.animationDelay = (i * 65) + 'ms';
            item.classList.add('toc-fade-out');
        });
    }

    if (heroEl) {
        var heroObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    // Hero in view → play hero entrance, hide TOC
                    playHero();
                    if (tocItems.length) hideToc();
                } else {
                    // Hero out of view → reset hero, show TOC
                    stopHero();
                    if (tocItems.length) showToc();
                }
            });
        }, { threshold: 0 });
        heroObserver.observe(heroEl);
    }

    /* ── TOC active highlight ─────────────────────── */
    var sections = document.querySelectorAll('.pand-section[id]');
    var tocLinks = document.querySelectorAll('[data-toc-target]');
    function highlightToc() {
        var scrollY = window.scrollY + 100;
        var current = null;
        sections.forEach(function (sec) {
            if (sec.offsetTop <= scrollY) current = sec.id;
        });
        tocLinks.forEach(function (a) {
            a.classList.toggle('active', a.dataset.tocTarget === current);
        });
    }

    /* ── Smooth TOC scroll ────────────────────────── */
    tocLinks.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.getElementById(a.dataset.tocTarget);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    /* ── Lightbox ─────────────────────────────────── */
    var lightbox    = document.getElementById('lightbox');
    var lightboxImg = document.getElementById('lightboxImg');
    var lbClose     = document.getElementById('lightbox__close');

    document.querySelectorAll('[data-lightbox]').forEach(function (img) {
        img.addEventListener('click', function () {
            lightboxImg.src = img.dataset.lightbox;
            lightbox.classList.add('open');
        });
    });
    function closeLightbox() {
        lightbox.classList.remove('open');
        setTimeout(function () { lightboxImg.src = ''; }, 300);
    }
    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lightbox) lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && lightbox && lightbox.classList.contains('open')) closeLightbox();
    });
})();
</script>

</body>
</html>
