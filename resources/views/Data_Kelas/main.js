/* ============================================================
   DATA KELAS – main.js
   Wires up 3-dot action dropdowns, edit & delete modals.
   ============================================================ */
(function () {
    'use strict';

    if (!window.PSC) {
        console.error('[Data_Kelas] PSC global helper not loaded.');
        return;
    }

    const { $, $$, openModal } = window.PSC;

    /* ── 1. 3-DOT ACTION DROPDOWN ─────────────────────────── */
    PSC.initActionDropdowns('.dk-action__btn', '.dk-dropdown');

    /* ── 2. MODAL CLOSE / OPEN HOOKS ──────────────────────── */
    PSC.initModalClose();
    PSC.initOpenButtons();
    PSC.initAlerts();
    PSC.initFormSpinner();

    /* ── 3. EDIT BUTTON ───────────────────────────────────── */
    function initEditButtons() {
        const form = $('#editForm');
        if (!form) return;
        const baseAction = form.dataset.baseAction || '';

        $$('[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id    = btn.dataset.id;
                const kelas = btn.dataset.kelas || '';
                const major = btn.dataset.major || '';

                btn.closest('.dk-dropdown')?.classList.remove('open');

                form.action = baseAction.replace('__ID__', id);

                const classSelect = form.querySelector('select[name="class"]');
                const majorInput  = form.querySelector('input[name="major"]');
                if (classSelect) classSelect.value = kelas;
                if (majorInput)  majorInput.value  = major;

                openModal('modalEditKelas');
            });
        });
    }

    /* ── 4. DELETE BUTTON ─────────────────────────────────── */
    function initDeleteButtons() {
        const form = $('#deleteForm');
        if (!form) return;
        const baseAction = form.dataset.baseAction || '';

        $$('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id    = btn.dataset.id;
                const kelas = btn.dataset.kelas || '';
                const major = btn.dataset.major || '';
                const fullName = 'Kelas ' + kelas + (major ? ' – ' + major : '');

                btn.closest('.dk-dropdown')?.classList.remove('open');

                form.action = baseAction.replace('__ID__', id);

                const nameEl = $('#deleteKelasName');
                const initEl = $('#deleteKelasInitial');
                if (nameEl) nameEl.textContent = fullName;
                if (initEl) initEl.textContent = (kelas || '?').toString().charAt(0);

                openModal('modalDeleteKelas');
            });
        });
    }

    initEditButtons();
    initDeleteButtons();
})();
