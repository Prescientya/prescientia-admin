(function () {
    'use strict';

    /* ── Confirm before approve / reject ─────────────────── */
    let currentForm = null;
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalOldId = document.getElementById('modalOldId');
    const modalNewId = document.getElementById('modalNewId');
    const btnConfirm = document.getElementById('btnConfirmAction');

    // Handle Confirm Buttons
    document.querySelectorAll('.btn-confirm').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            
            const form = btn.closest('form');
            currentForm = form;
            
            const action = btn.getAttribute('data-action');
            const oldId = btn.getAttribute('data-old') || '-';
            const newId = btn.getAttribute('data-new') || '-';

            modalOldId.textContent = oldId;
            modalNewId.textContent = newId;

            if (action === 'approve') {
                modalTitle.textContent = 'Setujui Permintaan';
                modalMessage.textContent = 'Setujui permintaan pergantian device ini? Device ID pengguna akan diperbarui.';
                btnConfirm.className = 'btn btn--primary';
                btnConfirm.textContent = 'Setujui';
            } else {
                modalTitle.textContent = 'Tolak Permintaan';
                modalMessage.textContent = 'Tolak permintaan pergantian device ini?';
                btnConfirm.className = 'btn btn--danger';
                btnConfirm.textContent = 'Tolak';
            }

            // Show Modal
            if (modal) {
                modal.style.display = 'flex';
            }
        });
    });

    // Handle close modal
    document.querySelectorAll('.js-modal-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (modal) {
                modal.style.display = 'none';
            }
            currentForm = null;
        });
    });

    // Handle Confirm Action
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            if (currentForm) {
                currentForm.submit();
            }
        });
    }

    /* ── Auto-submit type filter on change ───────────────── */
    // (already handled via onchange="this.form.submit()" in blade)

    /* ── Search: submit on Enter ─────────────────────────── */
    var searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }

}());
