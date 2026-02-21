<!-- Sidebar Component -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('assets/images/Logo_SMK.png') }}" alt="Logo">
        </div>
        <div class="sidebar-title">
            <h2>Prescientia</h2>
        </div>
    </div>

    <nav class="sidebar-menu">
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
            <img src="{{ asset('assets/icons/home.png') }}" alt="Dashboard" class="menu-icon">
            <span class="menu-text">Dashboard</span>
        </a>
        
        <!-- Master Data Submenu -->
        <div class="menu-item has-submenu" onclick="toggleSubmenu(this)" title="Master Data">
            <img src="{{ asset('assets/icons/open-book.png') }}" alt="Master Data" class="menu-icon">
            <span class="menu-text">Master Data</span>
            <span class="submenu-arrow">▼</span>
        </div>
        <div class="submenu">
            <a href="{{ route('admin.students.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" title="Data Siswa">
                <span class="submenu-text">Data Siswa</span>
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" title="Data Guru">
                <span class="submenu-text">Data Guru</span>
            </a>
            <a href="{{ route('admin.classes.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}" title="Data Kelas">
                <span class="submenu-text">Data Kelas</span>
            </a>
            <a href="{{ route('admin.mbg-officers.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.mbg-officers.*') ? 'active' : '' }}" title="Petugas MBG">
                <span class="submenu-text">Petugas MBG</span>
            </a>
            <a href="{{ route('admin.wifi.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.wifi.*') ? 'active' : '' }}" title="Data WiFi">
                <span class="submenu-text">Data WiFi</span>
            </a>
            <a href="{{ route('admin.calendar.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}" title="Kalender">
                <span class="submenu-text">Kalender</span>
            </a>
        </div>
        
        <!-- Kelola Waktu Belajar Submenu -->
        <div class="menu-item has-submenu" onclick="toggleSubmenu(this)" title="Kelola Waktu Belajar">
            <img src="{{ asset('assets/icons/open-book.png') }}" alt="Kelola Waktu Belajar" class="menu-icon">
            <span class="menu-text">Kelola Waktu Belajar</span>
            <span class="submenu-arrow">▼</span>
        </div>
        <div class="submenu">
            <a href="{{ route('admin.teached-classes.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.teached-classes.*') ? 'active' : '' }}" title="Kelola Guru Pengajar">
                <span class="submenu-text">Kelola Guru Pengajar</span>
            </a>
            <a href="{{ route('admin.subjects.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" title="Mata Pelajaran">
                <span class="submenu-text">Mata Pelajaran</span>
            </a>
            <a href="{{ route('admin.class-periods.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.class-periods.*') ? 'active' : '' }}" title="Jam Pelajaran">
                <span class="submenu-text">Jam Pelajaran</span>
            </a>
        </div>
        
        <!-- Absen Role Submenu -->
        <div class="menu-item has-submenu" onclick="toggleSubmenu(this)" title="Absen Role">
            <img src="{{ asset('assets/icons/list.png') }}" alt="Absen Role" class="menu-icon">
            <span class="menu-text">Absen Role</span>
            <span class="submenu-arrow">▼</span>
        </div>
        <div class="submenu">
            <a href="{{ route('admin.attendances.students') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.attendances.students') ? 'active' : '' }}" title="Absensi Siswa">
                <span class="submenu-text">Absensi Siswa</span>
            </a>
            <a href="{{ route('admin.attendances.teachers') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.attendances.teachers') ? 'active' : '' }}" title="Absensi Guru">
                <span class="submenu-text">Absensi Guru</span>
            </a>
            <a href="{{ route('admin.login-history.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.login-history.*') ? 'active' : '' }}" title="Riwayat Login">
                <span class="submenu-text">Riwayat Login</span>
            </a>
        </div>
        
        <div class="menu-item has-submenu" onclick="toggleSubmenu(this)" title="Akun">
            <img src="{{ asset('assets/icons/user.png') }}" alt="Akun" class="menu-icon">
            <span class="menu-text">Akun</span>
            <span class="submenu-arrow">▼</span>
        </div>
        <div class="submenu">
            <a href="{{ route('admin.profile.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" title="Profil">
                <span class="submenu-text">Profil</span>
            </a>
            <a href="{{ route('admin.device-accounts.index') }}" class="submenu-item sidebar-link {{ request()->routeIs('admin.device-accounts.*') ? 'active' : '' }}" title="Device Akun">
                <span class="submenu-text">Device Akun</span>
            </a>
        </div>
    </nav>
</aside>

<script>
// Toggle submenu functionality
function toggleSubmenu(element) {
    const submenu = element.nextElementSibling;
    const arrow = element.querySelector('.submenu-arrow');
    const isOpen = submenu.classList.contains('open');
    const menuText = element.querySelector('.menu-text').textContent;
    
    // Close all other submenus
    document.querySelectorAll('.submenu.open').forEach(menu => {
        if (menu !== submenu) {
            menu.classList.remove('open');
            menu.previousElementSibling.classList.remove('active');
            menu.previousElementSibling.querySelector('.submenu-arrow').textContent = '▼';
        }
    });
    
    // Toggle current submenu
    if (isOpen) {
        submenu.classList.remove('open');
        element.classList.remove('active');
        arrow.textContent = '▼';
        localStorage.removeItem('activeSubmenu');
    } else {
        submenu.classList.add('open');
        element.classList.add('active');
        arrow.textContent = '▲';
        // Save state ke localStorage
        localStorage.setItem('activeSubmenu', menuText);
    }
}

// Auto-open submenu based on current route or saved state
document.addEventListener('DOMContentLoaded', function() {
    const activeSubmenuItem = document.querySelector('.submenu-item.active');
    
    if (activeSubmenuItem) {
        // If there's an active menu item, open its parent submenu
        const submenu = activeSubmenuItem.closest('.submenu');
        const parentMenu = submenu.previousElementSibling;
        submenu.classList.add('open');
        parentMenu.classList.add('active');
        parentMenu.querySelector('.submenu-arrow').textContent = '▲';
        
        // Save to localStorage
        const menuText = parentMenu.querySelector('.menu-text').textContent;
        localStorage.setItem('activeSubmenu', menuText);
    } else {
        // If no active item but localStorage has saved submenu, restore it
        const savedSubmenu = localStorage.getItem('activeSubmenu');
        if (savedSubmenu) {
            const parentMenus = document.querySelectorAll('.menu-item.has-submenu');
            parentMenus.forEach(menu => {
                if (menu.querySelector('.menu-text').textContent === savedSubmenu) {
                    const submenu = menu.nextElementSibling;
                    submenu.classList.add('open');
                    menu.classList.add('active');
                    menu.querySelector('.submenu-arrow').textContent = '▲';
                }
            });
        }
    }
});
</script>
