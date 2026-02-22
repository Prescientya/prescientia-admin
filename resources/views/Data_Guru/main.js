/* ============================================================
   DATA GURU – main.js
   Module-specific only. Shared utilities → components/global.js
   ============================================================ */
(function () {
    'use strict';

    const { $, $$, openModal, closeModal } = window.PSC;

    /* ── 1. MAPEL TAG INPUT ───────────────────────────────── */
    /**
     * Creates a tag-pill input experience.
     * @param {string} wrapperId  id of .mapel-input-wrap element
     * @param {string} textInputId id of the underlying .mapel-text-input
     * @param {string} hiddenId   id of the hidden input that stores CSV value
     * @param {string[]} initial  pre-existing tags (for edit page)
     */
    function initMapelTagInput(wrapperId, textInputId, hiddenId, initial = []) {
        const wrap    = $('#' + wrapperId);
        const input   = $('#' + textInputId);
        const hidden  = $('#' + hiddenId);
        if (!wrap || !input || !hidden) return;

        let tags = [...initial];

        function renderTags() {
            // Remove existing tag elements (leave input)
            wrap.querySelectorAll('.mapel-tag').forEach(el => el.remove());
            // Insert before the text input
            tags.forEach(tag => {
                const pill = document.createElement('span');
                pill.className = 'mapel-tag';
                pill.innerHTML = `${tag}<button type="button" class="mapel-tag__remove" data-tag="${tag}" aria-label="Hapus ${tag}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg></button>`;
                wrap.insertBefore(pill, input);
                pill.querySelector('.mapel-tag__remove').addEventListener('click', e => {
                    e.stopPropagation();
                    removeTag(tag);
                });
            });
            hidden.value = tags.join(',');
        }

        function addTag(raw) {
            const names = raw.split(',').map(s => s.trim()).filter(Boolean);
            names.forEach(name => {
                if (name && !tags.includes(name)) tags.push(name);
            });
            renderTags();
        }

        function removeTag(name) {
            tags = tags.filter(t => t !== name);
            renderTags();
        }

        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = input.value.trim().replace(/,$/, '');
                if (val) { addTag(val); input.value = ''; }
            } else if (e.key === 'Backspace' && input.value === '' && tags.length > 0) {
                removeTag(tags[tags.length - 1]);
            }
        });

        input.addEventListener('blur', () => {
            const val = input.value.trim().replace(/,$/, '');
            if (val) { addTag(val); input.value = ''; }
        });

        // Allow clicking anywhere on wrap to focus input
        wrap.addEventListener('click', () => input.focus());

        renderTags();
    }

    /* ── 2. PHOTO PREVIEW ─────────────────────────────────── */
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

    /* ── 3. PASSWORD TOGGLE ───────────────────────────────── */
    function initPasswordToggles() {
        $$('.input-password__toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const inp = btn.closest('.input-password').querySelector('input');
                const isP = inp.type === 'password';
                inp.type  = isP ? 'text' : 'password';
                btn.querySelector('.icon-eye')?.classList.toggle('hidden', isP);
                btn.querySelector('.icon-eye-off')?.classList.toggle('hidden', !isP);
            });
        });
    }

    /* ── 4. DETAIL MODAL (AJAX) ───────────────────────────── */
    function initDetailButtons() {
        $$('[data-action="detail"]').forEach(btn => {
            btn.addEventListener('click', async e => {
                e.preventDefault();
                const id = btn.dataset.id;
                btn.closest('.dg-dropdown')?.classList.remove('open');

                try {
                    const res = await fetch(`/guru/${id}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) throw new Error('Gagal memuat data');
                    const d = await res.json();
                    populateDetailModal(d);
                    openModal('modalDetail');
                } catch (err) {
                    alert('Gagal memuat detail guru: ' + err.message);
                }
            });
        });
    }

    function populateDetailModal(d) {
        const initial  = d.name ? d.name.charAt(0).toUpperCase() : '?';
        const avatarEl = $('#dg-detailAvatar');
        if (avatarEl) {
            avatarEl.innerHTML = d.photo_url
                ? `<img src="${d.photo_url}" alt="${d.name}">`
                : initial;
        }

        const set = (id, val) => {
            const el = $('#' + id);
            if (el) el.textContent = val || '–';
        };

        set('dg-detailName',   d.name);
        set('dg-detailNip',    d.nip);
        set('dg-detailEmail',  d.email);
        set('dg-detailGender', d.gender_label);
        set('dg-detailDob',    d.date_of_birth);
        set('dg-detailPhone',  d.phone_number);
        set('dg-detailCreated',d.created_at);
        set('dg-detailAddress',d.address);

        // Status badge
        const statusEl = $('#dg-detailStatus');
        if (statusEl) {
            statusEl.textContent    = d.is_active ? 'Aktif' : 'Nonaktif';
            statusEl.className      = 'badge ' + (d.is_active ? 'badge--active' : 'badge--inactive');
        }

        // Subjects → pills
        const mapelEl = $('#dg-detailMapel');
        if (mapelEl) {
            if (d.subjects && d.subjects.length > 0) {
                mapelEl.innerHTML = d.subjects
                    .map(s => `<span class="subject-chip">${s}</span>`)
                    .join('');
            } else {
                mapelEl.innerHTML = '<span style="color:var(--text-muted);">–</span>';
            }
        }
    }

    /* ── 5. DELETE MODAL ──────────────────────────────────── */
    function initDeleteButtons() {
        $$('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id    = btn.dataset.id;
                const name  = btn.dataset.name   || '–';
                const nip   = btn.dataset.nip    || '–';
                const init  = name.charAt(0).toUpperCase();

                // Populate modal elements
                const nameEl = $('#deleteGuruName');
                const nipEl  = $('#deleteGuruNip');
                const initEl = $('#deleteGuruInitial');
                if (nameEl) nameEl.textContent = name;
                if (nipEl)  nipEl.textContent  = 'NIP: ' + nip;
                if (initEl) initEl.textContent  = init;

                // Set form action
                const form = $('#deleteGuruForm');
                if (form) form.action = `/guru/${id}`;

                // Close dropdown + open modal
                btn.closest('.dg-dropdown')?.classList.remove('open');
                openModal('modalDelete');
            });
        });
    }

    /* ── 6. EXCEL DROPZONE ────────────────────────────────── */
    function initExcelDropzone() {
        const dropzone  = $('#dg-excelDropzone');
        const fileInput = $('#dg-excelFileInput');
        const fileChosen = $('#dg-excelFileChosen');
        const fileName  = $('#dg-excelFileName');
        const fileClear = $('#dg-excelFileClear');
        const browseBtn = dropzone?.querySelector('.excel-dropzone__btn');

        if (!dropzone || !fileInput) return;

        const showFile = file => {
            if (fileName) fileName.textContent = file.name;
            fileChosen?.classList.add('visible');
            dropzone.style.display = 'none';
        };

        const clearFile = () => {
            fileInput.value = '';
            if (fileName) fileName.textContent = '–';
            fileChosen?.classList.remove('visible');
            dropzone.style.display = '';
        };

        browseBtn?.addEventListener('click', e => { e.preventDefault(); fileInput.click(); });
        fileInput.addEventListener('change', () => { if (fileInput.files[0]) showFile(fileInput.files[0]); });
        fileClear?.addEventListener('click', clearFile);

        dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                showFile(file);
            }
        });
        dropzone.addEventListener('click', e => {
            if (e.target !== browseBtn && !browseBtn?.contains(e.target)) fileInput.click();
        });
    }

    /* ── INIT ─────────────────────────────────────────────── */
    PSC.initActionDropdowns('.dg-action__btn', '.dg-dropdown');
    PSC.initModalClose();
    PSC.initOpenButtons();
    initPhotoPreview();
    initPasswordToggles();
    initDetailButtons();
    initDeleteButtons();
    initExcelDropzone();
    PSC.initAlerts();
    PSC.initFormSpinner();

    // Tag input for add modal
    initMapelTagInput('addMapelWrap', 'addMapelInput', 'addMapelHidden');

    // Tag input for edit page (if exists — pre-populated via data attribute)
    const editWrap = $('#editMapelWrap');
    if (editWrap) {
        const existingRaw = editWrap.dataset.existing || '';
        const existing = existingRaw ? existingRaw.split(',').map(s => s.trim()).filter(Boolean) : [];
        initMapelTagInput('editMapelWrap', 'editMapelInput', 'editMapelHidden', existing);
    }

})();
