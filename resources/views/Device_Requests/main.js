(function () {
    'use strict';

    /* ── Confirm before approve / reject ─────────────────── */
    document.querySelectorAll('.form-approve').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Setujui permintaan pergantian device ini?\nDevice ID pengguna akan diperbarui.')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.form-reject').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Tolak permintaan pergantian device ini?')) {
                e.preventDefault();
            }
        });
    });

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
