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
    function initMapelTagInput(wrapperId, textInputId, hiddenId, initial = null) {
        const wrap    = $('#' + wrapperId);
        const input   = $('#' + textInputId);
        const hidden  = $('#' + hiddenId);
        if (!wrap || !input || !hidden) return;

        const validMapel = Array.isArray(window.VALID_MAPEL) ? window.VALID_MAPEL : [];
        const suggestBox = document.createElement('div');
        suggestBox.className = 'mapel-suggest';
        suggestBox.id = textInputId + 'Suggest';
        suggestBox.setAttribute('role', 'listbox');
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', suggestBox.id);
        wrap.parentNode?.insertBefore(suggestBox, hidden);

        // If initial not explicitly provided, seed from hidden.value
        // (handles old() restoration after a validation error)
        let tags = initial !== null
            ? [...initial]
            : (hidden.value ? hidden.value.split(',').map(s => s.trim()).filter(Boolean) : []);

        function hasTag(name) {
            return tags.some(t => t.toLowerCase() === name.toLowerCase());
        }

        function hideSuggestions() {
            suggestBox.classList.remove('show');
            suggestBox.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
        }

        function showSuggestions(keyword) {
            const q = String(keyword || '').trim().toLowerCase();
            if (!q) {
                hideSuggestions();
                return;
            }

            const matches = validMapel
                .filter(name => name.toLowerCase().includes(q) && !hasTag(name))
                .slice(0, 8);

            if (!matches.length) {
                suggestBox.innerHTML = `
                    <div class="mapel-suggest__empty" role="status">
                        Tidak ada mata pelajaran untuk "${escHtml(keyword.trim())}".
                    </div>
                `;
                suggestBox.classList.add('show');
                input.setAttribute('aria-expanded', 'true');
                return;
            }

            suggestBox.innerHTML = matches
                .map(name => `
                    <button type="button" class="mapel-suggest__item" data-mapel="${escHtml(name)}" role="option">
                        ${escHtml(name)}
                    </button>
                `)
                .join('');

            suggestBox.querySelectorAll('.mapel-suggest__item').forEach(btn => {
                btn.addEventListener('click', () => {
                    addTag(btn.dataset.mapel || '');
                    input.value = '';
                    input.focus();
                    hideSuggestions();
                });
            });

            suggestBox.classList.add('show');
            input.setAttribute('aria-expanded', 'true');
        }

        function renderTags() {
            // Remove existing tag elements (leave input)
            wrap.querySelectorAll('.mapel-tag').forEach(el => el.remove());
            // Insert before the text input
            tags.forEach(tag => {
                const isValid = !validMapel ||
                    validMapel.some(s => s.toLowerCase() === tag.toLowerCase());
                const pill = document.createElement('span');
                pill.className = 'mapel-tag' + (isValid ? '' : ' mapel-tag--invalid');
                if (!isValid) pill.title = `Mapel "${tag}" tidak ditemukan di sistem sekolah ini`;
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
                if (name && !hasTag(name)) tags.push(name);
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
                if (val) {
                    addTag(val);
                    input.value = '';
                    hideSuggestions();
                }
            } else if (e.key === 'Backspace' && input.value === '' && tags.length > 0) {
                removeTag(tags[tags.length - 1]);
            }
        });

        input.addEventListener('input', () => showSuggestions(input.value));

        input.addEventListener('focus', () => {
            showSuggestions(input.value);
        });

        input.addEventListener('blur', () => {
            setTimeout(() => {
                const val = input.value.trim().replace(/,$/, '');
                if (val) {
                    addTag(val);
                    input.value = '';
                }
                hideSuggestions();
            }, 120);
        });

        // Allow clicking anywhere on wrap to focus input
        wrap.addEventListener('click', () => {
            input.focus();
            showSuggestions(input.value);
        });

        document.addEventListener('click', e => {
            if (!wrap.contains(e.target) && !suggestBox.contains(e.target)) {
                hideSuggestions();
            }
        });

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

        // Role & homeroom class
        const roleEl = $('#dg-detailRole');
        if (roleEl) {
            if (d.teacher_role === 'walikelas') {
                roleEl.innerHTML = '<span class="badge badge--active">Wali Kelas</span>';
            } else {
                roleEl.innerHTML = '<span class="badge badge--inactive">Pengajar</span>';
            }
        }
        const homeroomWrap = $('#dg-detailHomeroomWrap');
        const homeroomEl   = $('#dg-detailHomeroomClass');
        if (homeroomWrap) homeroomWrap.style.display = (d.teacher_role === 'walikelas') ? '' : 'none';
        if (homeroomEl)  homeroomEl.textContent = d.homeroom_class || '–';

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
        const zone      = $('#dg-excelDropzone');
        const input     = $('#dg-excelFileInput');
        const chosen    = $('#dg-excelFileChosen');
        const fileName  = $('#dg-excelFileName');
        const clearBtn  = $('#dg-excelFileClear');

        if (!zone || !input) return;

        /* ── drag / click ─────────────────────────────── */
        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
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

        /* ── helpers ──────────────────────────────────── */
        function showSection(id) { const el = $('#' + id); if (el) el.style.display = ''; }
        function hideSection(id) { const el = $('#' + id); if (el) el.style.display = 'none'; }

    function initResetPasswordButtons() {
        $$('[data-action="reset-password"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name || '–';
                const nip = btn.dataset.nip || '–';

                btn.closest('.dg-dropdown')?.classList.remove('open');

                const nameEl = $('#resetGuruName');
                const nipEl = $('#resetGuruNip');
                const initEl = $('#resetGuruInitial');
                const formEl = $('#resetGuruForm');

                if (nameEl) nameEl.textContent = name;
                if (nipEl) nipEl.textContent = 'NIP: ' + nip;
                if (initEl) initEl.textContent = name ? name.charAt(0).toUpperCase() : '?';
                if (formEl) formEl.action = `/guru/${id}/reset-password`;

                openModal('modalResetPasswordGuru');
            });
        });
    }

        function setProgress(pct, label) {
            const fill = $('#dg-importProgressFill');
    initResetPasswordButtons();
            const lbl  = $('#dg-importProgressLabel');
            if (fill) fill.style.width = pct + '%';
            if (lbl)  lbl.textContent  = label;
        }

        function resetPreview() {
            hideSection('dg-importReadingSection');
            hideSection('dg-importMissingSection');
            setProgress(0, 'Membaca file… 0%');
            const master = $('#dg-autoCreateMaster');
            if (master) { master.checked = false; master.indeterminate = false; }
        }

        function setExcelFile(file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            if (fileName) fileName.textContent = file.name;
            if (chosen)   chosen.classList.add('show');
            readExcelFile(file);
        }

        /* ── Excel reading ───────────────────────────── */
        function readExcelFile(file) {
            if (typeof XLSX === 'undefined') {
                console.warn('SheetJS (XLSX) belum dimuat.');
                return;
            }

            resetPreview();
            showSection('dg-importReadingSection');
            setProgress(0, 'Membaca file… 0%');

            const reader = new FileReader();

            reader.onprogress = e => {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 80);
                    setProgress(pct, `Membaca file… ${pct}%`);
                }
            };

            reader.onload = async e => {
                setProgress(85, 'Memproses data… 85%');
                try {
                    const data   = new Uint8Array(e.target.result);
                    const wb     = XLSX.read(data, { type: 'array' });
                    const ws     = wb.Sheets[wb.SheetNames[0]];
                    const rows   = XLSX.utils.sheet_to_json(ws, { defval: '' });

                    setProgress(90, 'Memeriksa mapel di database… 90%');

                    // Collect unique subject names from comma-separated 'mapel' column
                    const subjectSet = new Set();
                    rows.forEach(row => {
                        const mapelRaw = String(row['mapel'] || row['Mapel'] || '').trim();
                        if (mapelRaw) {
                            mapelRaw.split(',').forEach(n => {
                                const name = n.trim();
                                if (name) subjectSet.add(name);
                            });
                        }
                    });

                    const missingSubjects = await checkMissingSubjects(Array.from(subjectSet));

                    setProgress(100, `Selesai dibaca — ${rows.length} baris terdeteksi ✓`);

                    renderMissingSubjects(missingSubjects);

                } catch (err) {
                    setProgress(0, 'Gagal membaca file: ' + err.message);
                    console.error('Excel parse error:', err);
                }
            };

            reader.onerror = () => setProgress(0, 'Gagal membaca file.');
            reader.readAsArrayBuffer(file);
        }

        /* ── AJAX: check missing subjects ────────────── */
        async function checkMissingSubjects(subjects) {
            if (!subjects.length) return [];
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const res   = await fetch('/guru/check-subjects', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ subjects }),
                });
                if (!res.ok) throw new Error('Server error ' + res.status);
                const data = await res.json();
                return data.missing || [];
            } catch (err) {
                console.warn('checkMissingSubjects error:', err);
                return [];
            }
        }

        /* ── Render missing subjects ─────────────────── */
        function renderMissingSubjects(missing) {
            const section = $('#dg-importMissingSection');
            const listEl  = $('#dg-importMissingList');
            const descEl  = $('#dg-autoCreateDesc');
            if (!section || !listEl) return;

            if (!missing.length) {
                section.style.display = 'none';
                if (descEl) descEl.textContent = 'Semua mapel pada file sudah tersedia di database.';
                return;
            }

            listEl.innerHTML = missing.map(s => `
                <label class="import-missing-item">
                    <input type="checkbox" class="dg-missing-subject-cb"
                           name="subjects_to_create[]"
                           value="${escHtml(s.name)}"
                           checked>
                    <span class="import-missing-item-label">${escHtml(s.label)}</span>
                    <span class="import-missing-badge">Belum ada</span>
                </label>
            `).join('');

            section.style.display = '';

            if (descEl) {
                descEl.textContent = missing.length + ' mapel belum ada. Centang mapel yang ingin dibuat, atau gunakan tombol di atas.';
            }

            bindMasterCheckbox();
        }

        /* ── Master checkbox sync ────────────────────── */
        function bindMasterCheckbox() {
            const master = $('#dg-autoCreateMaster');
            if (!master) return;

            // Re-attach listener by cloning
            const newMaster = master.cloneNode(true);
            master.parentNode.replaceChild(newMaster, master);
            newMaster.checked = true;
            newMaster.indeterminate = false;

            newMaster.addEventListener('change', () => {
                document.querySelectorAll('.dg-missing-subject-cb').forEach(cb => {
                    cb.checked = newMaster.checked;
                });
            });

            function refreshMasterState() {
                const cbs     = Array.from(document.querySelectorAll('.dg-missing-subject-cb'));
                if (!cbs.length) return;
                const checked = cbs.filter(c => c.checked).length;
                newMaster.indeterminate = (checked > 0 && checked < cbs.length);
                newMaster.checked      = (checked === cbs.length);
            }

            document.querySelectorAll('.dg-missing-subject-cb').forEach(cb => {
                cb.addEventListener('change', refreshMasterState);
            });
        }

        /* ── Clear button ─────────────────────────────── */
        clearBtn?.addEventListener('click', e => {
            e.stopPropagation();
            input.value = '';
            if (chosen) chosen.classList.remove('show');
            resetPreview();
        });
    }

    /* ── HTML escape helper ───────────────────────── */
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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
