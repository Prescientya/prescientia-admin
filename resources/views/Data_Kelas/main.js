/* ============================================================
   DATA KELAS – main.js
   Module-specific only. Shared utilities → components/global.js
   ============================================================ */
(function () {
    'use strict';

    const { $, $$, openModal } = window.PSC;

    /* ── 1. EDIT MODAL — populate fields ─────────────────── */
    function initEditButtons() {
        $$('[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const { id, kelas, major } = btn.dataset;

                const form = $('#editForm');
                if (!form) return;

                // Update form action
                form.action = form.dataset.baseAction.replace('__ID__', id);

                // Fill fields
                const selKelas = form.querySelector('[name="class"]');
                const inpMajor = form.querySelector('[name="major"]');

                if (selKelas) selKelas.value = kelas;
                if (inpMajor) inpMajor.value = major;

                openModal('modalEditKelas');
            });
        });
    }

    /* ── 4. DELETE MODAL — populate info ─────────────────── */
    function initDeleteButtons() {
        $$('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const { id, kelas, major } = btn.dataset;

                const initial = $('#deleteKelasInitial');
                const name    = $('#deleteKelasName');
                const form    = $('#deleteForm');

                if (initial) initial.textContent = kelas;
                if (name)    name.textContent    = `Kelas ${kelas} – ${major}`;
                if (form)    form.action         = form.dataset.baseAction.replace('__ID__', id);

                openModal('modalDeleteKelas');
            });
        });
    }

    /* ── INIT ─────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        PSC.initActionDropdowns('.dk-action__btn', '.dk-dropdown');
        PSC.initModalClose();
        PSC.initOpenButtons();
        initEditButtons();
        initDeleteButtons();
        PSC.initAlerts();
        PSC.initFormSpinner();
    });

})();
