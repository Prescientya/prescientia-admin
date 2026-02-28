/**
 * Prescientia Admin - Main Layout JavaScript
 */
(function() {
    'use strict';

    // ===================== SIDEBAR TOGGLE =====================
    const appWrapper  = document.getElementById('appWrapper');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Check if we're on mobile
    function isMobile() {
        return window.innerWidth <= 1024;
    }

    // Handle mode switch between mobile and desktop
    function handleModeSwitch() {
        if (isMobile()) {
            // Mobile: remove collapsed class, sidebar will be hidden by CSS transform
            appWrapper.classList.remove('sidebar-collapsed');
            appWrapper.classList.remove('sidebar-open');
        } else {
            // Desktop: close mobile sidebar, restore collapsed preference
            appWrapper.classList.remove('sidebar-open');
            if (localStorage.getItem('prescentia-sidebar-collapsed') === '1') {
                appWrapper.classList.add('sidebar-collapsed');
            }
        }
    }

    // Toggle sidebar
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isMobile()) {
                // Mobile: toggle sidebar-open class (full width sidebar)
                // Ensure collapsed is removed for mobile
                appWrapper.classList.remove('sidebar-collapsed');
                appWrapper.classList.toggle('sidebar-open');
            } else {
                // Desktop: toggle sidebar-collapsed class
                appWrapper.classList.toggle('sidebar-collapsed');
                // Save preference
                localStorage.setItem('prescentia-sidebar-collapsed', 
                    appWrapper.classList.contains('sidebar-collapsed') ? '1' : '0');
            }
        });
    }

    // Close sidebar when clicking overlay (mobile)
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            appWrapper.classList.remove('sidebar-open');
        });
    }

    // Initialize: set correct state based on current screen size
    handleModeSwitch();

    // Handle resize: switch modes appropriately
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleModeSwitch, 100);
    });

    // ===================== PROFILE DROPDOWN =====================
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileTrigger && profileDropdown) {
        // Toggle dropdown on click
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = profileDropdown.classList.toggle('open');
            profileTrigger.setAttribute('aria-expanded', isOpen);
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
                profileDropdown.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                profileDropdown.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ===================== THEME SYSTEM =====================
    const modeBtns = document.querySelectorAll('[data-mode-btn]');
    const colorSwatches = document.querySelectorAll('[data-color-swatch]');

    // Apply theme
    function applyTheme(mode, color) {
        document.body.dataset.mode = mode;
        document.body.dataset.color = color;
        localStorage.setItem('prescentia-theme-mode', mode);
        localStorage.setItem('prescentia-theme-color', color);
        updateThemeUI(mode, color);
    }

    // Update UI to reflect current theme
    function updateThemeUI(mode, color) {
        modeBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.modeBtn === mode);
        });
        colorSwatches.forEach(swatch => {
            swatch.classList.toggle('active', swatch.dataset.colorSwatch === color);
        });
    }

    // Initialize theme UI
    const currentMode = localStorage.getItem('prescentia-theme-mode') || 'light';
    const currentColor = localStorage.getItem('prescentia-theme-color') || 'blue';
    updateThemeUI(currentMode, currentColor);

    // Mode button clicks
    modeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const newMode = this.dataset.modeBtn;
            const currentColor = document.body.dataset.color || 'blue';
            applyTheme(newMode, currentColor);
        });
    });

    // Color swatch clicks
    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', function() {
            const newColor = this.dataset.colorSwatch;
            const currentMode = document.body.dataset.mode || 'light';
            applyTheme(currentMode, newColor);
        });
    });

})();
