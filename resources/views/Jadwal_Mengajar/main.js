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

    let currentMode = 'teacher'; // 'teacher' | 'class'
    let currentId   = null;

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
            wrap.innerHTML = `<div class="jm-empty">
                <div class="jm-empty-icon">⚠️</div>
                <div class="jm-empty-title" style="color:#dc2626;">Gagal memuat jadwal</div>
                <div class="jm-empty-sub" style="color:#dc2626;">${err.message}</div>
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

        // Edit button
        wrap.querySelectorAll('.jm-edit-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                openEditModal(btn.dataset);
            });
        });

        // Delete button
        wrap.querySelectorAll('.jm-del-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                pendingDeleteId = btn.dataset.id;
                $('#hapusJadwalDesc').textContent = btn.dataset.desc;
                openModal('modalHapusJadwal');
            });
        });
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

        // Pre-fill teacher/class from current mode selection
        if (currentId) {
            if (currentMode === 'teacher') $('#jfTeacher').value = currentId;
            else                            $('#jfClass').value   = currentId;
        }

        clearDayPeriodPickers();
        openModal('modalJadwal');

        // If teacher + class already selected, load available periods
        const t = $('#jfTeacher').value;
        const c = $('#jfClass').value;
        if (t && c) loadAvailablePeriods(t, c, null, periodId, day);
    }

    /* ── 5. EDIT MODAL ───────────────────────────────────── */
    function openEditModal(d) {
        resetForm();
        $('#modalJadwalTitle').textContent = 'Edit Jadwal Mengajar';
        $('#jfId').value          = d.id;
        $('#jfMethod').value      = 'PATCH';
        $('#jfTeacher').value     = d.teacher;
        $('#jfSubject').value     = d.subject;
        $('#jfClass').value       = d.class;

        clearDayPeriodPickers();

        // Pass preselectPeriodId + preselectDay so loadAvailablePeriods sets the selects automatically
        loadAvailablePeriods(d.teacher, d.class, d.id, d.period, d.day);

        openModal('modalJadwal');
    }

    /* ── 6. TEACHER + CLASS CHANGE → load periods ─────── */
    function initFormWatchers() {
        ['#jfTeacher', '#jfClass'].forEach(sel => {
            $(sel)?.addEventListener('change', () => {
                const t = $('#jfTeacher').value;
                const c = $('#jfClass').value;
                clearDayPeriodPickers();
                if (t && c) loadAvailablePeriods(t, c, $('#jfId').value || null, null);
            });
        });

        $('#jfDay').addEventListener('change', () => {
            populatePeriodSelect($('#jfDay').value);
        });
    }

    let _availableByDay = {};

    async function loadAvailablePeriods(teacherId, classId, ignoreId, preselectPeriodId, preselectDay) {
        clearDayPeriodPickers();
        let url = `${R.available}?teacher_id=${teacherId}&class_id=${classId}`;
        if (ignoreId) url += `&ignore_id=${ignoreId}`;

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
                        : [data.message || 'Terjadi kesalahan'];
                    showFormError(msgs);
                    return;
                }
                closeModal('modalJadwal');
                toast(method === 'PATCH' ? 'Jadwal berhasil diperbarui.' : 'Jadwal berhasil ditambahkan.', 'success');
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
                if (!res.ok) throw new Error((await res.json()).message || 'Gagal menghapus');
                closeModal('modalHapusJadwal');
                toast('Jadwal berhasil dihapus.', 'success');
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
