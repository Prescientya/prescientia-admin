<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <link rel="shortcut icon" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <title>@yield('title', 'Dashboard') &mdash; Prescientia Admin</title>

    {{-- Anti-flash: apply theme BEFORE render --}}
    <script>{!! file_get_contents(resource_path('views/layouts/theme-head.js')) !!}</script>

    <style>{!! file_get_contents(resource_path('views/layouts/style.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('views/components/global.css')) !!}</style>
    @stack('styles')
</head>
<body>

{{-- Apply theme immediately on body (prevents flash) --}}
<script>{!! file_get_contents(resource_path('views/layouts/theme-body.js')) !!}</script>

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
                <img src="{{ asset('storage/prescientia-logo-square.png') }}" alt="Logo Prescientia" onerror="this.src='{{ asset('favicon.ico') }}'">
            </div>
            <span class="sidebar-brand-text">Prescientia</span>
        </a>

        {{-- Navigation --}}
                <nav class="sidebar-nav">


            {{-- Menu Utama --}}
            <div class="nav-section">
                <span class="nav-section-label">Menu Utama</span>

                <a href="{{ route('Dashboard') }}" title="Dashboard" class="nav-item {{ request()->routeIs('Dashboard') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span class="nav-item-label">Dashboard</span>
                </a>
            </div>

            {{-- Data Master --}}
            <div class="nav-section folder {{ request()->routeIs('siswa*', 'guru*', 'kelas*') ? 'open active' : '' }}">
                <div class="nav-section-header" title="Click to open">
                    <span class="nav-section-label">
                        <svg class="folder-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        <span class="folder-text-hide">Data Master</span>
                    </span>
                    <svg class="folder-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
                <div class="nav-folder-items">
                    <a href="{{ route('siswa.index') }}" title="Data Siswa" class="nav-item {{ request()->routeIs('siswa*') ? 'active' : '' }}">
                        <span class="nav-item-label">Data Siswa</span>
                    </a>
                    <a href="{{ route('guru.index') }}" title="Data Guru" class="nav-item {{ request()->routeIs('guru*') ? 'active' : '' }}">
                        <span class="nav-item-label">Data Guru</span>
                    </a>
                    <a href="{{ route('kelas.index') }}" title="Data Kelas" class="nav-item {{ request()->routeIs('kelas*') ? 'active' : '' }}">
                        <span class="nav-item-label">Data Kelas</span>
                    </a>
                </div>
            </div>

            {{-- Kehadiran --}}
            <div class="nav-section folder {{ request()->routeIs('attendance.student*', 'attendance.teacher*', 'absence-letters*') ? 'open active' : '' }}">
                <div class="nav-section-header" title="Click to open">
                    <span class="nav-section-label">
                        <svg class="folder-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span class="folder-text-hide">Kehadiran</span>
                    </span>
                    <svg class="folder-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
                <div class="nav-folder-items">
                    <a href="{{ route('attendance.student.index') }}" title="Kehadiran Siswa" class="nav-item {{ request()->routeIs('attendance.student*') ? 'active' : '' }}">
                        <span class="nav-item-label">Kehadiran Siswa</span>
                    </a>
                    <a href="{{ route('attendance.teacher.index') }}" title="Kehadiran Guru" class="nav-item {{ request()->routeIs('attendance.teacher*') ? 'active' : '' }}">
                        <span class="nav-item-label">Kehadiran Guru</span>
                    </a>
                    <a href="{{ route('absence-letters.index') }}" title="Surat Izin" class="nav-item {{ request()->routeIs('absence-letters*') ? 'active' : '' }}">
                        <span class="nav-item-label">Surat Izin</span>
                    </a>
                </div>
            </div>

            {{-- Akademik --}}
            <div class="nav-section folder {{ request()->routeIs('jadwal-mengajar*', 'mapel*', 'jam-pelajaran*', 'reports*') ? 'open active' : '' }}">
                <div class="nav-section-header" title="Click to open">
                    <span class="nav-section-label">
                        <svg class="folder-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        <span class="folder-text-hide">Akademik</span>
                    </span>
                    <svg class="folder-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
                <div class="nav-folder-items">
                    <a href="{{ route('jadwal-mengajar.index') }}" title="Jadwal Mengajar" class="nav-item {{ request()->routeIs('jadwal-mengajar*') ? 'active' : '' }}">
                        <span class="nav-item-label">Jadwal Mengajar</span>
                    </a>
                    <a href="{{ route('mapel.index') }}" title="Mata Pelajaran" class="nav-item {{ request()->routeIs('mapel*') ? 'active' : '' }}">
                        <span class="nav-item-label">Mata Pelajaran</span>
                    </a>
                    <a href="{{ route('jam-pelajaran.index') }}" title="Jam Pelajaran" class="nav-item {{ request()->routeIs('jam-pelajaran*') ? 'active' : '' }}">
                        <span class="nav-item-label">Jam Pelajaran</span>
                    </a>
                    <a href="#" title="Laporan" class="nav-item {{ request()->routeIs('reports*') ? 'active' : '' }}">
                        <span class="nav-item-label">Laporan</span>
                    </a>
                </div>
            </div>

            {{-- Informasi --}}
            <div class="nav-section folder {{ request()->routeIs('events*', 'guidelines*') ? 'open active' : '' }}">
                <div class="nav-section-header" title="Click to open">
                    <span class="nav-section-label">
                        <svg class="folder-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span class="folder-text-hide">Informasi</span>
                    </span>
                    <svg class="folder-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
                <div class="nav-folder-items">
                    <a href="{{ route('events.index') }}" title="Event / Acara" class="nav-item {{ request()->routeIs('events*') ? 'active' : '' }}">
                        <span class="nav-item-label">Event / Acara</span>
                    </a>
                    <a href="{{ route('guidelines.index') }}" title="Panduan Aplikasi" class="nav-item {{ request()->routeIs('guidelines*') ? 'active' : '' }}">
                        <span class="nav-item-label">Panduan Aplikasi</span>
                    </a>
                </div>
            </div>

            {{-- Sistem --}}
            <div class="nav-section folder {{ request()->routeIs('school-calendar*', 'wifi-networks*', 'device-requests*', 'settings*') ? 'open active' : '' }}">
                <div class="nav-section-header" title="Click to open">
                    <span class="nav-section-label">
                        <svg class="folder-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                        <span class="folder-text-hide">Sistem</span>
                    </span>
                    <svg class="folder-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
                <div class="nav-folder-items">
                    <a href="{{ route('school-calendar.index') }}" title="Kalender Sekolah" class="nav-item {{ request()->routeIs('school-calendar*') ? 'active' : '' }}">
                        <span class="nav-item-label">Kalender Sekolah</span>
                    </a>
                    <a href="{{ route('wifi-networks.index') }}" title="Jaringan WiFi" class="nav-item {{ request()->routeIs('wifi-networks*') ? 'active' : '' }}">
                        <span class="nav-item-label">Jaringan WiFi</span>
                    </a>
                    <a href="{{ route('device-requests.index') }}" title="Permintaan Device" class="nav-item {{ request()->routeIs('device-requests*') ? 'active' : '' }}">
                        <span class="nav-item-label">Permintaan Device</span>
                    </a>
                    <a href="#" title="Pengaturan" class="nav-item {{ request()->routeIs('settings*') ? 'active' : '' }}">
                        <span class="nav-item-label">Pengaturan</span>
                    </a>
                </div>
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









