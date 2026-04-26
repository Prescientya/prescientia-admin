/* ============================================================
   JAM PELAJARAN — main.js
   ============================================================ */
(function () {
    'use strict';

    const { $, $$, openModal, closeModal } = window.PSC;
    const R = window.JP_ROUTES;
    const CSRF = R.csrf;

    /* ── 1. TABS ──────────────────────────────────────────── */
    function initTabs() {
        $$('.jp-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                $$('.jp-tab-btn').forEach(b => b.classList.remove('active'));
                $$('.jp-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                const panel = $('#panel-' + btn.dataset.day);
                if (panel) panel.classList.add('active');

                // Sync the "Hari" dropdown in modal to active tab
                const daySelect = $('#jam_day');
                if (daySelect) daySelect.value = btn.dataset.day;
            });
        });
    }

    /* ── 2. DURASI HINT ───────────────────────────────────── */
    function initDurasiHint() {
        const start = $('#jam_start');
        const end   = $('#jam_end');
        const hint  = $('#durasiHint');
        function update() {
            if (!start.value || !end.value) { hint.textContent = ''; return; }
            const [sh, sm] = start.value.split(':').map(Number);
            const [eh, em] = end.value.split(':').map(Number);
            const dur = (eh * 60 + em) - (sh * 60 + sm);
            hint.textContent = dur > 0 ? `Durasi: ${dur} menit` : 'Waktu selesai harus setelah mulai';
            hint.style.color = dur > 0 ? 'var(--accent)' : '#dc2626';
        }
        start?.addEventListener('input', update);
        end?.addEventListener('input', update);
    }

    /* ── 3. TAMBAH JAM button ─────────────────────────────── */
    function initTambahBtn() {
        $('#btnTambahJam')?.addEventListener('click', () => {
            resetForm();
            $('#modalJamTitle').textContent = 'Tambah Jam Pelajaran';
            $('#jamMethod').value = 'POST';
            // Pre-select active tab day
            const activeTab = $('.jp-tab-btn.active');
            if (activeTab) $('#jam_day').value = activeTab.dataset.day;
            openModal('modalJam');
        });
    }

    /* ── 4. EDIT buttons (delegated) ──────────────────────── */
    function initEditBtns() {
        document.addEventListener('click', e => {
            const btn = e.target.closest('.jp-dropdown-edit');
            if (!btn) return;
            resetForm();
            $('#modalJamTitle').textContent = 'Edit Jam Pelajaran';
            $('#jamId').value          = btn.dataset.id;
            $('#jamMethod').value      = 'PATCH';
            $('#jam_day').value        = btn.dataset.day;
            $('#jam_sequence').value   = btn.dataset.sequence;
            $('#jam_start').value      = btn.dataset.start;
            $('#jam_end').value        = btn.dataset.end;
            $('#jam_type').value       = btn.dataset.type;
            $('#jam_note').value       = btn.dataset.note;
            $('#durasiHint').textContent = '';
            openModal('modalJam');
            // Trigger hint
            $('#jam_start').dispatchEvent(new Event('input'));
        });
    }

    /* ── 5. DELETE buttons (delegated) ───────────────────── */
    let pendingDeleteId = null;
    function initDeleteBtns() {
        document.addEventListener('click', e => {
            const btn = e.target.closest('.jp-dropdown-del');
            if (!btn) return;
            pendingDeleteId = btn.dataset.id;
            $('#hapusJamDesc').textContent = btn.dataset.desc;
            openModal('modalHapusJam');
        });

        $('#btnKonfirmasiHapus')?.addEventListener('click', async () => {
            if (!pendingDeleteId) return;
            setLoading('#btnKonfirmasiHapus', true);

            try {
                const res = await fetch(`${R.base}/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Jam pelajaran gagal dihapus.');
                closeModal('modalHapusJam');
                await refreshDay(data.day);
                PSC.toast(data.message || 'Jam pelajaran berhasil dihapus.', 'success');
            } catch (err) {
                PSC.toast(err.message, 'error');
            } finally {
                setLoading('#btnKonfirmasiHapus', false);
                pendingDeleteId = null;
            }
        });
    }

    /* ── 6. SAVE (tambah / edit) ──────────────────────────── */
    function initSaveBtn() {
        $('#btnSimpanJam')?.addEventListener('click', async () => {
            const id     = $('#jamId').value;
            const method = $('#jamMethod').value;
            const url    = method === 'PATCH' ? `${R.base}/${id}` : R.store;

            hideError();
            setLoading('#btnSimpanJam', true);

            const body = new FormData($('#formJam'));
            if (method === 'PATCH') body.append('_method', 'PATCH');

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body,
                });
                const data = await res.json();
                if (!res.ok) {
                    showErrors(data.errors || { _: [data.message || (method === 'PATCH' ? 'Jam pelajaran gagal diperbarui.' : 'Jam pelajaran gagal ditambahkan.')] });
                    return;
                }
                closeModal('modalJam');
                await refreshDay(data.day);
                PSC.toast(data.message || (method === 'PATCH' ? 'Jam pelajaran berhasil diperbarui.' : 'Jam pelajaran berhasil ditambahkan.'), 'success');
            } catch (err) {
                showErrors({ _: [err.message] });
            } finally {
                setLoading('#btnSimpanJam', false);
            }
        });
    }

    /* ── 7. RESET button ──────────────────────────────────── */
    function initResetBtns() {
        $$('.jp-reset-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm(`Reset jadwal ${btn.dataset.day} ke template default? Data yang sudah diubah akan hilang.`)) return;

                btn.disabled = true;
                try {
                    const body = new FormData();
                    body.append('day', btn.dataset.day);
                    const res = await fetch(`${R.base}/reset`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body,
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Reset gagal');
                    await refreshDay(data.day);
                    PSC.toast(data.message || 'Jadwal jam pelajaran berhasil direset.', 'success');
                } catch (err) {
                    PSC.toast(err.message, 'error');
                } finally {
                    btn.disabled = false;
                }
            });
        });
    }
    /* ── 9. IMPORT button ──────────────────────────────────────── */
    function initImportBtn() {
        $('#btnImportJam')?.addEventListener('click', () => {
            openModal('modalImportJam');
        });
    }

    /* ── 10. IMPORT DROPZONE ───────────────────────────────────── */
    function initImportDropzone() {
        const zone      = $('#jp-excelDropzone');
        const input     = $('#jp-excelFileInput');
        const chosen    = $('#jp-excelFileChosen');
        const fileName  = $('#jp-excelFileName');
        const clearBtn  = $('#jp-excelFileClear');

        if (!zone || !input) return;

        const showFile = file => {
            if (fileName) fileName.textContent = file.name;
            chosen?.classList.add('show');
            zone.style.display = 'none';
        };

        const clearFile = () => {
            input.value = '';
            if (fileName) fileName.textContent = '–';
            chosen?.classList.remove('show');
            zone.style.display = '';
        };

        const browseBtn = zone.querySelector('.excel-dropzone__btn');
        browseBtn?.addEventListener('click', e => { e.stopPropagation(); input.click(); });

        zone.addEventListener('click', e => {
            if (e.target !== browseBtn && !browseBtn?.contains(e.target)) input.click();
        });
        zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                showFile(file);
            }
        });

        input.addEventListener('change', () => {
            if (input.files[0]) showFile(input.files[0]);
        });

        clearBtn?.addEventListener('click', e => {
            e.stopPropagation();
            clearFile();
        });

        // Reset dropzone when modal is closed
        $('#modalImportJam')?.addEventListener('click', e => {
            if (e.target.closest('[data-close-modal]') || e.target === $('#modalImportJam')) {
                clearFile();
            }
        });
    }
    /* ── 8. REFRESH table HTML for a day ─────────────────── */
    async function refreshDay(day) {
        const res = await fetch(`${R.base}/${day}/table`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        const tableWrap = $(`#table-${day}`);
        if (tableWrap) tableWrap.innerHTML = data.html;

        // Update lesson count badge in tab
        const badge = $(`#count-${day}`);
        if (badge) badge.textContent = data.lesson_count + ' JP';

        // Re-init 3-dot dropdowns for the newly inserted rows
        PSC.initActionDropdowns('.jp-action__btn', '.jp-dropdown');
    }

    /* ── Helpers ──────────────────────────────────────────── */
    function resetForm() {
        $('#formJam').reset();
        $('#jamId').value = '';
        $('#durasiHint').textContent = '';
        hideError();
    }

    function showErrors(errs) {
        const el = $('#formJamError');
        el.style.display = 'flex';
        el.innerHTML = Object.values(errs).flat().map(m => `<div>${m}</div>`).join('');
    }

    function hideError() {
        const el = $('#formJamError');
        if (el) { el.style.display = 'none'; el.innerHTML = ''; }
    }

    function setLoading(selector, loading) {
        const btn = $(selector);
        if (!btn) return;
        btn.disabled = loading;
        btn.querySelector('.spinner')?.classList.toggle('active', loading);
    }

    /* ── INIT ─────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        initTabs();
        initDurasiHint();
        initTambahBtn();
        initImportBtn();
        initImportDropzone();
        initEditBtns();
        initDeleteBtns();
        initSaveBtn();
        initResetBtns();
        PSC.initModalClose();
        PSC.initAlerts();
        PSC.initFormSpinner();
        PSC.initActionDropdowns('.jp-action__btn', '.jp-dropdown');
    });

})();
