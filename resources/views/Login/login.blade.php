<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <link rel="shortcut icon" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <title>Login &mdash; Prescientia Admin</title>

    {{-- Anti-flash theme apply --}}
    <script>
        (function(){
            var m = localStorage.getItem('prescentia-theme-mode') || 'light';
            var c = localStorage.getItem('prescentia-theme-color') || 'blue';
            document.documentElement.dataset.mode  = m;
            document.documentElement.dataset.color = c;
        })();
    </script>

    <style>{!! file_get_contents(resource_path('views/Login/style.css')) !!}</style>
</head>
<body>

{{-- Apply theme on body immediately --}}
<script>
    (function(){
        var m = localStorage.getItem('prescentia-theme-mode') || 'light';
        var c = localStorage.getItem('prescentia-theme-color') || 'blue';
        document.body.dataset.mode  = m;
        document.body.dataset.color = c;
    })();
</script>

{{-- ======================== GEAR ICON ======================== --}}
<button class="theme-gear-btn" id="themeGearBtn" aria-label="Pengaturan Tema" title="Pengaturan Tema">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M16.24 7.76a6 6 0 0 1 0 8.49
                 M4.93 4.93a10 10 0 0 0 0 14.14M7.76 7.76a6 6 0 0 0 0 8.49"/>
    </svg>
</button>

{{-- ======================== THEME POPUP ======================== --}}
<div class="theme-popup" id="themePopup" role="dialog" aria-label="Pengaturan Tema">
    <div class="theme-popup-header">
        <span class="theme-popup-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
            </svg>
            Pengaturan Tema
        </span>
        <button class="theme-popup-close" id="themePopupClose" aria-label="Tutup">&times;</button>
    </div>

    <div class="theme-popup-body">
        {{-- Mode --}}
        <div class="tp-section-label">Mode Tampilan</div>
        <div class="tp-mode-toggle">
            <button class="tp-mode-btn" data-login-mode="light">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1"  x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                Terang
            </button>
            <button class="tp-mode-btn" data-login-mode="dark">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                Gelap
            </button>
        </div>

        {{-- Color --}}
        <div class="tp-section-label" style="margin-top:16px">Warna Tema</div>
        <div class="tp-color-grid">
            <div class="tp-swatch-wrap">
                <button class="tp-swatch" data-login-color="blue"
                    style="background:#1e3a5f" title="Biru" aria-label="Tema Biru"></button>
                <span class="tp-swatch-label">Biru</span>
            </div>
            <div class="tp-swatch-wrap">
                <button class="tp-swatch" data-login-color="black"
                    style="background:#111827" title="Hitam" aria-label="Tema Hitam"></button>
                <span class="tp-swatch-label">Hitam</span>
            </div>
            <div class="tp-swatch-wrap">
                <button class="tp-swatch" data-login-color="white"
                    style="background:#d1d5db;border:2px solid #9ca3af" title="Putih" aria-label="Tema Putih"></button>
                <span class="tp-swatch-label">Putih</span>
            </div>
            <div class="tp-swatch-wrap">
                <button class="tp-swatch" data-login-color="purple"
                    style="background:#4c1d95" title="Ungu" aria-label="Tema Ungu"></button>
                <span class="tp-swatch-label">Ungu</span>
            </div>
            <div class="tp-swatch-wrap">
                <button class="tp-swatch" data-login-color="green"
                    style="background:#064e3b" title="Hijau" aria-label="Tema Hijau"></button>
                <span class="tp-swatch-label">Hijau</span>
            </div>
        </div>
    </div>
</div>

{{-- ======================== LOGIN CARD ======================== --}}
<div class="login-wrapper">
    <div class="login-card">

        <div class="login-header">
            <h1>Prescientia</h1>
            <p>Masuk ke Panel Admin</p>
        </div>

        {{-- Error / Session Message --}}
        @if ($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @elseif (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Masukkan email admin"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan password">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <span class="btn-text">Masuk</span>
                <span class="btn-spinner" id="btnSpinner" style="display:none"></span>
            </button>

        </form>

    </div>
</div>

<script>{!! file_get_contents(resource_path('views/Login/main.js')) !!}</script>
</body>
</html>
