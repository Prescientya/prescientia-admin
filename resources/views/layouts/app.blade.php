<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &mdash; Prescientia Admin</title>

    {{-- Anti-flash: apply theme BEFORE render --}}
    <script>
        (function(){
            var m = localStorage.getItem('prescentia-theme-mode') || 'light';
            var c = localStorage.getItem('prescentia-theme-color') || 'blue';
            document.documentElement.setAttribute('data-pre-mode', m);
            document.documentElement.setAttribute('data-pre-color', c);
        })();
    </script>

    <style>{!! file_get_contents(resource_path('views/layouts/style.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('views/components/global.css')) !!}</style>
    @stack('styles')
</head>
<body>

{{-- Apply theme immediately on body (prevents flash) --}}
<script>
    (function(){
        var m = localStorage.getItem('prescentia-theme-mode') || 'light';
        var c = localStorage.getItem('prescentia-theme-color') || 'blue';
        document.body.dataset.mode  = m;
        document.body.dataset.color = c;
    })();
</script>

@php
    $authUser  = Auth::guard('admin')->user();
    $adminName = optional(optional($authUser)->admin)->name ?? 'Admin';
    $adminEmail = $authUser->email ?? '';
    $adminInitial = strtoupper(substr($adminName, 0, 1));
    $adminPhoto = optional(optional($authUser)->admin)->photo_profile;
@endphp

<div class="app-wrapper" id="appWrapper">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside class="sidebar" id="sidebar">

        {{-- Brand --}}
        <a href="{{ route('Dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <span class="sidebar-brand-text">Prescientia</span>
        </a>

        {{-- Navigation --}}
        <nav class="sidebar-nav">

            {{-- Menu Utama --}}
            <div class="nav-section">
                <span class="nav-section-label">Menu Utama</span>

                <a href="{{ route('Dashboard') }}"
                   title="Dashboard"
                   class="nav-item {{ request()->routeIs('Dashboard') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span class="nav-item-label">Dashboard</span>
                </a>
            </div>

            {{-- Data Master --}}
            <div class="nav-section">
                <span class="nav-section-label">Data Master</span>

                <a href="{{ route('siswa.index') }}" title="Data Siswa" class="nav-item {{ request()->routeIs('siswa*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span class="nav-item-label">Data Siswa</span>
                </a>

                <a href="{{ route('guru.index') }}" title="Data Guru" class="nav-item {{ request()->routeIs('guru*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                        <polyline points="16 11 18 13 22 9"/>
                    </svg>
                    <span class="nav-item-label">Data Guru</span>
                </a>

                <a href="{{ route('kelas.index') }}" title="Data Kelas" class="nav-item {{ request()->routeIs('kelas*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    <span class="nav-item-label">Data Kelas</span>
                </a>
            </div>

            {{-- Kehadiran --}}
            <div class="nav-section">
                <span class="nav-section-label">Kehadiran</span>

                <a href="{{ route('attendance.student.index') }}" title="Kehadiran Siswa" class="nav-item {{ request()->routeIs('attendance.student*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <polyline points="9 16 11 18 15 14"/>
                    </svg>
                    <span class="nav-item-label">Kehadiran Siswa</span>
                </a>

                <a href="{{ route('attendance.teacher.index') }}" title="Kehadiran Guru" class="nav-item {{ request()->routeIs('attendance.teacher*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <polyline points="9 15 11 17 15 13"/>
                    </svg>
                    <span class="nav-item-label">Kehadiran Guru</span>
                </a>

                <a href="{{ route('absence-letters.index') }}" title="Surat Izin" class="nav-item {{ request()->routeIs('absence-letters*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span class="nav-item-label">Surat Izin</span>
                </a>
            </div>

            {{-- Akademik --}}
            <div class="nav-section">
                <span class="nav-section-label">Akademik</span>

                <a href="{{ route('jadwal-mengajar.index') }}" title="Jadwal Mengajar" class="nav-item {{ request()->routeIs('jadwal-mengajar*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <line x1="8" y1="14" x2="8" y2="14"/><line x1="12" y1="14" x2="12" y2="14"/>
                        <line x1="8" y1="18" x2="8" y2="18"/><line x1="12" y1="18" x2="12" y2="18"/>
                    </svg>
                    <span class="nav-item-label">Jadwal Mengajar</span>
                </a>

                <a href="{{ route('mapel.index') }}" title="Mata Pelajaran" class="nav-item {{ request()->routeIs('mapel*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span class="nav-item-label">Mata Pelajaran</span>
                </a>

                <a href="{{ route('jam-pelajaran.index') }}" title="Jam Pelajaran" class="nav-item {{ request()->routeIs('jam-pelajaran*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span class="nav-item-label">Jam Pelajaran</span>
                </a>

                <a href="#" title="Laporan" class="nav-item {{ request()->routeIs('reports*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    <span class="nav-item-label">Laporan</span>
                </a>
            </div>

            {{-- Informasi --}}
            <div class="nav-section">
                <span class="nav-section-label">Informasi</span>

                <a href="{{ route('events.index') }}" title="Event / Acara"
                   class="nav-item {{ request()->routeIs('events*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <line x1="9" y1="14" x2="15" y2="14"/><line x1="9" y1="18" x2="13" y2="18"/>
                    </svg>
                    <span class="nav-item-label">Event / Acara</span>
                </a>
            </div>

            {{-- Sistem --}}
            <div class="nav-section">
                <span class="nav-section-label">Sistem</span>

                <a href="{{ route('school-calendar.index') }}" title="Kalender Sekolah"
                   class="nav-item {{ request()->routeIs('school-calendar*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span class="nav-item-label">Kalender Sekolah</span>
                </a>

                <a href="{{ route('wifi-networks.index') }}" title="Jaringan WiFi"
                   class="nav-item {{ request()->routeIs('wifi-networks*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                        <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                        <line x1="12" y1="20" x2="12.01" y2="20"/>
                    </svg>
                    <span class="nav-item-label">Jaringan WiFi</span>
                </a>

                <a href="{{ route('device-requests.index') }}" title="Permintaan Device"
                   class="nav-item {{ request()->routeIs('device-requests*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                        <line x1="12" y1="18" x2="12.01" y2="18"/>
                    </svg>
                    <span class="nav-item-label">Permintaan Device</span>
                </a>

                <a href="#" title="Pengaturan" class="nav-item {{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M16.24 7.76a6 6 0 0 1 0 8.49M4.93 4.93a10 10 0 0 0 0 14.14M7.76 7.76a6 6 0 0 0 0 8.49"/>
                    </svg>
                    <span class="nav-item-label">Pengaturan</span>
                </a>
            </div>

        </nav>

    </aside>

    {{-- Sidebar Overlay (mobile) --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ===================== MAIN ===================== --}}
    <div class="app-main" id="appMain">

        {{-- ===== HEADER ===== --}}
        <header class="app-header">
            <div class="header-left">
                {{-- Sidebar toggle --}}
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6"  x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                {{-- Breadcrumb --}}
                <span class="breadcrumb-text">@yield('breadcrumb', 'Dashboard')</span>
            </div>

            <div class="header-right">

                {{-- Profile + Theme Dropdown --}}
                <div class="profile-dropdown-wrapper">
                    <button class="profile-trigger" id="profileTrigger" aria-expanded="false"
                            aria-haspopup="true" aria-label="Menu Profil">

                        <div class="profile-avatar">
                            @if($adminPhoto)
                                <img src="{{ asset('storage/' . $adminPhoto) }}" alt="{{ $adminName }}">
                            @else
                                {{ $adminInitial }}
                            @endif
                        </div>

                        <span class="profile-name">{{ $adminName }}</span>

                        {{-- Chevron --}}
                        <svg class="profile-chevron" xmlns="http://www.w3.org/2000/svg"
                            width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>

                    {{-- Dropdown --}}
                    <div class="profile-dropdown" id="profileDropdown">

                        {{-- Profile Header --}}
                        <div class="dropdown-profile-header">
                            <div class="dropdown-profile-avatar">
                                @if($adminPhoto)
                                    <img src="{{ asset('storage/' . $adminPhoto) }}" alt="{{ $adminName }}">
                                @else
                                    {{ $adminInitial }}
                                @endif
                            </div>
                            <div>
                                <div class="dropdown-profile-name">{{ $adminName }}</div>
                                <div class="dropdown-profile-email">{{ $adminEmail }}</div>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        {{-- Menu Items --}}
                        <a href="#" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Profil Saya
                        </a>

                        <div class="dropdown-divider"></div>

                        {{-- Theme Panel --}}
                        <div class="theme-panel">
                            <div class="theme-panel-title">Tema Tampilan</div>

                            {{-- Mode Toggle --}}
                            <div class="mode-toggle">
                                <button class="mode-btn" data-mode-btn="light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="5"/>
                                        <line x1="12" y1="1"  x2="12" y2="3"/>
                                        <line x1="12" y1="21" x2="12" y2="23"/>
                                        <line x1="4.22" y1="4.22"   x2="5.64" y2="5.64"/>
                                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                        <line x1="1" y1="12" x2="3" y2="12"/>
                                        <line x1="21" y1="12" x2="23" y2="12"/>
                                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                    </svg>
                                    Terang
                                </button>
                                <button class="mode-btn" data-mode-btn="dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                    </svg>
                                    Gelap
                                </button>
                            </div>

                            {{-- Color Palette --}}
                            <div class="theme-panel-subtitle">Warna Tema</div>
                            <div class="color-palette">
                                @foreach([
                                    'blue'   => ['label'=>'Biru',  'bg'=>'#1e3a5f'],
                                    'black'  => ['label'=>'Hitam', 'bg'=>'#111827'],
                                    'white'  => ['label'=>'Putih', 'bg'=>'#d1d5db'],
                                    'purple' => ['label'=>'Ungu',  'bg'=>'#4c1d95'],
                                    'green'  => ['label'=>'Hijau', 'bg'=>'#064e3b'],
                                ] as $colorKey => $colorData)
                                <div class="color-swatch-wrapper">
                                    <button
                                        class="color-swatch"
                                        data-color-swatch="{{ $colorKey }}"
                                        style="background:{{ $colorData['bg'] }}"
                                        title="{{ $colorData['label'] }}"
                                        aria-label="Tema {{ $colorData['label'] }}"
                                    ></button>
                                    <span class="color-swatch-label">{{ $colorData['label'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        {{-- Logout --}}
                        <form action="{{ route('logout') }}" method="POST" style="margin:0">
                            @csrf
                            <button type="submit" class="dropdown-item danger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Keluar
                            </button>
                        </form>

                    </div>
                </div>
                {{-- End Profile Dropdown --}}

            </div>
        </header>
        {{-- End Header --}}

        {{-- ===== CONTENT ===== --}}
        <main class="app-content">
            <div class="content-card">
                @yield('content')
            </div>
        </main>

        {{-- ===== FOOTER ===== --}}
        <footer class="app-footer">
            <span class="footer-text">&copy; {{ date('Y') }} Prescientia Admin. All rights reserved.</span>
            <span class="footer-text">v1.0.0</span>
        </footer>

    </div>
    {{-- End App Main --}}

</div>
{{-- End App Wrapper --}}

<script>{!! file_get_contents(resource_path('views/layouts/main.js')) !!}</script>
<script>{!! file_get_contents(resource_path('views/components/global.js')) !!}</script>
@stack('scripts')

</body>
</html>
