<!-- Sidebar Component -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('assets/icons/home.png') }}" alt="Logo" width="28" height="28">
        </div>
        <div class="sidebar-title">
            <h2>SekolahKu</h2>
            <p>Admin Panel</p>
        </div>
    </div>

    <nav class="sidebar-menu">
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
            <img src="{{ asset('assets/icons/home.png') }}" alt="Dashboard" class="menu-icon">
            <span class="menu-text">Dashboard</span>
        </a>
        
        <a href="{{ route('admin.students.index') }}" class="menu-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" title="Data Siswa">
            <img src="{{ asset('assets/icons/Siswa.png') }}" alt="Siswa" class="menu-icon">
            <span class="menu-text">Data Siswa</span>
        </a>
        
        <a href="{{ route('admin.teachers.index') }}" class="menu-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" title="Data Guru">
            <img src="{{ asset('assets/icons/teacher.png') }}" alt="Guru" class="menu-icon">
            <span class="menu-text">Data Guru</span>
        </a>
        
        <a href="{{ route('admin.teached-classes.index') }}" class="menu-item {{ request()->routeIs('admin.teached-classes.*') ? 'active' : '' }}" title="Setting Guru Pengajar">
            <img src="{{ asset('assets/icons/teacher.png') }}" alt="Setting Guru Pengajar" class="menu-icon">
            <span class="menu-text">kelola Guru Pengajar</span>
        </a>
        
        <a href="{{ route('admin.classes.index') }}" class="menu-item {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}" title="Data Kelas">
            <img src="{{ asset('assets/icons/open-book.png') }}" alt="Kelas" class="menu-icon">
            <span class="menu-text">Data Kelas</span>
        </a>
        
        <a href="{{ route('admin.attendances.index') }}" class="menu-item {{ request()->routeIs('admin.attendances.*') ? 'active' : '' }}" title="Data Absensi">
            <img src="{{ asset('assets/icons/list.png') }}" alt="Absensi" class="menu-icon">
            <span class="menu-text">Data Absensi</span>
        </a>
        
        <a href="{{ route('admin.wifi.index') }}" class="menu-item {{ request()->routeIs('admin.wifi.*') ? 'active' : '' }}" title="Data WiFi">
            <img src="{{ asset('assets/icons/wifi.png') }}" alt="WiFi" class="menu-icon">
            <span class="menu-text">Data WiFi</span>
        </a>
        
        <a href="{{ route('admin.calendar.index') }}" class="menu-item {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}" title="Kalender">
            <img src="{{ asset('assets/icons/holiday.png') }}" alt="Kalender" class="menu-icon">
            <span class="menu-text">Kalender</span>
        </a>
        
        <a href="{{ route('admin.login-history.index') }}" class="menu-item {{ request()->routeIs('admin.login-history.*') ? 'active' : '' }}" title="Riwayat Login">
            <img src="{{ asset('assets/icons/history.png') }}" alt="History" class="menu-icon">
            <span class="menu-text">Riwayat Login</span>
        </a>
        
        <a href="{{ route('admin.mbg-officers.index') }}" class="menu-item {{ request()->routeIs('admin.mbg-officers.*') ? 'active' : '' }}" title="Petugas MBG">
            <img src="{{ asset('assets/icons/list.png') }}" alt="Petugas MBG" class="menu-icon">
            <span class="menu-text">Petugas MBG</span>
        </a>
        
        <a href="{{ route('admin.profile.index') }}" class="menu-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" title="Profil">
            <img src="{{ asset('assets/icons/user.png') }}" alt="Profil" class="menu-icon">
            <span class="menu-text">Profil</span>
        </a>
    </nav>
</aside>
