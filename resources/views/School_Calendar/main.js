/* ============================================================
   SCHOOL CALENDAR – main.js
   ============================================================ */

(function () {
    'use strict';

    /* ── Helpers ─────────────────────────────────────────── */
    const $  = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    /* ── Modal open / close ───────────────────────────────── */
    function openModal(overlay) {
        if (!overlay) return;
        overlay.classList.add('open');
    }

    function closeModal(overlay) {
        if (!overlay) return;
        overlay.classList.remove('open');
    }

    // Open modal buttons
    $$('[data-open-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.openModal;
            const modal = $('#' + target);
            if (modal) openModal(modal);
        });
    });

    // Close buttons
    $$('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.closeModal;
            const modal = $('#' + target);
            if (modal) closeModal(modal);
        });
    });

    // Click outside modal box to close
    $$('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) closeModal(overlay);
        });
    });

    // Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            $$('.modal-overlay.open').forEach(m => closeModal(m));
        }
    });

    /* ── Day click → open Edit Modal ─────────────────────── */
    const editModal     = $('#modalEditEntry');
    const editForm      = $('#editEntryForm');
    const editDateIcon  = $('#editDateIcon');
    const editDateFull  = $('#editDateFull');
    const editDateStatus= $('#editDateStatus');
    const editStatus    = $('#editEntryStatus');
    const editNotes     = $('#editEntryNotes');
    const editNotesWrap = $('#editNotesWrap');

    // Update notes field visibility based on status
    function updateNotesVisibility() {
        if (editStatus && editNotesWrap) {
            editNotesWrap.style.display = (editStatus.value === 'libur') ? 'block' : 'none';
        }
    }

    if (editStatus) {
        editStatus.addEventListener('change', updateNotesVisibility);
    }

    /* Calendar cell clicks */
    $$('.sc-cal-day--clickable').forEach(cell => {
        cell.addEventListener('click', () => {
            const id      = cell.dataset.id;
            const day     = cell.dataset.day;
            const status  = cell.dataset.status;
            const notes   = cell.dataset.notes || '';
            const dateStr = cell.dataset.dateStr;

            // Update form action
            if (editForm) {
                const baseAction = editForm.dataset.baseAction;
                editForm.action = baseAction.replace('__ID__', id);
            }

            // Update date display
            if (editDateIcon) {
                editDateIcon.textContent = day;
                editDateIcon.classList.remove('sc-edit-date-icon--school', 'sc-edit-date-icon--holiday');
                editDateIcon.classList.add(status === 'libur' ? 'sc-edit-date-icon--holiday' : 'sc-edit-date-icon--school');
            }
            if (editDateFull) editDateFull.textContent = dateStr;
            if (editDateStatus) {
                editDateStatus.textContent = status === 'libur' ? 'Hari Libur' : 'Hari Sekolah';
                editDateStatus.style.color = status === 'libur' ? '#dc2626' : '#16a34a';
            }

            // Set form values
            if (editStatus) editStatus.value = status;
            if (editNotes) editNotes.value = notes;
            updateNotesVisibility();

            // Open modal
            openModal(editModal);
        });
    });

    /* ── Generate Year Form - Update description ──────────── */
    const generateYear = $('#generateYear');
    const generateYearDesc = $('#generateYearDesc');

    if (generateYear && generateYearDesc) {
        generateYear.addEventListener('change', function() {
            generateYearDesc.textContent = `Kalender tahun ${this.value} akan dibuat ulang jika sudah ada.`;
        });
    }

    /* ── Submit button spinner ────────────────────────────── */
    $$('form[data-loading]').forEach(form => {
        form.addEventListener('submit', function () {
            const btn = this.querySelector('[type="submit"]');
            if (btn) {
                btn.disabled = true;
                const spinner = btn.querySelector('.spinner');
                const btnText = btn.querySelector('.btn-text');
                if (spinner) spinner.style.display = 'inline-block';
                if (btnText) btnText.style.opacity = '0.6';
            }
        });
    });

    /* ── Auto-dismiss flash alerts ────────────────────────── */
    $$('.alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .5s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        }, 5000);
    });

})();
