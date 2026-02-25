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

    /* ── ROLE CHECK (edit page) ───────────────────────────── */
    function initRoleCheck() {
        const classSelect = document.getElementById('classSelect');
        const roleSection = document.getElementById('roleSection');
        const roleSelect  = document.getElementById('roleSelect');
        const roleHints   = document.getElementById('roleHints');
        if (!classSelect || !roleSelect) return;

        const cfg = window.SISWA_EDIT || {};

        const LABELS = { km: 'KM', wakil_km: 'Wakil KM', sekretaris: 'Sekretaris' };

        function buildHints(data) {
            roleHints.innerHTML = '';
            const items = [];
            if (data.km)        items.push({ key: 'km',        label: LABELS.km,        holder: data.km });
            if (data.wakil_km)  items.push({ key: 'wakil_km',  label: LABELS.wakil_km,  holder: data.wakil_km });
            if (data.sekretaris && data.sekretaris.length) {
                data.sekretaris.forEach(name => {
                    items.push({ key: 'sekretaris', label: LABELS.sekretaris, holder: name });
                });
            }
            items.forEach(item => {
                const el = document.createElement('span');
                el.className = 'role-hint-item';
                el.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"' +
                    ' fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                    '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
                    'Sudah ada ' + item.label + ': <strong>' + item.holder + '</strong>';
                roleHints.appendChild(el);
            });

            // Update option labels
            ['km', 'wakil_km', 'sekretaris'].forEach(key => {
                const opt = document.getElementById('opt-' + key);
                if (!opt) return;
                const base = key === 'km' ? 'KM (Ketua Murid)' : (key === 'wakil_km' ? 'Wakil KM' : 'Sekretaris');
                if (key === 'sekretaris') {
                    const count = (data.sekretaris || []).length;
                    opt.textContent = count >= 2
                        ? base + ' — Slot penuh (2/2)'
                        : base + (count === 1 ? ' — Slot tersisa 1' : '');
                } else {
                    const holder = data[key];
                    opt.textContent = holder ? base + ' — Sudah ada: ' + holder : base;
                }
            });
        }

        async function fetchAndRefresh(classId) {
            if (!classId) {
                roleSection.style.display = 'none';
                return;
            }
            roleSection.style.display = '';
            try {
                const res = await fetch(
                    `/siswa/check-role?class_id=${classId}&student_id=${cfg.studentId || 0}`,
                    { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
                );
                const data = await res.json();
                buildHints(data);
            } catch (e) {
                roleHints.innerHTML = '';
            }
        }

        // Initial render from PHP-passed data
        if (classSelect.value && cfg.classRoleData) {
            buildHints(cfg.classRoleData);
        }

        classSelect.addEventListener('change', () => {
            fetchAndRefresh(classSelect.value);
            // Reset role to pelajar when class changes
            if (roleSelect) roleSelect.value = 'pelajar';
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
        initRoleCheck();
        PSC.initFormSpinner();
        PSC.initAlerts();
    });

})();
