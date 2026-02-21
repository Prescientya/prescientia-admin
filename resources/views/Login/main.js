/* ============================================================
   PRESCIENTIA – Login Page JS
   Theme management (gear popup) + password toggle + submit spinner
   ============================================================ */

(function () {
    'use strict';

    const STORAGE_MODE  = 'prescentia-theme-mode';
    const STORAGE_COLOR = 'prescentia-theme-color';

    /* ----------------------------------------------------------
       HELPERS
    ---------------------------------------------------------- */
    function getMode()  { return localStorage.getItem(STORAGE_MODE)  || 'light'; }
    function getColor() { return localStorage.getItem(STORAGE_COLOR) || 'blue'; }

    function applyTheme(mode, color) {
        document.body.dataset.mode  = mode;
        document.body.dataset.color = color;
        localStorage.setItem(STORAGE_MODE,  mode);
        localStorage.setItem(STORAGE_COLOR, color);
        syncUI(mode, color);
    }

    function syncUI(mode, color) {
        document.querySelectorAll('[data-login-mode]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.loginMode === mode);
        });
        document.querySelectorAll('[data-login-color]').forEach(sw => {
            sw.classList.toggle('active', sw.dataset.loginColor === color);
        });
    }

    /* ----------------------------------------------------------
       GEAR POPUP
    ---------------------------------------------------------- */
    function initThemePopup() {
        const gearBtn  = document.getElementById('themeGearBtn');
        const popup    = document.getElementById('themePopup');
        const closeBtn = document.getElementById('themePopupClose');

        if (!gearBtn || !popup) return;

        gearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            popup.classList.toggle('open');
        });

        closeBtn && closeBtn.addEventListener('click', () => {
            popup.classList.remove('open');
        });

        document.addEventListener('click', (e) => {
            if (!popup.contains(e.target) && e.target !== gearBtn) {
                popup.classList.remove('open');
            }
        });

        document.querySelectorAll('[data-login-mode]').forEach(btn => {
            btn.addEventListener('click', () => applyTheme(btn.dataset.loginMode, getColor()));
        });

        document.querySelectorAll('[data-login-color]').forEach(sw => {
            sw.addEventListener('click', () => applyTheme(getMode(), sw.dataset.loginColor));
        });
    }

    /* ----------------------------------------------------------
       TOGGLE SHOW/HIDE PASSWORD
    ---------------------------------------------------------- */
    function initPasswordToggle() {
        const toggleBtn = document.getElementById('togglePassword');
        const pwdInput  = document.getElementById('password');
        const eyeIcon   = document.getElementById('eyeIcon');

        const eyeOpen = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
        const eyeClosed = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>`;

        if (!toggleBtn || !pwdInput) return;
        toggleBtn.addEventListener('click', () => {
            const hidden = pwdInput.type === 'password';
            pwdInput.type     = hidden ? 'text' : 'password';
            eyeIcon.innerHTML = hidden ? eyeClosed : eyeOpen;
        });
    }

    /* ----------------------------------------------------------
       SUBMIT SPINNER
    ---------------------------------------------------------- */
    function initSubmitSpinner() {
        const form    = document.getElementById('loginForm');
        const btn     = document.getElementById('btnLogin');
        const text    = btn && btn.querySelector('.btn-text');
        const spinner = document.getElementById('btnSpinner');

        if (!form) return;
        form.addEventListener('submit', () => {
            if (btn)     btn.disabled        = true;
            if (text)    text.textContent    = 'Memproses...';
            if (spinner) spinner.style.display = 'inline-block';
        });
    }

    /* ----------------------------------------------------------
       INIT
    ---------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(getMode(), getColor());
        initThemePopup();
        initPasswordToggle();
        initSubmitSpinner();
    });

})();
    const eyeIcon        = document.getElementById('eyeIcon');

    const eyeOpenSVG = `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
    `;
    const eyeClosedSVG = `
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8
                 a18.45 18.45 0 0 1 5.06-5.94"/>
        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8
                 a18.5 18.5 0 0 1-2.16 3.19"/>
        <line x1="1" y1="1" x2="23" y2="23"/>
    `;

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeIcon.innerHTML  = isHidden ? eyeClosedSVG : eyeOpenSVG;
        });
    }

    // ========================
    //  Loading Spinner on Submit
    // ========================
    const loginForm  = document.getElementById('loginForm');
    const btnLogin   = document.getElementById('btnLogin');
    const btnText    = btnLogin ? btnLogin.querySelector('.btn-text')    : null;
    const btnSpinner = document.getElementById('btnSpinner');

    if (loginForm) {
        loginForm.addEventListener('submit', function () {
            if (btnLogin)   btnLogin.disabled          = true;
            if (btnText)    btnText.textContent         = 'Memproses...';
            if (btnSpinner) btnSpinner.style.display   = 'inline-block';
        });
    }



