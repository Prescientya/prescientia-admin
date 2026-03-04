/* ============================================================
   PRESCIENTIA ADMIN – Shared Utilities (global.js)
   ============================================================ */

(function () {
    'use strict';

    /* ── Core Helpers ────────────────────────────────────── */
    const $  = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    /* ── Modal Functions ─────────────────────────────────── */
    function openModal(modalId) {
        const overlay = typeof modalId === 'string' ? $('#' + modalId) : modalId;
        if (!overlay) return;
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const overlay = typeof modalId === 'string' ? $('#' + modalId) : modalId;
        if (!overlay) return;
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        // Check if there are other open modals
        if (!$('.modal-overlay.open')) {
            document.body.style.overflow = '';
        }
    }

    function initModalClose() {
        // Close buttons with data-close-modal attribute (explicit target)
        $$('[data-close-modal]').forEach(btn => {
            if (btn.dataset._pscCloseBound) return;
            btn.dataset._pscCloseBound = '1';
            btn.addEventListener('click', () => {
                const target = btn.dataset.closeModal;
                closeModal(target);
            });
        });

        // Close buttons with modal-close class but no data-close-modal
        // → find nearest parent .modal-overlay and close it
        $$('.modal-close:not([data-close-modal])').forEach(btn => {
            if (btn.dataset._pscCloseBound) return;
            btn.dataset._pscCloseBound = '1';
            btn.addEventListener('click', () => {
                const overlay = btn.closest('.modal-overlay');
                if (overlay) closeModal(overlay);
            });
        });

        // Click outside modal box (on overlay) to close
        $$('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeModal(overlay);
            });
        });

        // Escape key to close all open modals
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                $$('.modal-overlay.open').forEach(m => closeModal(m));
            }
        });
    }

    /* ── Open Modal Buttons ──────────────────────────────── */
    function initOpenButtons() {
        $$('[data-open-modal]').forEach(btn => {
            // Avoid double-binding
            if (btn.dataset._pscBound) return;
            btn.dataset._pscBound = '1';
            btn.addEventListener('click', e => {
                e.preventDefault();
                const target = btn.dataset.openModal;
                openModal(target);
            });
        });
    }

    /* ── Toast Notifications ─────────────────────────────── */
    function toast(message, type = 'info', duration = 4000) {
        // Remove existing toast container if exists
        let container = $('#psc-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'psc-toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }

        const toastEl = document.createElement('div');
        toastEl.className = `psc-toast psc-toast--${type}`;
        
        // Icon based on type
        const icons = {
            success: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
            error: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
            warning: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
            info: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`
        };

        const colors = {
            success: { bg: '#dcfce7', border: '#16a34a', text: '#166534', icon: '#16a34a' },
            error: { bg: '#fee2e2', border: '#dc2626', text: '#991b1b', icon: '#dc2626' },
            warning: { bg: '#fef9c3', border: '#ca8a04', text: '#854d0e', icon: '#ca8a04' },
            info: { bg: '#dbeafe', border: '#2563eb', text: '#1e40af', icon: '#2563eb' }
        };

        const c = colors[type] || colors.info;
        toastEl.style.cssText = `
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: ${c.bg};
            border: 1px solid ${c.border};
            border-radius: 8px;
            color: ${c.text};
            font-size: 0.875rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            animation: pscToastIn 0.3s ease;
        `;

        toastEl.innerHTML = `
            <span style="color:${c.icon};flex-shrink:0;">${icons[type] || icons.info}</span>
            <span style="flex:1;">${message}</span>
            <button type="button" style="background:none;border:none;cursor:pointer;color:${c.text};opacity:0.6;padding:0;display:flex;" onclick="this.parentElement.remove()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;

        container.appendChild(toastEl);

        // Auto-dismiss
        if (duration > 0) {
            setTimeout(() => {
                toastEl.style.animation = 'pscToastOut 0.3s ease forwards';
                setTimeout(() => toastEl.remove(), 300);
            }, duration);
        }
    }

    // Add toast animation styles
    if (!$('#psc-toast-styles')) {
        const style = document.createElement('style');
        style.id = 'psc-toast-styles';
        style.textContent = `
            @keyframes pscToastIn {
                from { opacity: 0; transform: translateX(100%); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes pscToastOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100%); }
            }
        `;
        document.head.appendChild(style);
    }

    /* ── Flash Alerts Auto-dismiss ───────────────────────── */
    function initAlerts() {
        $$('.alert').forEach(el => {
            // Skip if already initialized
            if (el.dataset._pscAlertInit) return;
            el.dataset._pscAlertInit = '1';

            setTimeout(() => {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }, 5000);
        });
    }

    /* ── Action Dropdowns ────────────────────────────────── */
    function initActionDropdowns(btnSelector, dropdownSelector) {
        const btns = $$(btnSelector);
        const dropdowns = $$(dropdownSelector);

        btns.forEach(btn => {
            // Avoid double-binding
            if (btn.dataset._pscDropdownBound) return;
            btn.dataset._pscDropdownBound = '1';

            btn.addEventListener('click', e => {
                e.stopPropagation();
                const dropdown = btn.nextElementSibling;
                if (!dropdown) return;

                // Close other dropdowns
                dropdowns.forEach(d => {
                    if (d !== dropdown) d.classList.remove('open');
                });

                // Toggle current
                const wasOpen = dropdown.classList.contains('open');
                dropdown.classList.toggle('open');

                // Position the dropdown if it just opened
                if (!wasOpen) {
                    const btnRect = btn.getBoundingClientRect();
                    const dropdownHeight = dropdown.offsetHeight || 150;
                    const viewportHeight = window.innerHeight;
                    
                    // Default: position below and right-aligned to button
                    let top = btnRect.bottom + 4;
                    let left = btnRect.right - dropdown.offsetWidth;
                    
                    // If dropdown would go below viewport, show above
                    if (top + dropdownHeight > viewportHeight - 20) {
                        top = btnRect.top - dropdownHeight - 4;
                    }
                    
                    // Ensure dropdown doesn't go off left side
                    if (left < 10) {
                        left = btnRect.left;
                    }
                    
                    dropdown.style.top = top + 'px';
                    dropdown.style.left = left + 'px';
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            dropdowns.forEach(d => d.classList.remove('open'));
        });

        // Prevent dropdown content clicks from closing
        dropdowns.forEach(d => {
            d.addEventListener('click', e => e.stopPropagation());
        });
    }

    /* ── Form Submit Spinner ─────────────────────────────── */
    function initFormSpinner() {
        $$('form[data-loading]').forEach(form => {
            // Avoid double-binding
            if (form.dataset._pscSpinnerBound) return;
            form.dataset._pscSpinnerBound = '1';

            form.addEventListener('submit', function () {
                const btn = this.querySelector('[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    const spinner = btn.querySelector('.spinner');
                    const btnText = btn.querySelector('.btn-text');
                    if (spinner) spinner.classList.add('active');
                    if (btnText) btnText.style.opacity = '0.6';
                }
            });
        });
    }

    /* ── Export to window.PSC ────────────────────────────── */
    window.PSC = {
        $,
        $$,
        openModal,
        closeModal,
        initModalClose,
        initOpenButtons,
        initAlerts,
        toast,
        initActionDropdowns,
        initFormSpinner
    };

    /* ── Auto-init on DOMContentLoaded ───────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        initModalClose();
        initOpenButtons();
        initAlerts();
        initFormSpinner();
    });

})();
