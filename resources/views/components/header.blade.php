<!-- Header Component -->
<header class="admin-header">
    <div class="header-container">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <span>☰</span>
        </button>

        <!-- Left Side: Page Title -->
        <div class="header-left">
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
        </div>

        <!-- Right Side: Notifications & User Menu -->
        <div class="header-right">
            <!-- Notifications -->
            <button class="btn-icon notification-btn" id="notificationBtn" title="Notifikasi">
                <span class="notification-icon">🔔</span>
            </button>

            <!-- User Menu -->
            <div class="user-menu">
                <button class="user-button" id="userMenuBtn">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->admin->name ?? Auth::user()->email, 0, 1) }}
                    </div>
                </button>

                <!-- Dropdown Menu -->
                <div class="dropdown-menu" id="userDropdown">
                    <a href="{{ route('admin.profile.index') }}" class="dropdown-item">
                        <span class="item-icon">👤</span>
                        <span>Profil</span>
                    </a>
                    <a href="{{ route('admin.login-history.index') }}" class="dropdown-item">
                        <span class="item-icon">🕐</span>
                        <span>Riwayat Login</span>
                    </a>
                    <hr class="dropdown-divider">
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item logout-btn">
                            <span class="item-icon">↪</span>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // User Menu Toggle
    document.getElementById('userMenuBtn')?.addEventListener('click', function() {
        document.getElementById('userDropdown').classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const userMenu = document.querySelector('.user-menu');
        if (userMenu && !userMenu.contains(e.target)) {
            document.getElementById('userDropdown').classList.remove('show');
        }
    });

    // Mobile Menu Toggle
    document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
        document.querySelector('.admin-sidebar')?.classList.toggle('show');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.admin-sidebar');
        const toggle = document.getElementById('mobileMenuToggle');
        
        if (window.innerWidth <= 768 && sidebar && !sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });
</script>
