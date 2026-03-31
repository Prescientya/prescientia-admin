(function(){
    var m = localStorage.getItem('prescentia-theme-mode') || 'light';
    // Always use custom color mode now
    var customHex = localStorage.getItem('prescentia-custom-color') || '#1e3a5f';
    
    document.documentElement.setAttribute('data-pre-mode', m);
    document.documentElement.setAttribute('data-pre-color', 'custom');
    
    // Pre-apply custom color CSS variables to prevent flash
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
        
        // HSV to RGB helper
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
        
        // Generate dark variants
        var darkSidebarBgRgb = hsvToRgb(h, Math.min(s * 0.9, 70), Math.max(v * 0.35, 10));
        var darkHeaderBgRgb = hsvToRgb(h, Math.min(s * 0.7, 50), Math.max(v * 0.5, 15));
        var darkContentBgRgb = hsvToRgb(h, Math.min(s * 0.6, 40), Math.max(v * 0.25, 5));
        var darkAccentRgb = hsvToRgb(h, Math.min(s, 70), Math.min(v * 1.5, 85));
        
        var style = document.documentElement.style;
        style.setProperty('--custom-sidebar-bg', customHex);
        style.setProperty('--custom-accent', customHex);
        style.setProperty('--custom-accent-light', 'rgba('+r+','+g+','+b+',0.1)');
        style.setProperty('--custom-accent-rgb', r+','+g+','+b);
        style.setProperty('--custom-sidebar-bg-dark', rgbToHex(darkSidebarBgRgb.r, darkSidebarBgRgb.g, darkSidebarBgRgb.b));
        style.setProperty('--custom-header-bg-dark', rgbToHex(darkHeaderBgRgb.r, darkHeaderBgRgb.g, darkHeaderBgRgb.b));
        style.setProperty('--custom-content-bg-dark', rgbToHex(darkContentBgRgb.r, darkContentBgRgb.g, darkContentBgRgb.b));
        style.setProperty('--custom-accent-dark', rgbToHex(darkAccentRgb.r, darkAccentRgb.g, darkAccentRgb.b));
        var dar = Math.round(darkAccentRgb.r), dag = Math.round(darkAccentRgb.g), dab = Math.round(darkAccentRgb.b);
        style.setProperty('--custom-accent-light-dark', 'rgba('+dar+','+dag+','+dab+',0.12)');
        style.setProperty('--custom-accent-rgb-dark', dar+','+dag+','+dab);
    }
})();
