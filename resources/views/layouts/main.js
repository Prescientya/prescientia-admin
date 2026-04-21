/**
 * Prescientia Admin - Main Layout JavaScript
 */
(function() {
    'use strict';

    // ===================== SIDEBAR TOGGLE =====================
    const appWrapper  = document.getElementById('appWrapper');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function isMobile() {
        return window.innerWidth <= 1024;
    }

    function handleModeSwitch() {
        if (!appWrapper) return;
        if (isMobile()) {
            appWrapper.classList.remove('sidebar-collapsed');
            appWrapper.classList.remove('sidebar-open');
        } else {
            appWrapper.classList.remove('sidebar-open');
            if (localStorage.getItem('prescentia-sidebar-collapsed') === '1') {
                appWrapper.classList.add('sidebar-collapsed');
            }
        }
    }

    if (sidebarToggle && appWrapper) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isMobile()) {
                appWrapper.classList.remove('sidebar-collapsed');
                appWrapper.classList.toggle('sidebar-open');
            } else {
                appWrapper.classList.toggle('sidebar-collapsed');
                localStorage.setItem('prescentia-sidebar-collapsed', 
                    appWrapper.classList.contains('sidebar-collapsed') ? '1' : '0');
            }
        });
    }

    if (sidebarOverlay && appWrapper) {
        sidebarOverlay.addEventListener('click', function() {
            appWrapper.classList.remove('sidebar-open');
        });
    }

    handleModeSwitch();

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleModeSwitch, 100);
    });

    // ===================== PROFILE DROPDOWN =====================
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = profileDropdown.classList.toggle('open');
            profileTrigger.setAttribute('aria-expanded', isOpen);
        });

        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
                profileDropdown.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                profileDropdown.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ===================== COLOR UTILITY FUNCTIONS =====================
    
    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    function rgbToHex(r, g, b) {
        return '#' + [r, g, b].map(x => {
            const hex = Math.round(x).toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }).join('');
    }

    function rgbToHsv(r, g, b) {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b);
        let h, s, v = max;
        const d = max - min;
        s = max === 0 ? 0 : d / max;
        if (max === min) {
            h = 0;
        } else {
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        return { h: h * 360, s: s * 100, v: v * 100 };
    }

    function hsvToRgb(h, s, v) {
        h /= 360; s /= 100; v /= 100;
        let r, g, b;
        const i = Math.floor(h * 6);
        const f = h * 6 - i;
        const p = v * (1 - s);
        const q = v * (1 - f * s);
        const t = v * (1 - (1 - f) * s);
        switch (i % 6) {
            case 0: r = v; g = t; b = p; break;
            case 1: r = q; g = v; b = p; break;
            case 2: r = p; g = v; b = t; break;
            case 3: r = p; g = q; b = v; break;
            case 4: r = t; g = p; b = v; break;
            case 5: r = v; g = p; b = q; break;
        }
        return { r: r * 255, g: g * 255, b: b * 255 };
    }

    function generateThemeColors(baseHex) {
        const rgb = hexToRgb(baseHex);
        if (!rgb) return null;
        
        const hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
        
        // Calculate luminance to determine if color is light or dark
        const luminance = (0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b) / 255;
        const isLightColor = luminance > 0.5;
        const isVeryLight = luminance > 0.7;
        const isLowSaturation = hsv.s < 20;
        
        // ========== LIGHT MODE ==========
        let lightSidebarBg, lightAccent, lightAccentRgb;
        
        if (isVeryLight || isLowSaturation) {
            // For very light or low saturation colors, darken sidebar for contrast
            const sidebarHsv = { 
                h: hsv.h || 220, // Default to blue if no hue
                s: Math.max(hsv.s, 15), 
                v: Math.min(hsv.v, 22) 
            };
            const sidebarRgb = hsvToRgb(sidebarHsv.h, sidebarHsv.s, sidebarHsv.v);
            lightSidebarBg = rgbToHex(sidebarRgb.r, sidebarRgb.g, sidebarRgb.b);
            
            // Accent color: use a more saturated/darker version
            const accentHsv = { 
                h: hsv.h || 220, 
                s: Math.max(hsv.s, 45), 
                v: Math.min(hsv.v, 45) 
            };
            const accentRgb = hsvToRgb(accentHsv.h, accentHsv.s, accentHsv.v);
            lightAccent = rgbToHex(accentRgb.r, accentRgb.g, accentRgb.b);
            lightAccentRgb = accentRgb;
        } else if (isLightColor) {
            // Light but saturated colors - darken slightly for sidebar
            const sidebarHsv = { 
                h: hsv.h, 
                s: Math.min(hsv.s * 1.1, 85), 
                v: Math.max(hsv.v * 0.5, 28) 
            };
            const sidebarRgb = hsvToRgb(sidebarHsv.h, sidebarHsv.s, sidebarHsv.v);
            lightSidebarBg = rgbToHex(sidebarRgb.r, sidebarRgb.g, sidebarRgb.b);
            lightAccent = baseHex;
            lightAccentRgb = rgb;
        } else {
            // Normal/dark saturated colors - use as is
            lightSidebarBg = baseHex;
            lightAccent = baseHex;
            lightAccentRgb = rgb;
        }
        
        const lightAccentLight = `rgba(${Math.round(lightAccentRgb.r)},${Math.round(lightAccentRgb.g)},${Math.round(lightAccentRgb.b)},0.1)`;
        
        // ========== DARK MODE ==========
        // Dark mode should ALWAYS have dark backgrounds regardless of selected color
        let darkSidebarBg, darkHeaderBg, darkContentBg, darkAccent;
        
        // Use the hue from the selected color, but force dark values
        const baseHue = hsv.h || 220; // Default to blue if grayscale
        const baseSat = Math.max(hsv.s, 20); // Minimum saturation for some color
        
        // Sidebar: Very dark with slight color tint
        const darkSidebarHsv = { 
            h: baseHue, 
            s: Math.min(baseSat * 0.7, 50), 
            v: 12 // Fixed dark value
        };
        const darkSidebarRgb = hsvToRgb(darkSidebarHsv.h, darkSidebarHsv.s, darkSidebarHsv.v);
        darkSidebarBg = rgbToHex(darkSidebarRgb.r, darkSidebarRgb.g, darkSidebarRgb.b);
        
        // Header: Slightly lighter than sidebar
        const darkHeaderHsv = { 
            h: baseHue, 
            s: Math.min(baseSat * 0.5, 40), 
            v: 18 // Fixed dark value
        };
        const darkHeaderRgb = hsvToRgb(darkHeaderHsv.h, darkHeaderHsv.s, darkHeaderHsv.v);
        darkHeaderBg = rgbToHex(darkHeaderRgb.r, darkHeaderRgb.g, darkHeaderRgb.b);
        
        // Content: Darkest
        const darkContentHsv = { 
            h: baseHue, 
            s: Math.min(baseSat * 0.4, 30), 
            v: 8 // Fixed very dark value
        };
        const darkContentRgb = hsvToRgb(darkContentHsv.h, darkContentHsv.s, darkContentHsv.v);
        darkContentBg = rgbToHex(darkContentRgb.r, darkContentRgb.g, darkContentRgb.b);
        
        // Dark mode accent: bright version of the color for visibility on dark bg
        const darkAccentHsv = { 
            h: baseHue, 
            s: Math.min(Math.max(baseSat, 55), 70), 
            v: 70 // Bright enough to be visible
        };
        const darkAccentRgb = hsvToRgb(darkAccentHsv.h, darkAccentHsv.s, darkAccentHsv.v);
        darkAccent = rgbToHex(darkAccentRgb.r, darkAccentRgb.g, darkAccentRgb.b);
        
        const darkAccentRgbParsed = hexToRgb(darkAccent);
        
        return {
            sidebarBg: lightSidebarBg,
            accent: lightAccent,
            accentLight: lightAccentLight,
            accentRgb: `${lightAccentRgb.r},${lightAccentRgb.g},${lightAccentRgb.b}`,
            sidebarBgDark: darkSidebarBg,
            headerBgDark: darkHeaderBg,
            contentBgDark: darkContentBg,
            accentDark: darkAccent,
            accentLightDark: `rgba(${darkAccentRgbParsed.r},${darkAccentRgbParsed.g},${darkAccentRgbParsed.b},0.12)`,
            accentRgbDark: `${darkAccentRgbParsed.r},${darkAccentRgbParsed.g},${darkAccentRgbParsed.b}`
        };
    }

    function applyCustomColorVariables(colors) {
        const root = document.documentElement;
        root.style.setProperty('--custom-sidebar-bg', colors.sidebarBg);
        root.style.setProperty('--custom-accent', colors.accent);
        root.style.setProperty('--custom-accent-light', colors.accentLight);
        root.style.setProperty('--custom-accent-rgb', colors.accentRgb);
        root.style.setProperty('--custom-sidebar-bg-dark', colors.sidebarBgDark);
        root.style.setProperty('--custom-header-bg-dark', colors.headerBgDark);
        root.style.setProperty('--custom-content-bg-dark', colors.contentBgDark);
        root.style.setProperty('--custom-accent-dark', colors.accentDark);
        root.style.setProperty('--custom-accent-light-dark', colors.accentLightDark);
        root.style.setProperty('--custom-accent-rgb-dark', colors.accentRgbDark);
    }

    // ===================== THEME SYSTEM =====================
    const modeBtns = document.querySelectorAll('[data-mode-btn]');
    const colorPickerSatVal = document.getElementById('colorPickerSatVal');
    const satValCursor = document.getElementById('satValCursor');
    const colorPickerHue = document.getElementById('colorPickerHue');
    const hueCursor = document.getElementById('hueCursor');
    const colorCurrentSwatch = document.getElementById('colorCurrentSwatch');
    const colorPickerHex = document.getElementById('colorPickerHex');
    const colorFavoriteBtn = document.getElementById('colorFavoriteBtn');
    const colorFavorites = document.getElementById('colorFavorites');
    const colorFavoritesList = document.getElementById('colorFavoritesList');

    // Color picker state
    let pickerHue = 210;
    let pickerSat = 68;
    let pickerVal = 37;
    let isDraggingSatVal = false;
    let isDraggingHue = false;

    // Favorites storage
    function getFavorites() {
        try {
            return JSON.parse(localStorage.getItem('prescentia-favorite-colors') || '[]');
        } catch {
            return [];
        }
    }

    function saveFavorites(favorites) {
        localStorage.setItem('prescentia-favorite-colors', JSON.stringify(favorites));
    }

    function addFavorite(hex) {
        const favorites = getFavorites();
        const normalized = hex.toUpperCase();
        if (!favorites.includes(normalized) && favorites.length < 10) {
            favorites.push(normalized);
            saveFavorites(favorites);
        }
        renderFavorites();
        updateFavoriteButton();
    }

    function removeFavorite(hex) {
        let favorites = getFavorites();
        const normalized = hex.toUpperCase();
        favorites = favorites.filter(f => f !== normalized);
        saveFavorites(favorites);
        renderFavorites();
        updateFavoriteButton();
    }

    function isFavorite(hex) {
        const favorites = getFavorites();
        return favorites.includes(hex.toUpperCase());
    }

    function updateFavoriteButton() {
        if (!colorFavoriteBtn) return;
        const rgb = hsvToRgb(pickerHue, pickerSat, pickerVal);
        const hex = rgbToHex(rgb.r, rgb.g, rgb.b).toUpperCase();
        colorFavoriteBtn.classList.toggle('favorited', isFavorite(hex));
    }

    const DEFAULT_COLOR = '#2F6FD6';
    
    function renderFavorites() {
        if (!colorFavoritesList || !colorFavorites) return;
        
        const favorites = getFavorites();
        const currentHex = getCurrentHex().toUpperCase();
        
        // Always show container since we have default color
        colorFavorites.classList.add('has-favorites');
        
        // Start with default color (always first, no remove button)
        let html = `
            <div class="color-favorite-item">
                <button class="color-favorite-swatch color-default-swatch ${DEFAULT_COLOR === currentHex ? 'active' : ''}" 
                        style="background:${DEFAULT_COLOR}" 
                        data-favorite-hex="${DEFAULT_COLOR}"
                        title="Default Blue"></button>
            </div>
        `;
        
        // Add user favorites (with remove button)
        html += favorites.filter(hex => hex !== DEFAULT_COLOR).map(hex => `
            <div class="color-favorite-item">
                <button class="color-favorite-swatch ${hex === currentHex ? 'active' : ''}" 
                        style="background:${hex}" 
                        data-favorite-hex="${hex}"
                        title="${hex}"></button>
                <button class="color-favorite-remove" data-remove-hex="${hex}" title="Hapus">×</button>
            </div>
        `).join('');
        
        colorFavoritesList.innerHTML = html;
        
        // Add click handlers
        colorFavoritesList.querySelectorAll('.color-favorite-swatch').forEach(swatch => {
            swatch.addEventListener('click', function() {
                const hex = this.dataset.favoriteHex;
                setColorFromHex(hex);
                applyColorRealtime();
            });
        });
        
        colorFavoritesList.querySelectorAll('.color-favorite-remove').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                removeFavorite(this.dataset.removeHex);
            });
        });
    }

    function getCurrentHex() {
        const rgb = hsvToRgb(pickerHue, pickerSat, pickerVal);
        return rgbToHex(rgb.r, rgb.g, rgb.b);
    }

    function setColorFromHex(hex) {
        const rgb = hexToRgb(hex);
        if (rgb) {
            const hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
            pickerHue = hsv.h;
            pickerSat = hsv.s;
            pickerVal = hsv.v;
            updateColorPickerUI();
        }
    }

    // Apply color in real-time
    function applyColorRealtime() {
        const hex = getCurrentHex();
        const currentMode = document.body.dataset.mode || 'light';
        
        // Apply theme immediately
        document.body.dataset.color = 'custom';
        localStorage.setItem('prescentia-theme-color', 'custom');
        localStorage.setItem('prescentia-custom-color', hex);
        
        const colors = generateThemeColors(hex);
        if (colors) {
            applyCustomColorVariables(colors);
        }
        
        // Update favorite button state
        updateFavoriteButton();
        
        // Update favorites active state
        renderFavorites();
    }

    // Update color picker UI
    function updateColorPickerUI() {
        // Update satval background hue
        if (colorPickerSatVal) {
            const hueRgb = hsvToRgb(pickerHue, 100, 100);
            colorPickerSatVal.style.backgroundColor = rgbToHex(hueRgb.r, hueRgb.g, hueRgb.b);
        }
        
        // Update cursors
        if (satValCursor) {
            satValCursor.style.left = pickerSat + '%';
            satValCursor.style.top = (100 - pickerVal) + '%';
        }
        
        if (hueCursor) {
            hueCursor.style.top = (pickerHue / 360 * 100) + '%';
        }
        
        // Update current swatch and hex
        const hex = getCurrentHex();
        
        if (colorCurrentSwatch) {
            colorCurrentSwatch.style.background = hex;
        }
        
        if (colorPickerHex && !colorPickerHex.matches(':focus')) {
            colorPickerHex.value = hex.toUpperCase();
        }
        
        updateFavoriteButton();
    }

    // Apply theme (for mode changes)
    function applyTheme(mode) {
        document.body.dataset.mode = mode;
        localStorage.setItem('prescentia-theme-mode', mode);
        
        // Re-apply custom color for new mode
        const hex = getCurrentHex();
        const colors = generateThemeColors(hex);
        if (colors) {
            applyCustomColorVariables(colors);
        }
        
        updateModeUI(mode);
    }

    function updateModeUI(mode) {
        modeBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.modeBtn === mode);
        });
    }

    // Initialize
    function initialize() {
        const savedMode = localStorage.getItem('prescentia-theme-mode') || 'light';
        const lockedColor = DEFAULT_COLOR;
        
        // Color customization is disabled in UI; keep a single default color.
        setColorFromHex(lockedColor);
        localStorage.setItem('prescentia-custom-color', lockedColor);
        localStorage.setItem('prescentia-theme-color', 'custom');
        
        // Apply theme
        document.body.dataset.mode = savedMode;
        document.body.dataset.color = 'custom';
        
        const colors = generateThemeColors(lockedColor);
        if (colors) {
            applyCustomColorVariables(colors);
        }
        
        updateModeUI(savedMode);
        updateColorPickerUI();
        renderFavorites();
    }

    initialize();

    // Mode button clicks
    modeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            applyTheme(this.dataset.modeBtn);
        });
    });

    // ===================== COLOR PICKER INTERACTIONS =====================
    
    // Saturation/Value picker
    if (colorPickerSatVal) {
        function updateSatVal(e) {
            const rect = colorPickerSatVal.getBoundingClientRect();
            pickerSat = Math.max(0, Math.min(100, (e.clientX - rect.left) / rect.width * 100));
            pickerVal = Math.max(0, Math.min(100, 100 - (e.clientY - rect.top) / rect.height * 100));
            updateColorPickerUI();
            applyColorRealtime();
        }
        
        colorPickerSatVal.addEventListener('mousedown', function(e) {
            isDraggingSatVal = true;
            updateSatVal(e);
        });
        
        document.addEventListener('mousemove', function(e) {
            if (isDraggingSatVal) {
                updateSatVal(e);
            }
        });
        
        document.addEventListener('mouseup', function() {
            isDraggingSatVal = false;
        });
        
        // Touch support
        colorPickerSatVal.addEventListener('touchstart', function(e) {
            e.preventDefault();
            isDraggingSatVal = true;
            updateSatVal(e.touches[0]);
        });
        
        document.addEventListener('touchmove', function(e) {
            if (isDraggingSatVal) {
                e.preventDefault();
                updateSatVal(e.touches[0]);
            }
        }, { passive: false });
        
        document.addEventListener('touchend', function() {
            isDraggingSatVal = false;
        });
    }
    
    // Hue slider
    if (colorPickerHue) {
        function updateHue(e) {
            const rect = colorPickerHue.getBoundingClientRect();
            pickerHue = Math.max(0, Math.min(360, (e.clientY - rect.top) / rect.height * 360));
            updateColorPickerUI();
            applyColorRealtime();
        }
        
        colorPickerHue.addEventListener('mousedown', function(e) {
            isDraggingHue = true;
            updateHue(e);
        });
        
        document.addEventListener('mousemove', function(e) {
            if (isDraggingHue) {
                updateHue(e);
            }
        });
        
        document.addEventListener('mouseup', function() {
            isDraggingHue = false;
        });
        
        // Touch support
        colorPickerHue.addEventListener('touchstart', function(e) {
            e.preventDefault();
            isDraggingHue = true;
            updateHue(e.touches[0]);
        });
        
        document.addEventListener('touchmove', function(e) {
            if (isDraggingHue) {
                e.preventDefault();
                updateHue(e.touches[0]);
            }
        }, { passive: false });
        
        document.addEventListener('touchend', function() {
            isDraggingHue = false;
        });
    }
    
    // Hex input
    if (colorPickerHex) {
        colorPickerHex.addEventListener('input', function() {
            let val = this.value.trim();
            if (!val.startsWith('#')) val = '#' + val;
            
            if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                setColorFromHex(val);
                updateColorPickerUI();
                applyColorRealtime();
            }
        });
        
        colorPickerHex.addEventListener('blur', function() {
            this.value = getCurrentHex().toUpperCase();
        });
    }
    
    // Favorite button
    if (colorFavoriteBtn) {
        colorFavoriteBtn.addEventListener('click', function() {
            const hex = getCurrentHex().toUpperCase();
            if (isFavorite(hex)) {
                removeFavorite(hex);
            } else {
                addFavorite(hex);
            }
        });
    }

    // ===================== SIDEBAR FOLDERS (ACCORDION) =====================
    const folderHeaders = document.querySelectorAll('.nav-section-header');
    folderHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.preventDefault();
            this.parentElement.classList.toggle('open');
        });
    });
})();

