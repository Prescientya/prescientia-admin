/* ============================================================
   DATA SISWA – main.js
   Module-specific only. Shared utilities → components/global.js
   ============================================================ */
(function () {
    'use strict';

    const { $, $$, openModal, closeModal } = window.PSC;

    /* ── 1. TABS inside modal ─────────────────────────────── */
    function initTabs() {
        $$('.modal-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const group  = btn.closest('[data-tabs]');
                const target = btn.dataset.tab;

                // Deactivate all in this group
                $$('.modal-tab-btn', group).forEach(b  => b.classList.remove('active'));
                $$('.modal-tab-content', group).forEach(c => c.classList.remove('active'));

                btn.classList.add('active');
                $(`[data-tab-content="${target}"]`, group)?.classList.add('active');
            });
        });
    }

    /* ── 2. PASSWORD TOGGLE ───────────────────────────────── */
    function initPasswordToggles() {
        $$('.input-password__toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const inp  = btn.closest('.input-password').querySelector('input');
                const isP  = inp.type === 'password';
                inp.type   = isP ? 'text' : 'password';
                // swap icon
                btn.querySelector('.icon-eye')?.classList.toggle('hidden', isP);
                btn.querySelector('.icon-eye-off')?.classList.toggle('hidden', !isP);
            });
        });
    }

    /* ── 3. PHOTO PREVIEW ─────────────────────────────────── */
    function initPhotoPreview() {
        $$('input[data-preview]').forEach(input => {
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) return;
                const previewEl = $('#' + input.dataset.preview);
                if (!previewEl) return;
                const reader = new FileReader();
                reader.onload = e => {
                    previewEl.innerHTML = `<img src="${e.target.result}" alt="preview">`;
                };
                reader.readAsDataURL(file);
            });
        });
    }

    /* ── 4. DETAIL MODAL (AJAX) ───────────────────────────── */
    function initDetailButtons() {
        $$('[data-action="detail"]').forEach(btn => {
            btn.addEventListener('click', async e => {
                e.preventDefault();
                const id = btn.dataset.id;

                // Close the action dropdown
                btn.closest('.ds-dropdown')?.classList.remove('open');

                try {
                    const res  = await fetch(`/siswa/${id}`, {
                        headers: { 'Accept': 'application/json',
                                   'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) throw new Error('Gagal memuat data');
                    const d = await res.json();
                    populateDetailModal(d);
                    openModal('modalDetail');
                } catch (err) {
                    alert('Gagal memuat detail siswa: ' + err.message);
                }
            });
        });
    }

    function populateDetailModal(d) {
        const initial = d.name ? d.name.charAt(0).toUpperCase() : '?';
        const avatarEl = $('#detailAvatar');
        if (avatarEl) {
            avatarEl.innerHTML = d.photo_url
                ? `<img src="${d.photo_url}" alt="${d.name}">`
                : initial;
        }

        const set = (id, val) => {
            const el = $('#' + id);
            if (el) el.textContent = val || '-';
        };

        set('detailName',     d.name);
        set('detailNis',      d.nis);
        set('detailEmail',    d.email);
        set('detailGender',   d.gender_label);
        set('detailDob',      d.date_of_birth);
        set('detailPhone',    d.phone_number);
        set('detailAddress',  d.address);
        set('detailKelas',    d.kelas_lengkap);
        set('detailCreated',  d.created_at);

        const statusEl = $('#detailStatus');
        if (statusEl) {
            statusEl.className = 'badge ' + (d.is_active ? 'badge--active' : 'badge--inactive');
            statusEl.textContent = d.is_active ? 'Aktif' : 'Nonaktif';
        }
    }

    /* ── 5. DELETE MODAL ──────────────────────────────────── */
    function initDeleteButtons() {
        $$('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id    = btn.dataset.id;
                const name  = btn.dataset.name;
                const nis   = btn.dataset.nis;
                const email = btn.dataset.email;

                // Close dropdown
                btn.closest('.ds-dropdown')?.classList.remove('open');

                // Populate modal
                const nameEl  = $('#deleteStudentName');
                const nisEl   = $('#deleteStudentNis');
                const initEl  = $('#deleteStudentInitial');
                const formEl  = $('#deleteForm');

                if (nameEl)  nameEl.textContent  = name;
                if (nisEl)   nisEl.textContent   = 'NIS: ' + nis;
                if (initEl)  initEl.textContent  = name ? name.charAt(0).toUpperCase() : '?';
                if (formEl)  formEl.action = `/siswa/${id}`;

                openModal('modalDelete');
            });
        });
    }

    /* ── 6. EXCEL DROPZONE ────────────────────────────────── */
    function initExcelDropzone() {
        const zone     = $('#excelDropzone');
        const input    = $('#excelFileInput');
        const chosen   = $('#excelFileChosen');
        const fileName = $('#excelFileName');
        const clearBtn = $('#excelFileClear');

        if (!zone || !input) return;

        zone.addEventListener('click', () => input.click());

        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) setExcelFile(file);
        });

        input.addEventListener('change', () => {
            if (input.files[0]) setExcelFile(input.files[0]);
        });

        function setExcelFile(file) {
            // Copy to real input for form submission
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;

            if (fileName) fileName.textContent = file.name;
            if (chosen)   chosen.classList.add('show');
        }

        clearBtn?.addEventListener('click', e => {
            e.stopPropagation();
            input.value = '';
            if (chosen) chosen.classList.remove('show');
        });
    }

    /* ── INIT ALL ─────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        PSC.initActionDropdowns('.ds-action__btn', '.ds-dropdown');
        PSC.initModalClose();
        PSC.initOpenButtons();
        initTabs();
        initPasswordToggles();
        initPhotoPreview();
        initDetailButtons();
        initDeleteButtons();
        initExcelDropzone();
        PSC.initFormSpinner();
        PSC.initAlerts();
    });

})();
