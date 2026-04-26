/* ============================================================
   JADWAL MENGAJAR — main.js
   ============================================================ */
(function () {
    'use strict';

    const { $, $$, openModal, closeModal, initModalClose, initAlerts, toast } = window.PSC;
    const R    = window.JM_ROUTES;
    const CSRF = R.csrf;
    const DAY_LABELS = window.JM_DAYS; // { senin:'Senin', ... }
    const DAY_ORDER  = Object.keys(DAY_LABELS);

    let currentMode      = 'teacher'; // 'teacher' | 'class'
    let currentId        = null;
    let _pendingPreselect = { periodId: null, day: null }; // row clicked from grid

    /* ── 1. MODE TOGGLE ──────────────────────────────────── */
    function initModeTabs() {
        $$('.jm-mode-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.dataset.mode === currentMode) return;
                $$('.jm-mode-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentMode = btn.dataset.mode;
                currentId   = null;

                // Update picker label & options
                $('#jmPickerLabel').textContent =
                    currentMode === 'teacher' ? 'Pilih Guru:' : 'Pilih Kelas:';
                rebuildPicker();
                clearGrid();
            });
        });
    }

    function rebuildPicker() {
        // The picker options were rendered server-side for teachers.
        // For class mode we rebuild from the hidden data.
        const picker = $('#jmPicker');
        picker.innerHTML = '';
        const blank = new Option(`— Pilih ${currentMode === 'teacher' ? 'Guru' : 'Kelas'} —`, '');
        picker.add(blank);

        const src = currentMode === 'teacher' ? window.JM_TEACHERS : window.JM_CLASSES;
        src.forEach(item => {
            picker.add(new Option(item.label, item.id));
        });
    }

    /* ── 2. PICKER CHANGE → load grid ───────────────────── */
    function initPicker() {
        $('#jmPicker').addEventListener('change', function () {
            currentId = this.value ? Number(this.value) : null;
            if (!currentId) { clearGrid(); return; }
            loadGrid();
        });
    }

    async function loadGrid() {
        if (!currentId) return;
        const wrap = $('#jmGridWrap');
        wrap.innerHTML = '<div class="jm-empty"><div class="jm-empty-icon" style="font-size:2rem;">⟳</div><div class="jm-empty-sub">Memuat jadwal...</div></div>';

        try {
            const res  = await fetch(`${R.schedule}?mode=${currentMode}&id=${currentId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (!res.ok) {
                // Server returned 4xx/5xx — show the actual error message
                throw new Error(data.message || `Server error ${res.status}`);
            }

            if (!data.html) {
                throw new Error('Respons server tidak valid (html kosong).');
            }

            wrap.innerHTML = data.html;
            attachGridEvents();
        } catch (err) {
            wrap.innerHTML = `<div class="jm-empty jm-empty--error">
                <div class="jm-empty-icon">⚠️</div>
                <div class="jm-empty-title">Gagal memuat jadwal</div>
                <div class="jm-empty-sub">${err.message}</div>
            </div>`;
        }
    }

    function clearGrid() {
        $('#jmGridWrap').innerHTML = `
            <div class="jm-empty">
                <div class="jm-empty-icon">📅</div>
                <div class="jm-empty-title">Belum ada pilihan</div>
                <div class="jm-empty-sub">Pilih ${currentMode === 'teacher' ? 'guru' : 'kelas'} di atas untuk melihat jadwal mengajar</div>
            </div>`;
    }

    /* ── 3. GRID EVENT DELEGATION ────────────────────────── */
    function attachGridEvents() {
        const wrap = $('#jmGridWrap');

        // Day tab switching (inline <script> in _grid won't fire via innerHTML)
        wrap.querySelectorAll('[data-jmday]').forEach(btn => {
            btn.addEventListener('click', () => {
                wrap.querySelectorAll('[data-jmday]').forEach(b => b.classList.remove('active'));
                wrap.querySelectorAll('[id^="jm-panel-"]').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                wrap.querySelector('#jm-panel-' + btn.dataset.jmday)?.classList.add('active');
            });
        });

        // Add slot button (empty lesson cell)
        wrap.querySelectorAll('.jm-add-slot-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                openAddModal({ periodId: btn.dataset.period, day: btn.dataset.day });
            });
        });

        // Edit button (3-dot dropdown item)
        wrap.querySelectorAll('.jm-dropdown-edit').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                openEditModal(btn.dataset);
            });
        });

        // Delete button (3-dot dropdown item)
        wrap.querySelectorAll('.jm-dropdown-del').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                pendingDeleteId = btn.dataset.id;
                $('#hapusJadwalDesc').textContent = btn.dataset.desc;
                openModal('modalHapusJadwal');
            });
        });

        // 3-dot dropdown behavior
        PSC.initActionDropdowns('.jm-action__btn', '.jm-dropdown');
    }

    /* ── 4. ADD MODAL ────────────────────────────────────── */
    function initTambahBtn() {
        $('#btnTambahJadwal').addEventListener('click', () => openAddModal({}));
    }

    function openAddModal({ periodId, day } = {}) {
        resetForm();
        $('#modalJadwalTitle').textContent = 'Tambah Jadwal Mengajar';
        $('#jfId').value     = '';
        $('#jfMethod').value = 'POST';

        // Remember which row was clicked
        _pendingPreselect = { periodId: periodId || null, day: day || null };

        // Pre-fill teacher/class from current mode selection
        if (currentId) {
            if (currentMode === 'teacher') {
                $('#jfTeacher').value = currentId;
            } else {
                $('#jfClass').value = currentId;
            }
        }

        // Keep dropdowns in sync: teacher -> subject, subject -> class map
        filterSubjectsByTeacher(
            $('#jfTeacher').value || null,
            null,
            $('#jfClass').value || null
        );

        // Immediately pre-fill Hari + Jam from the clicked row (no API needed)
        if (_pendingPreselect.day && _pendingPreselect.periodId) {
            prefillDayPeriodFromAll(_pendingPreselect.day, _pendingPreselect.periodId);
        }

        openModal('modalJadwal');

        // If one of teacher/class is set, load available slots (conflict check)
        const t = $('#jfTeacher').value;
        const c = $('#jfClass').value;
        if (t || c) {
            const { periodId: pid, day: d } = _pendingPreselect;
            _pendingPreselect = { periodId: null, day: null };
            loadAvailablePeriods(t || null, c || null, null, pid, d);
        } else {
            _pendingPreselect = { periodId: null, day: null };
        }
    }

    /**
     * Pre-fill Hari + Jam selects directly from JM_ALL_PERIODS (no API call).
     * Used when a grid row is clicked so the fields show immediately.
     */
    function prefillDayPeriodFromAll(day, periodId) {
        const allPeriods = window.JM_ALL_PERIODS || {};
        const dayEl  = $('#jfDay');
        const jamEl  = $('#jfPeriod');

        // Populate day dropdown with all days that have periods
        dayEl.innerHTML = '<option value="">— Pilih Hari —</option>';
        DAY_ORDER.forEach(d => {
            if (allPeriods[d]?.length) dayEl.add(new Option(DAY_LABELS[d], d));
        });
        if (day) dayEl.value = day;

        // Populate jam dropdown for this day
        jamEl.innerHTML = '<option value="">— Pilih Jam —</option>';
        (allPeriods[day] || []).forEach(p => jamEl.add(new Option(p.label, p.id)));
        if (periodId) jamEl.value = String(periodId);

        // Sync internal state so later populatePeriodSelect calls work correctly
        _availableByDay = {};
        DAY_ORDER.forEach(d => {
            if (allPeriods[d]?.length) _availableByDay[d] = allPeriods[d];
        });
    }

    /* ── 5. EDIT MODAL ───────────────────────────────────── */
    function openEditModal(d) {
        resetForm();
        _pendingPreselect = { periodId: null, day: null };
        $('#modalJadwalTitle').textContent = 'Edit Jadwal Mengajar';
        $('#jfId').value          = d.id;
        $('#jfMethod').value      = 'PATCH';
        $('#jfTeacher').value = d.teacher;
        filterSubjectsByTeacher(d.teacher, d.subject, d.class);

        clearDayPeriodPickers();

        // Pass preselectPeriodId + preselectDay so loadAvailablePeriods sets the selects automatically
        loadAvailablePeriods(d.teacher, d.class, d.id, d.period, d.day);

        openModal('modalJadwal');
    }

    /* ── 6. TEACHER + CLASS CHANGE → load periods ─────── */
    function initFormWatchers() {
        // Teacher change → filter subject dropdown then reload available slots
        $('#jfTeacher')?.addEventListener('change', function () {
            filterSubjectsByTeacher(this.value, null, $('#jfClass').value || null);
            const c = $('#jfClass').value;
            // Preserve currently visible day/period (or pending preselect)
            const pid = _pendingPreselect.periodId || $('#jfPeriod').value || null;
            const d   = _pendingPreselect.day      || $('#jfDay').value    || null;
            _pendingPreselect = { periodId: null, day: null };
            if (this.value || c) {
                loadAvailablePeriods(this.value || null, c || null, $('#jfId').value || null, pid, d);
            } else {
                clearDayPeriodPickers();
            }
        });

        // Subject change → filter allowed classes by Mapel Penugasan Kelas
        $('#jfSubject')?.addEventListener('change', function () {
            filterClassesBySubject(this.value, $('#jfClass').value || null);

            const t = $('#jfTeacher').value;
            const c = $('#jfClass').value;
            const pid = _pendingPreselect.periodId || $('#jfPeriod').value || null;
            const d   = _pendingPreselect.day      || $('#jfDay').value    || null;
            _pendingPreselect = { periodId: null, day: null };

            if (t || c) {
                loadAvailablePeriods(t || null, c || null, $('#jfId').value || null, pid, d);
            } else {
                clearDayPeriodPickers();
            }
        });

        $('#jfClass')?.addEventListener('change', () => {
            const t = $('#jfTeacher').value;
            const c = $('#jfClass').value;
            // Preserve currently visible day/period (or pending preselect)
            const pid = _pendingPreselect.periodId || $('#jfPeriod').value || null;
            const d   = _pendingPreselect.day      || $('#jfDay').value    || null;
            _pendingPreselect = { periodId: null, day: null };
            if (t || c) {
                loadAvailablePeriods(t || null, c || null, $('#jfId').value || null, pid, d);
            } else {
                clearDayPeriodPickers();
            }
        });

        $('#jfDay').addEventListener('change', () => {
            populatePeriodSelect($('#jfDay').value);
        });
    }

    /**
     * Filter (or restore) the subject dropdown based on the selected teacher.
     * - No teacher → show all subjects
     * - Teacher has 0 assigned subjects → show all subjects
     * - Teacher has 1 subject → auto-select it (no blank option needed)
     * - Teacher has N>1 subjects → show only those subjects
     * @param {string|number|null} teacherId
     * @param {string|number|null} preselectId  – force-select this subject id (used in edit modal)
     */
    function filterSubjectsByTeacher(teacherId, preselectId = null, preselectClassId = null) {
        const el   = $('#jfSubject');
        const all  = window.JM_ALL_SUBJECTS || [];
        const subs = teacherId ? (window.JM_TEACHER_SUBJECTS?.[teacherId] || []) : [];
        const list = subs.length > 0 ? subs : all;

        el.innerHTML = '';
        // Show blank placeholder when multiple choices exist, or when a preselect is being restored
        if (list.length > 1 || preselectId != null) {
            el.add(new Option('— Pilih Mapel —', ''));
        }
        list.forEach(s => el.add(new Option(s.name, s.id)));
        el.disabled = false;

        if (preselectId != null) {
            el.value = String(preselectId);
            // If subject not in teacher's list (e.g. imported data), fall back to full list
            if (!el.value) {
                el.innerHTML = '<option value="">— Pilih Mapel —</option>';
                all.forEach(s => el.add(new Option(s.name, s.id)));
                el.value = String(preselectId);
            }
        } else if (subs.length === 1) {
            el.value = String(subs[0].id);
        }

        filterClassesBySubject(el.value || null, preselectClassId ?? ($('#jfClass')?.value || null));
    }

    function getSubjectClassIds(subjectId) {
        if (!subjectId) return null;
        const map = window.JM_SUBJECT_CLASSES || {};
        const raw = map[String(subjectId)] || [];
        return Array.isArray(raw) ? raw.map(Number) : [];
    }

    function filterClassesBySubject(subjectId, preselectClassId = null) {
        const classEl = $('#jfClass');
        const hintEl  = $('#jfClassHint');
        if (!classEl) return;

        const allClasses = window.JM_CLASSES || [];
        const selectedValue = (preselectClassId ?? classEl.value ?? '') ? String(preselectClassId ?? classEl.value) : '';

        classEl.innerHTML = '<option value="">— Pilih Kelas —</option>';
        classEl.disabled = false;

        let options = allClasses;
        if (subjectId) {
            const allowedIds = getSubjectClassIds(subjectId) || [];
            options = allClasses.filter(c => allowedIds.includes(Number(c.id)));

            if (!options.length) {
                classEl.innerHTML = '<option value="">— Belum ada kelas untuk mapel ini —</option>';
                classEl.value = '';
                classEl.disabled = true;
                if (hintEl) {
                    hintEl.style.display = '';
                    hintEl.textContent = 'Mapel ini belum memiliki Penugasan Kelas. Atur dulu di menu Mata Pelajaran.';
                }
                return;
            }

            if (hintEl) {
                hintEl.style.display = '';
                hintEl.textContent = 'Menampilkan kelas yang sudah dipetakan di Penugasan Kelas mapel ini.';
            }
        } else if (hintEl) {
            hintEl.style.display = '';
            hintEl.textContent = 'Pilih mapel agar kelas difilter sesuai Penugasan Kelas mapel tersebut.';
        }

        options.forEach(c => classEl.add(new Option(c.label, c.id)));

        if (selectedValue && options.some(c => String(c.id) === selectedValue)) {
            classEl.value = selectedValue;
        } else {
            classEl.value = '';
        }
    }

    let _availableByDay = {};

    async function loadAvailablePeriods(teacherId, classId, ignoreId, preselectPeriodId, preselectDay) {
        // Don't clear the already pre-filled pickers — just refresh them after the API responds
        const params = new URLSearchParams();
        if (teacherId) params.set('teacher_id', teacherId);
        if (classId)   params.set('class_id',   classId);
        if (ignoreId)  params.set('ignore_id',  ignoreId);
        const url = `${R.available}?${params}`;

        try {
            const res  = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            _availableByDay = data.days || {};

            // Populate day select
            const dayEl = $('#jfDay');
            dayEl.innerHTML = '<option value="">— Pilih Hari —</option>';
            DAY_ORDER.forEach(day => {
                if (_availableByDay[day]?.length) {
                    dayEl.add(new Option(DAY_LABELS[day], day));
                }
            });

            // Determine which day to pre-select
            const targetDay = preselectDay || (() => {
                if (!preselectPeriodId) return null;
                for (const [day, periods] of Object.entries(_availableByDay)) {
                    if (periods.find(p => String(p.id) === String(preselectPeriodId))) return day;
                }
                return null;
            })();

            if (targetDay) {
                dayEl.value = targetDay;
                dayEl.dispatchEvent(new Event('change'));
                if (preselectPeriodId) {
                    setTimeout(() => { $('#jfPeriod').value = preselectPeriodId; }, 50);
                }
            }
        } catch (err) {
            console.error('loadAvailablePeriods:', err);
        }
        return Promise.resolve();
    }

    function populatePeriodSelect(day) {
        const el = $('#jfPeriod');
        el.innerHTML = '<option value="">— Pilih Jam —</option>';
        const periods = _availableByDay[day] || [];
        periods.forEach(p => el.add(new Option(p.label, p.id)));
    }

    function clearDayPeriodPickers() {
        $('#jfDay').innerHTML   = '<option value="">— Pilih Hari —</option>';
        $('#jfPeriod').innerHTML = '<option value="">— Pilih Jam —</option>';
        _availableByDay = {};
    }

    /* ── 7. SAVE ──────────────────────────────────────────── */
    function initSaveBtn() {
        $('#btnSimpanJadwal').addEventListener('click', async () => {
            const id     = $('#jfId').value;
            const method = $('#jfMethod').value;
            const url    = method === 'PATCH' ? `${R.base}/${id}` : R.store;

            hideFormError();
            setLoading('#btnSimpanJadwal', true);

            const body = new FormData($('#formJadwal'));
            if (method === 'PATCH') body.append('_method', 'PATCH');

            try {
                const res  = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body,
                });
                const data = await res.json();
                if (!res.ok) {
                    const msgs = data.errors
                        ? Object.values(data.errors).flat()
                        : [data.message || (method === 'PATCH'
                            ? 'Jadwal mengajar gagal diperbarui.'
                            : 'Jadwal mengajar gagal ditambahkan.')];
                    showFormError(msgs);
                    return;
                }
                closeModal('modalJadwal');
                toast(data.message || (method === 'PATCH' ? 'Jadwal mengajar berhasil diperbarui.' : 'Jadwal mengajar berhasil ditambahkan.'), 'success');
                loadGrid();
            } catch (err) {
                showFormError([err.message]);
            } finally {
                setLoading('#btnSimpanJadwal', false);
            }
        });
    }

    /* ── 8. DELETE ───────────────────────────────────────── */
    let pendingDeleteId = null;
    function initDelete() {
        $('#btnKonfirmasiHapusJadwal').addEventListener('click', async () => {
            if (!pendingDeleteId) return;
            setLoading('#btnKonfirmasiHapusJadwal', true);
            try {
                const res = await fetch(`${R.base}/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Jadwal mengajar gagal dihapus.');
                closeModal('modalHapusJadwal');
                toast(data.message || 'Jadwal mengajar berhasil dihapus.', 'success');
                loadGrid();
            } catch (err) {
                toast(err.message, 'error');
            } finally {
                setLoading('#btnKonfirmasiHapusJadwal', false);
                pendingDeleteId = null;
            }
        });
    }

    /* ── Helpers ──────────────────────────────────────────── */
    function resetForm() {
        $('#formJadwal').reset();
        $('#jfId').value = '';
        _pendingPreselect = { periodId: null, day: null };
        filterSubjectsByTeacher(null); // restore full subject + class list
        hideFormError();
    }
    function showFormError(msgs) {
        const el = $('#formJadwalError');
        el.style.display = 'flex';
        el.innerHTML = msgs.map(m => `<div>${m}</div>`).join('');
    }
    function hideFormError() {
        const el = $('#formJadwalError');
        if (el) { el.style.display = 'none'; el.innerHTML = ''; }
    }
    function setLoading(sel, on) {
        const btn = $(sel);
        if (!btn) return;
        btn.disabled = on;
        btn.querySelector('.spinner')?.classList.toggle('active', on);
    }

    /* ── EXCEL DROPZONE (Import modal) ───────────────────── */
    function initExcelDropzone() {
        const dropzone   = $('#jm-excelDropzone');
        const fileInput  = $('#jm-excelFileInput');
        const fileChosen = $('#jm-excelFileChosen');
        const fileName   = $('#jm-excelFileName');
        const fileClear  = $('#jm-excelFileClear');
        const browseBtn  = dropzone?.querySelector('.excel-dropzone__btn');

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
    document.addEventListener('DOMContentLoaded', () => {
        initModeTabs();
        initPicker();
        initTambahBtn();
        initFormWatchers();
        initSaveBtn();
        initDelete();
        PSC.initOpenButtons();
        initModalClose();
        initAlerts();
        initExcelDropzone();
        PSC.initFormSpinner();

        // Expose callbacks for grid inline scripts
        window._jmOpenEdit   = d => openEditModal(d);
        window._jmOpenDelete = (id, desc) => {
            pendingDeleteId = id;
            $('#hapusJadwalDesc').textContent = desc;
            openModal('modalHapusJadwal');
        };
        window._jmOpenAdd = ({ periodId, day } = {}) => openAddModal({ periodId, day });
    });

})();
