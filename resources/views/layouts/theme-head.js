(function(){
    var m = localStorage.getItem('prescentia-theme-mode') || 'light';
    var customHex = localStorage.getItem('prescentia-custom-color') || '#1e3a5f';
    
    document.documentElement.setAttribute('data-pre-mode', m);
    document.documentElement.setAttribute('data-pre-color', 'custom');
    
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(customHex);
    if (result) {
        var r = parseInt(result[1], 16);
        var g = parseInt(result[2], 16);
        var b = parseInt(result[3], 16);
        
        // RGB to HSV
        var rn = r/255, gn = g/255, bn = b/255;
        var max = Math.max(rn, gn, bn), min = Math.min(rn, gn, bn);
        var h, s, v = max, d = max - min;
        s = max === 0 ? 0 : d / max;
        if (max === min) { h = 0; } 
        else {
            switch (max) {
                case rn: h = (gn - bn) / d + (gn < bn ? 6 : 0); break;
                case gn: h = (bn - rn) / d + 2; break;
                case bn: h = (rn - gn) / d + 4; break;
            }
            h /= 6;
        }
        h *= 360; s *= 100; v *= 100;
        
        // Calculate luminance
        var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        var isLightColor = luminance > 0.5;
        var isVeryLight = luminance > 0.7;
        var isLowSaturation = s < 20;
        
        function hsvToRgb(hh, ss, vv) {
            hh /= 360; ss /= 100; vv /= 100;
            var rr, gg, bb, i = Math.floor(hh * 6), f = hh * 6 - i;
            var p = vv * (1 - ss), q = vv * (1 - f * ss), t = vv * (1 - (1 - f) * ss);
            switch (i % 6) {
                case 0: rr = vv; gg = t; bb = p; break;
                case 1: rr = q; gg = vv; bb = p; break;
                case 2: rr = p; gg = vv; bb = t; break;
                case 3: rr = p; gg = q; bb = vv; break;
                case 4: rr = t; gg = p; bb = vv; break;
                case 5: rr = vv; gg = p; bb = q; break;
            }
            return { r: rr * 255, g: gg * 255, b: bb * 255 };
        }
        
        function rgbToHex(rr, gg, bb) {
            return '#' + [rr, gg, bb].map(function(x) {
                var hex = Math.round(x).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
        }
        
        var style = document.documentElement.style;
        var lightSidebarBg, lightAccent, lightAccentR, lightAccentG, lightAccentB;
        
        // Light mode colors
        if (isVeryLight || isLowSaturation) {
            var baseHue = h || 220;
            var sidebarRgb = hsvToRgb(baseHue, Math.max(s, 15), Math.min(v, 22));
            lightSidebarBg = rgbToHex(sidebarRgb.r, sidebarRgb.g, sidebarRgb.b);
            var accentRgb = hsvToRgb(baseHue, Math.max(s, 45), Math.min(v, 45));
            lightAccent = rgbToHex(accentRgb.r, accentRgb.g, accentRgb.b);
            lightAccentR = Math.round(accentRgb.r);
            lightAccentG = Math.round(accentRgb.g);
            lightAccentB = Math.round(accentRgb.b);
        } else if (isLightColor) {
            var sidebarRgb = hsvToRgb(h, Math.min(s * 1.1, 85), Math.max(v * 0.5, 28));
            lightSidebarBg = rgbToHex(sidebarRgb.r, sidebarRgb.g, sidebarRgb.b);
            lightAccent = customHex;
            lightAccentR = r; lightAccentG = g; lightAccentB = b;
        } else {
            lightSidebarBg = customHex;
            lightAccent = customHex;
            lightAccentR = r; lightAccentG = g; lightAccentB = b;
        }
        
        // Dark mode colors - ALWAYS dark backgrounds regardless of selected color
        var baseHue = h || 220;
        var baseSat = Math.max(s, 20);
        
        // Sidebar: Very dark with slight color tint
        var dsbRgb = hsvToRgb(baseHue, Math.min(baseSat * 0.7, 50), 12);
        var darkSidebarBg = rgbToHex(dsbRgb.r, dsbRgb.g, dsbRgb.b);
        
        // Header: Slightly lighter than sidebar
        var dhbRgb = hsvToRgb(baseHue, Math.min(baseSat * 0.5, 40), 18);
        var darkHeaderBg = rgbToHex(dhbRgb.r, dhbRgb.g, dhbRgb.b);
        
        // Content: Darkest
        var dcbRgb = hsvToRgb(baseHue, Math.min(baseSat * 0.4, 30), 8);
        var darkContentBg = rgbToHex(dcbRgb.r, dcbRgb.g, dcbRgb.b);
        
        // Dark mode accent: bright version of the color for visibility
        var daRgb = hsvToRgb(baseHue, Math.min(Math.max(baseSat, 55), 70), 70);
        var darkAccent = rgbToHex(daRgb.r, daRgb.g, daRgb.b);
        
        var darkAccentParsed = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(darkAccent);
        var dar = parseInt(darkAccentParsed[1], 16);
        var dag = parseInt(darkAccentParsed[2], 16);
        var dab = parseInt(darkAccentParsed[3], 16);
        
        style.setProperty('--custom-sidebar-bg', lightSidebarBg);
        style.setProperty('--custom-accent', lightAccent);
        style.setProperty('--custom-accent-light', 'rgba('+lightAccentR+','+lightAccentG+','+lightAccentB+',0.1)');
        style.setProperty('--custom-accent-rgb', lightAccentR+','+lightAccentG+','+lightAccentB);
        style.setProperty('--custom-sidebar-bg-dark', darkSidebarBg);
        style.setProperty('--custom-header-bg-dark', darkHeaderBg);
        style.setProperty('--custom-content-bg-dark', darkContentBg);
        style.setProperty('--custom-accent-dark', darkAccent);
        style.setProperty('--custom-accent-light-dark', 'rgba('+dar+','+dag+','+dab+',0.12)');
        style.setProperty('--custom-accent-rgb-dark', dar+','+dag+','+dab);
    }
})();
