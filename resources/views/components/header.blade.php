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
            <!-- Notification Wrapper -->
            <div class="notification-wrapper">
                <!-- Notifications -->
                <button class="btn-icon notification-btn" id="notificationBtn" title="Notifikasi">
                    <span class="notification-icon">🔔</span>
                    @if($recentActivities && count($recentActivities) > 0)
                        <span class="notification-badge">{{ count($recentActivities) }}</span>
                    @endif
                </button>

                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h3>Aktivitas Terbaru</h3>
                    <span class="notification-count">{{ $recentActivities ? count($recentActivities) : 0 }}</span>
                </div>
                <div class="notification-list">
                    @if($recentActivities && count($recentActivities) > 0)
                        @foreach($recentActivities as $activity)
                            <div class="notification-item" style="border-left: 4px solid {{ $activity->color ?? '#3B82F6' }};">
                                <div class="notification-content">
                                    <p class="notification-title">{{ $activity->title ?? 'Aktivitas' }}</p>
                                    <p class="notification-time">{{ $activity->description ?? $activity->time_ago ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="notification-empty">
                            <p>Tidak ada aktivitas terbaru</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- User Menu -->
            <div class="user-menu">
                <button class="user-button" id="userMenuBtn">
                    <div class="user-avatar">
                        @php
                            $user = Auth::user();
                            $name = $user->email;
                            
                            // Get first letter
                            $letter = !empty($name) ? strtoupper(substr($name, 0, 1)) : 'U';
                        @endphp
                        <span title="Debug: {{ $name }} | Email: {{ $user->email }}">{{ $letter }}</span>
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
    // Notification Dropdown Toggle
    document.getElementById('notificationBtn')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('show');
        document.getElementById('userDropdown').classList.remove('show');
    });

    // User Menu Toggle
    document.getElementById('userMenuBtn')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
        document.getElementById('notificationDropdown').classList.remove('show');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const notificationDropdown = document.getElementById('notificationDropdown');
        const userDropdown = document.getElementById('userDropdown');
        const header = document.querySelector('.header-right');
        
        if (header && !header.contains(e.target)) {
            notificationDropdown?.classList.remove('show');
            userDropdown?.classList.remove('show');
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
