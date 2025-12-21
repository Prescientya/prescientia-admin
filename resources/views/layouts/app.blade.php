<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SekolahKu Admin')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    🎓
                </div>
                <div class="sidebar-title">
                    <h2>SekolahKu</h2>
                    <p>Admin Panel</p>
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ route('admin.students.index') }}" class="menu-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <span class="menu-icon">🎓</span>
                    <span>Data Siswa</span>
                </a>
                
                <a href="{{ route('admin.teachers.index') }}" class="menu-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                    <span class="menu-icon">👥</span>
                    <span>Data Guru</span>
                </a>
                
                <a href="{{ route('admin.classes.index') }}" class="menu-item {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                    <span class="menu-icon">📚</span>
                    <span>Data Kelas</span>
                </a>
                
                <a href="{{ route('admin.attendances.index') }}" class="menu-item {{ request()->routeIs('admin.attendances.*') ? 'active' : '' }}">
                    <span class="menu-icon">📋</span>
                    <span>Data Absensi</span>
                </a>
                
                <a href="{{ route('admin.wifi.index') }}" class="menu-item {{ request()->routeIs('admin.wifi.*') ? 'active' : '' }}">
                    <span class="menu-icon">📶</span>
                    <span>Data WiFi</span>
                </a>
                
                <a href="{{ route('admin.calendar.index') }}" class="menu-item {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                    <span class="menu-icon">📅</span>
                    <span>Kalender</span>
                </a>
                
                <a href="{{ route('admin.login-history.index') }}" class="menu-item {{ request()->routeIs('admin.login-history.*') ? 'active' : '' }}">
                    <span class="menu-icon">🕐</span>
                    <span>Riwayat Login</span>
                </a>
                
                <a href="{{ route('admin.profile.index') }}" class="menu-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <span class="menu-icon">👤</span>
                    <span>Profil</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <span>↪</span>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
