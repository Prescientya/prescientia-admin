/* ============================================================
   GLOBAL – Shared UI utilities  (window.PSC)
   Loaded once in layouts/app.blade.php before @stack('scripts').
   Module JS files call PSC.* instead of re-implementing these.
   ============================================================ */
window.PSC = (function () {
    'use strict';

    /* ── DOM helpers ──────────────────────────────────────── */
    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

    /* ── Modal ────────────────────────────────────────────── */
    function openModal(id)  { $('#' + id)?.classList.add('open');    }
    function closeModal(id) { $('#' + id)?.classList.remove('open'); }

    function initModalClose() {
        $$('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', () => closeModal(btn.dataset.closeModal));
        });
        $$('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) overlay.classList.remove('open');
            });
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                $$('.modal-overlay.open').forEach(o => o.classList.remove('open'));
            }
        });
    }

    function initOpenButtons() {
        $$('[data-open-modal]').forEach(el => {
            el.addEventListener('click', () => openModal(el.dataset.openModal));
        });
    }

    /* ── Action Dropdown (3-dot) ──────────────────────────── */
    /**
     * @param {string} btnSel  CSS selector for the trigger button, e.g. '.ds-action__btn'
     * @param {string} dropSel CSS selector for the dropdown panel,  e.g. '.ds-dropdown'
     *
     * The dropdown panel must be the immediate nextElementSibling of the button.
     */
    function initActionDropdowns(btnSel, dropSel) {
        let currentDrop = null;

        function positionDrop(btn, drop) {
            const rect  = btn.getBoundingClientRect();
            const dropW = drop.offsetWidth || 170;
            let left = rect.right - dropW;
            let top  = rect.bottom + 4;
            if (left < 8) left = 8;
            if (top + 200 > window.innerHeight) top = rect.top - 4 - (drop.offsetHeight || 140);
            drop.style.top  = top  + 'px';
            drop.style.left = left + 'px';
        }

        $$(btnSel).forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                const drop   = btn.nextElementSibling;
                const isOpen = drop.classList.contains('open');
                if (currentDrop && currentDrop !== drop) currentDrop.classList.remove('open');
                if (!isOpen) {
                    positionDrop(btn, drop);
                    drop.classList.add('open');
                    currentDrop = drop;
                } else {
                    drop.classList.remove('open');
                    currentDrop = null;
                }
            });
        });

        document.addEventListener('click', () => {
            if (currentDrop) { currentDrop.classList.remove('open'); currentDrop = null; }
        });
        window.addEventListener('scroll', () => {
            if (currentDrop) { currentDrop.classList.remove('open'); currentDrop = null; }
        }, true);
        window.addEventListener('resize', () => {
            if (currentDrop) { currentDrop.classList.remove('open'); currentDrop = null; }
        });
    }

    /* ── Alerts (auto-dismiss) ────────────────────────────── */
    function initAlerts() {
        $$('.alert').forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity    = '0';
                setTimeout(() => el.remove(), 500);
            }, 5000);
        });
    }

    /* ── Form Submit Spinner ──────────────────────────────── */
    function initFormSpinner() {
        $$('form[data-loading]').forEach(form => {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('[type="submit"]');
                if (btn) btn.classList.add('loading');
            });
        });
    }

    /* ── Toast Notification ────────────────────────────── */
    function toast(message, type = 'success') {
        const container = document.getElementById('psc-toast-container')
            || (() => {
                const el = document.createElement('div');
                el.id = 'psc-toast-container';
                document.body.appendChild(el);
                return el;
            })();

        const el = document.createElement('div');
        el.className = `psc-toast psc-toast--${type}`;
        el.textContent = message;
        container.appendChild(el);

        requestAnimationFrame(() => el.classList.add('psc-toast--in'));

        setTimeout(() => {
            el.classList.remove('psc-toast--in');
            el.addEventListener('transitionend', () => el.remove(), { once: true });
        }, 3000);
    }

    /* ── Public API ──────────────────────────────────── */
    return {
        $, $$,
        openModal, closeModal,
        initModalClose,
        initOpenButtons,
        initActionDropdowns,
        initAlerts,
        initFormSpinner,
        toast,
    };
})();
