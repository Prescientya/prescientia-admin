/* ============================================================
   PRESCIENTIA ADMIN – layouts/main.js
   Theme management, sidebar toggle, dropdown
   ============================================================ */

(function () {
    'use strict';

    /* ----------------------------------------------------------
       THEME CONSTANTS
    ---------------------------------------------------------- */
    const STORAGE_MODE  = 'prescentia-theme-mode';
    const STORAGE_COLOR = 'prescentia-theme-color';
    const DEFAULT_MODE  = 'light';
    const DEFAULT_COLOR = 'blue';

    const COLOR_META = {
        blue   : { label: 'Biru',   bg: '#1e3a5f' },
        black  : { label: 'Hitam',  bg: '#111827' },
        white  : { label: 'Putih',  bg: '#d1d5db' },
        purple : { label: 'Ungu',   bg: '#4c1d95' },
        green  : { label: 'Hijau',  bg: '#064e3b' },
    };

    /* ----------------------------------------------------------
       THEME INIT  (also runs inline in <head> to prevent flash)
    ---------------------------------------------------------- */
    function getCurrentMode()  { return localStorage.getItem(STORAGE_MODE)  || DEFAULT_MODE; }
    function getCurrentColor() { return localStorage.getItem(STORAGE_COLOR) || DEFAULT_COLOR; }

    function applyTheme(mode, color) {
        document.body.dataset.mode  = mode;
        document.body.dataset.color = color;
        localStorage.setItem(STORAGE_MODE,  mode);
        localStorage.setItem(STORAGE_COLOR, color);

        // Sync UI controls
        document.querySelectorAll('[data-mode-btn]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.modeBtn === mode);
        });
        document.querySelectorAll('[data-color-swatch]').forEach(sw => {
            sw.classList.toggle('active', sw.dataset.colorSwatch === color);
        });
    }

    /* ----------------------------------------------------------
       SIDEBAR
    ---------------------------------------------------------- */
    const SIDEBAR_COLLAPSED_KEY = 'prescentia-sidebar-collapsed';

    function initSidebar() {
        const wrapper        = document.getElementById('appWrapper');
        const toggleBtn      = document.getElementById('sidebarToggle');
        const overlay        = document.getElementById('sidebarOverlay');

        if (!wrapper || !toggleBtn) return;

        // Restore collapsed state on desktop only
        const isCollapsed = localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === 'true';
        if (isDesktop()) {
            if (isCollapsed) wrapper.classList.add('sidebar-collapsed');
        } else {
            // Mobile: always start with sidebar hidden, no collapsed class
            wrapper.classList.remove('sidebar-collapsed');
        }

        function isDesktop() { return window.innerWidth > 1024; }

        toggleBtn.addEventListener('click', () => {
            if (isDesktop()) {
                // Desktop: collapse/expand
                const collapsed = wrapper.classList.toggle('sidebar-collapsed');
                localStorage.setItem(SIDEBAR_COLLAPSED_KEY, collapsed);
            } else {
                // Mobile: slide in/out
                const open = wrapper.classList.toggle('sidebar-open');
                document.body.style.overflow = open ? 'hidden' : '';
            }
        });

        if (overlay) {
            overlay.addEventListener('click', () => {
                wrapper.classList.remove('sidebar-open');
                document.body.style.overflow = '';
            });
        }

        // Close mobile sidebar on resize to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                wrapper.classList.remove('sidebar-open');
                document.body.style.overflow = '';
            } else {
                // On mobile: remove collapsed class so it doesn't block slideIn
                wrapper.classList.remove('sidebar-collapsed');
                wrapper.classList.remove('sidebar-open');
                document.body.style.overflow = '';
            }
        });
    }

    /* ----------------------------------------------------------
       PROFILE DROPDOWN
    ---------------------------------------------------------- */
    function initProfileDropdown() {
        const trigger  = document.getElementById('profileTrigger');
        const dropdown = document.getElementById('profileDropdown');

        if (!trigger || !dropdown) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.toggle('open');
            trigger.setAttribute('aria-expanded', isOpen);
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });

        // Prevent dropdown from closing on internal click (except logout form/link, or theme controls)
        dropdown.addEventListener('click', (e) => {
            if (e.target.closest('form') || e.target.closest('a')) return;
            if (e.target.closest('[data-mode-btn]') || e.target.closest('[data-color-swatch]')) return;
            e.stopPropagation();
        });
    }

    /* ----------------------------------------------------------
       THEME CONTROLS (mode toggle + color swatches)
       Attach directly to elements so stopPropagation in dropdown
       does not block clicks from reaching the handler.
    ---------------------------------------------------------- */
    function initThemeControls() {
        document.querySelectorAll('[data-mode-btn]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                applyTheme(btn.dataset.modeBtn, getCurrentColor());
            });
        });

        document.querySelectorAll('[data-color-swatch]').forEach(swatch => {
            swatch.addEventListener('click', (e) => {
                e.stopPropagation();
                applyTheme(getCurrentMode(), swatch.dataset.colorSwatch);
            });
        });
    }

    /* ----------------------------------------------------------
       INIT
    ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        // Apply saved theme
        applyTheme(getCurrentMode(), getCurrentColor());

        initSidebar();
        initProfileDropdown();
        initThemeControls();
    });

    // Expose for external use (e.g. login page)
    window.PreTheme = { apply: applyTheme, getMode: getCurrentMode, getColor: getCurrentColor };

})();
