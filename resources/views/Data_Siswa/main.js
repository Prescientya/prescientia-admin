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

    /* ── 6. EXCEL DROPZONE + PREVIEW ─────────────────────── */
    function initExcelDropzone() {
        const zone      = $('#excelDropzone');
        const input     = $('#excelFileInput');
        const chosen    = $('#excelFileChosen');
        const fileName  = $('#excelFileName');
        const clearBtn  = $('#excelFileClear');

        if (!zone || !input) return;

        /* ── drag / click ──────────────────────────────── */
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

        /* ── helpers ───────────────────────────────────── */
        function showSection(id)  { const el = $('#' + id); if (el) el.style.display = ''; }
        function hideSection(id)  { const el = $('#' + id); if (el) el.style.display = 'none'; }

    function initResetPasswordButtons() {
        $$('[data-action="reset-password"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name || '–';
                const nis = btn.dataset.nis || '–';

                btn.closest('.ds-dropdown')?.classList.remove('open');

                const nameEl = $('#resetStudentName');
                const nisEl = $('#resetStudentNis');
                const initEl = $('#resetStudentInitial');
                const formEl = $('#resetStudentForm');

                if (nameEl) nameEl.textContent = name;
                if (nisEl) nisEl.textContent = 'NIS: ' + nis;
                if (initEl) initEl.textContent = name ? name.charAt(0).toUpperCase() : '?';
                if (formEl) formEl.action = `/siswa/${id}/reset-password`;

                openModal('modalResetPasswordSiswa');
            });
        });
    }

        function setProgress(pct, label) {
            const fill  = $('#importProgressFill');
    initResetPasswordButtons();
            const lbl   = $('#importProgressLabel');
            if (fill) fill.style.width = pct + '%';
            if (lbl)  lbl.textContent  = label;
        }

        function resetPreview() {
            hideSection('importReadingSection');
            hideSection('importMissingSection');
            setProgress(0, 'Membaca file… 0%');
            const master = $('#autoCreateMaster');
            if (master) {
                master.checked = false;
                master.indeterminate = false;
            }
        }

        function setExcelFile(file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;

            if (fileName) fileName.textContent = file.name;
            if (chosen)   chosen.classList.add('show');

            readExcelFile(file);
        }

        /* ── Excel reading ──────────────────────────────── */
        function readExcelFile(file) {
            if (typeof XLSX === 'undefined') {
                console.warn('SheetJS (XLSX) belum dimuat.');
                return;
            }

            resetPreview();
            showSection('importReadingSection');
            setProgress(0, 'Membaca file… 0%');

            const reader = new FileReader();

            reader.onprogress = e => {
                if (e.lengthComputable) {
                    // File reading = 0-80%, parsing+checking = 80-100%
                    const pct = Math.round((e.loaded / e.total) * 80);
                    setProgress(pct, `Membaca file… ${pct}%`);
                }
            };

            reader.onload = async e => {
                setProgress(85, 'Memproses data… 85%');
                try {
                    const data = new Uint8Array(e.target.result);
                    const wb   = XLSX.read(data, { type: 'array' });

                    // Baca semua sheet, gabungkan barisnya
                    let allRows = [];
                    wb.SheetNames.forEach(wsName => {
                        const ws   = wb.Sheets[wsName];
                        const rows = XLSX.utils.sheet_to_json(ws, { defval: '' });
                        allRows = allRows.concat(rows);
                    });
                    const totalSheets = wb.SheetNames.length;

                    setProgress(90, 'Memeriksa kelas di database… 90%');

                    // Extract unique (tingkat, jurusan) combinations dari semua sheet
                    const classMap = new Map();
                    allRows.forEach(row => {
                        const tingkat = String(row['tingkat'] || row['Tingkat'] || '').trim();
                        const jurusan = String(row['jurusan'] || row['Jurusan'] || '').trim().toUpperCase();
                        if (tingkat) {
                            const key = tingkat + '|' + jurusan;
                            if (!classMap.has(key)) {
                                classMap.set(key, { tingkat: parseInt(tingkat, 10), jurusan });
                            }
                        }
                    });

                    const missingClasses = await checkMissingClasses(Array.from(classMap.values()));

                    const sheetInfo = totalSheets > 1 ? ` (${totalSheets} sheet)` : '';
                    setProgress(100, `Selesai dibaca — ${allRows.length} baris terdeteksi${sheetInfo} ✓`);

                    renderMissingClasses(missingClasses);

                } catch (err) {
                    setProgress(0, 'Gagal membaca file: ' + err.message);
                    console.error('Excel parse error:', err);
                }
            };

            reader.onerror = () => setProgress(0, 'Gagal membaca file.');

            reader.readAsArrayBuffer(file);
        }

        /* ── AJAX: check missing classes ───────────────── */
        async function checkMissingClasses(classes) {
            if (!classes.length) return [];
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const res = await fetch('/siswa/check-classes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':        'application/json',
                        'X-CSRF-TOKEN':  token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ classes }),
                });
                if (!res.ok) throw new Error('Server error ' + res.status);
                const data = await res.json();
                return data.missing || [];
            } catch (err) {
                console.warn('checkMissingClasses error:', err);
                return [];
            }
        }

        /* ── Render missing classes ─────────────────────── */
        function renderMissingClasses(missing) {
            const section = $('#importMissingSection');
            const listEl  = $('#importMissingList');
            const master  = $('#autoCreateMaster');
            const descEl  = $('#autoCreateDesc');
            if (!section || !listEl) return;

            if (!missing.length) {
                section.style.display = 'none';
                if (descEl) {
                    descEl.textContent = 'Semua kelas pada file sudah tersedia di database.';
                }
                return;
            }

            // Build checkboxes for each missing class
            listEl.innerHTML = missing.map(cls => `
                <label class="import-missing-item">
                    <input type="checkbox" class="missing-class-cb"
                           name="classes_to_create[]"
                           value="${escHtml(cls.key)}"
                           data-tingkat="${cls.tingkat}"
                           data-jurusan="${escHtml(cls.jurusan)}"
                           checked>
                    <span class="import-missing-item-label">${escHtml(cls.label)}</span>
                    <span class="import-missing-badge">Belum ada</span>
                </label>
            `).join('');

            section.style.display = '';

            if (descEl) {
                descEl.textContent = missing.length + ' kelas belum ada. Centang kelas yang ingin dibuat, atau gunakan tombol di atas.';
            }

            // Sync master ↔ individual checkboxes
            bindMasterCheckbox(section);
        }

        /* ── Master checkbox sync ───────────────────────── */
        function bindMasterCheckbox(scope) {
            const master = $('#autoCreateMaster');
            if (!master) return;

            // Remove old listener by cloning
            const newMaster = master.cloneNode(true);
            master.parentNode.replaceChild(newMaster, master);

            // Set initial state: all individual cbs are checked by default
            newMaster.checked = true;
            newMaster.indeterminate = false;

            newMaster.addEventListener('change', () => {
                $$('.missing-class-cb').forEach(cb => { cb.checked = newMaster.checked; });
            });

            // Add listeners to individual checkboxes directly (no document-level listener)
            function refreshMasterState() {
                const cbs = Array.from($$('.missing-class-cb'));
                if (!cbs.length) return;
                const checked = cbs.filter(c => c.checked).length;
                newMaster.indeterminate = (checked > 0 && checked < cbs.length);
                newMaster.checked      = (checked === cbs.length);
            }

            $$('.missing-class-cb').forEach(cb => {
                cb.addEventListener('change', refreshMasterState);
            });
        }

        /* ── Clear button ───────────────────────────────── */
        clearBtn?.addEventListener('click', e => {
            e.stopPropagation();
            input.value = '';
            if (chosen) chosen.classList.remove('show');
            resetPreview();
        });
    }

    /* ── HTML escape helper ───────────────────────────── */
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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

    /* ── 7. BULK DEACTIVATE MODAL ─────────────────────────── */
    function initBulkDeactivate() {
        const modal     = $('#modalBulkDeactivate');
        const form      = $('#formBulkDeactivate');
        const preview   = $('#bulkPreview');
        const countEl   = $('#bulkPreviewCount');
        const listEl    = $('#bulkPreviewList');
        const submitBtn = $('#btnSubmitDeactivate');

        if (!modal || !form) return;

        const modeRadios = $$('input[name="mode"]', modal);

        // Show/hide mode contents on tab change
        modeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                $$('.bulk-mode-content', modal).forEach(el => {
                    el.style.display = el.dataset.mode === radio.value ? '' : 'none';
                });
                resetPreview();
            });
        });

        // Listen to select changes for preview
        const selects = ['bulkClassSelect', 'bulkGradeSelect', 'bulkMajorSelect', 'bulkGradeCombo', 'bulkMajorCombo'];
        selects.forEach(id => {
            const el = $('#' + id);
            if (el) el.addEventListener('change', debounce(fetchPreview, 300));
        });

        function getSelectedMode() {
            const checked = $('input[name="mode"]:checked', modal);
            return checked ? checked.value : 'class';
        }

        function resetPreview() {
            preview.style.display = 'none';
            submitBtn.disabled = true;
            listEl.innerHTML = '';
        }

        async function fetchPreview() {
            const mode = getSelectedMode();
            const params = new URLSearchParams();

            if (mode === 'class') {
                const classId = $('#bulkClassSelect')?.value;
                if (!classId) return resetPreview();
                params.set('class_id', classId);
            } else if (mode === 'grade') {
                const grade = $('#bulkGradeSelect')?.value;
                if (!grade) return resetPreview();
                params.set('grade', grade);
            } else if (mode === 'major') {
                const major = $('#bulkMajorSelect')?.value;
                if (!major) return resetPreview();
                params.set('major', major);
            } else if (mode === 'grade_major') {
                const grade = $('#bulkGradeCombo')?.value;
                const major = $('#bulkMajorCombo')?.value;
                if (!grade || !major) return resetPreview();
                params.set('grade', grade);
                params.set('major', major);
            }

            try {
                const res = await fetch('/siswa/preview-bulk?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Server error');
                const data = await res.json();

                if (data.count === 0) {
                    preview.style.display = 'block';
                    countEl.textContent = '0';
                    listEl.innerHTML = '<div class="bulk-preview__more">Tidak ada siswa aktif yang cocok.</div>';
                    submitBtn.disabled = true;
                    return;
                }

                countEl.textContent = data.count;
                listEl.innerHTML = data.students.map(s => `
                    <div class="bulk-preview__item">
                        <span class="bulk-preview__item-name">${escHtml(s.name)}</span>
                        <span class="bulk-preview__item-nis">${escHtml(s.nis)}</span>
                        <span class="bulk-preview__item-kelas">${escHtml(s.kelas)}</span>
                    </div>
                `).join('');

                if (data.hasMore) {
                    listEl.innerHTML += `<div class="bulk-preview__more">...dan ${data.count - 10} siswa lainnya</div>`;
                }

                preview.style.display = 'block';
                submitBtn.disabled = false;

                // Sync hidden inputs for form submission
                syncFormInputs(mode);

            } catch (err) {
                console.error('Preview error:', err);
                resetPreview();
            }
        }

        function syncFormInputs(mode) {
            // Remove old hidden inputs
            $$('input[data-bulk-sync]', form).forEach(el => el.remove());

            // Add correct hidden inputs based on mode
            if (mode === 'grade_major') {
                const gradeInput = document.createElement('input');
                gradeInput.type = 'hidden';
                gradeInput.name = 'grade';
                gradeInput.value = $('#bulkGradeCombo')?.value || '';
                gradeInput.dataset.bulkSync = '1';
                form.appendChild(gradeInput);

                const majorInput = document.createElement('input');
                majorInput.type = 'hidden';
                majorInput.name = 'major';
                majorInput.value = $('#bulkMajorCombo')?.value || '';
                majorInput.dataset.bulkSync = '1';
                form.appendChild(majorInput);
            }
        }

        function debounce(fn, delay) {
            let timer;
            return function(...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }
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
        initBulkDeactivate();
        PSC.initFormSpinner();
        PSC.initAlerts();
    });

})();
