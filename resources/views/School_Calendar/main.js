/* ============================================================
   SCHOOL CALENDAR – main.js
   ============================================================ */

(function () {
    'use strict';

    /* ── Helpers ─────────────────────────────────────────── */
    const $  = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    /* ── Month navigation via form submit ─────────────────── */
    const yearSel  = $('#sc-year-select');
    const monthSel = $('#sc-month-select');
    const navForm  = $('#sc-nav-form');

    function submitNav() {
        if (navForm) navForm.submit();
    }

    if (yearSel)  yearSel.addEventListener('change', submitNav);
    if (monthSel) monthSel.addEventListener('change', submitNav);

    // Prev / Next month buttons
    const btnPrev = $('#sc-prev-month');
    const btnNext = $('#sc-next-month');

    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            let m = parseInt(monthSel.value);
            let y = parseInt(yearSel.value);
            m--; if (m < 1) { m = 12; y--; }
            monthSel.value = m;
            yearSel.value  = y;
            navForm.submit();
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', () => {
            let m = parseInt(monthSel.value);
            let y = parseInt(yearSel.value);
            m++; if (m > 12) { m = 1; y++; }
            monthSel.value = m;
            yearSel.value  = y;
            navForm.submit();
        });
    }

    /* ── Day click → open Edit Modal ─────────────────────── */
    const editModal      = $('#modal-edit-day');
    const editForm       = $('#form-edit-day');
    const editDateNum    = $('#edit-date-num');
    const editDateDay    = $('#edit-date-day');
    const editDateFull   = $('#edit-date-full');
    const editNotes      = $('#edit-notes');
    const editNotesGroup = $('#edit-notes-group');
    const btnActive      = $('#btn-status-active');
    const btnHoliday     = $('#btn-status-holiday');
    const hiddenStatus   = $('#input-status');

    let selectedStatus = 'aktif';

    function setStatus(s) {
        selectedStatus = s;
        hiddenStatus.value = s;
        btnActive.classList.toggle('selected-active',  s === 'aktif');
        btnHoliday.classList.toggle('selected-holiday', s === 'libur');
        if (editNotesGroup) {
            editNotesGroup.style.display = (s === 'libur') ? 'flex' : 'none';
        }
    }

    if (btnActive)  btnActive.addEventListener('click',  () => setStatus('aktif'));
    if (btnHoliday) btnHoliday.addEventListener('click', () => setStatus('libur'));

    /* Calendar cell clicks */
    $$('.sc-day[data-id]').forEach(cell => {
        cell.addEventListener('click', openEditForElement.bind(null, cell));
    });

    /* Table row edit-button clicks */
    $$('.sc-edit-btn[data-id]').forEach(btn => {
        btn.addEventListener('click', openEditForElement.bind(null, btn));
    });

    function openEditForElement(el) {
        const id     = el.dataset.id;
        const day    = el.dataset.day;
        const dayname= el.dataset.dayname;
        const full   = el.dataset.full;
        const status = el.dataset.status;
        const notes  = el.dataset.notes || '';

        if (editDateNum)  editDateNum.textContent  = day;
        if (editDateDay)  editDateDay.textContent  = dayname;
        if (editDateFull) editDateFull.textContent = full;
        if (editNotes)    editNotes.value          = notes;

        const base = editForm.dataset.baseUrl;
        editForm.action = base.replace('__ID__', id);

        setStatus(status || 'aktif');
        openModal(editModal);
    }

    /* ── Generate Year Modal ──────────────────────────────── */
    const btnOpenGen = $('#btn-open-generate');
    const modalGen   = $('#modal-generate');

    if (btnOpenGen) {
        btnOpenGen.addEventListener('click', () => openModal(modalGen));
    }

    /* ── Delete Year Modal ────────────────────────────────── */
    const btnOpenDel  = $('#btn-open-delete-year');
    const modalDel    = $('#modal-delete-year');
    const delYearSpan = $('#del-year-span');
    const delForm     = $('#form-delete-year');

    if (btnOpenDel) {
        btnOpenDel.addEventListener('click', () => {
            const yr = yearSel ? parseInt(yearSel.value) : '';
            if (delYearSpan) delYearSpan.textContent = yr;
            // update hidden input inside form
            const hiddenYr = delForm ? delForm.querySelector('input[name="year"]') : null;
            if (hiddenYr) hiddenYr.value = yr;
            openModal(modalDel);
        });
    }

    /* ── Modal open / close ───────────────────────────────── */
    function openModal(overlay) {
        if (!overlay) return;
        overlay.style.display = 'flex';
        requestAnimationFrame(() => overlay.classList.add('open'));
    }

    function closeModal(overlay) {
        if (!overlay) return;
        overlay.classList.remove('open');
        overlay.addEventListener('transitionend', () => {
            overlay.style.display = 'none';
        }, { once: true });
    }

    // Close buttons (.modal-close and data-close-modal)
    $$('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const m = btn.closest('.modal-overlay');
            if (m) closeModal(m);
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

    /* ── Submit button spinner ────────────────────────────── */
    $$('form').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('[type="submit"]');
            if (btn) {
                btn.classList.add('loading');
                btn.disabled = true;
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
