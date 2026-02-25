/* ================================================================
   Mapel (Mata Pelajaran) — main.js
================================================================ */
(function () {
    'use strict';

    const D = window.MP_DATA;

    // ─── DOM refs ─────────────────────────────────────────────────
    const overlay    = document.getElementById('mp-overlay');
    const modal      = document.getElementById('mp-modal');
    const form       = document.getElementById('mp-form');
    const titleEl    = document.getElementById('mp-modal-title');
    const subjectId  = document.getElementById('mp-subject-id');
    const methodEl   = document.getElementById('mp-method');
    const nameEl     = document.getElementById('mp-name');
    const descEl     = document.getElementById('mp-desc');
    const activeEl   = document.getElementById('mp-active');
    const activeLbl  = document.getElementById('mp-active-label');
    const saveBtn    = document.getElementById('mp-save-btn');
    const previewEl  = document.getElementById('mp-preview');
    const ruleInput  = document.getElementById('mp-rule-type');
    const toast      = document.getElementById('mp-toast');

    // ─── Tabs (modal) ─────────────────────────────────────────────
    document.querySelectorAll('.mp-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.mp-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.mp-tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.querySelector(`[data-panel="${btn.dataset.tab}"]`).classList.add('active');
        });
    });

    // ─── Rule pills ───────────────────────────────────────────────
    document.querySelectorAll('.mp-rule-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.mp-rule-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            ruleInput.value = pill.dataset.rule;
            document.querySelectorAll('.mp-rule-panel').forEach(p => p.classList.remove('active'));
            document.querySelector(`[data-rulepanel="${pill.dataset.rule}"]`).classList.add('active');
            updatePreview();
        });
    });

    // ─── Checkbox change → update preview ─────────────────────────
    document.querySelectorAll(
        '.mp-grade-cb, .mp-major-cb, .mp-tj-grade-cb, .mp-tj-major-cb, .mp-manual-cb'
    ).forEach(cb => cb.addEventListener('change', updatePreview));

    // ─── Toggle active label ──────────────────────────────────────
    activeEl.addEventListener('change', () => {
        activeLbl.textContent = activeEl.checked ? 'Aktif' : 'Non-Aktif';
    });

    // ─── Preview updater ──────────────────────────────────────────
    function updatePreview() {
        const rule = ruleInput.value;
        let text = '';

        if (rule === 'semua') {
            text = `Akan diterapkan ke <strong>semua ${D.totalClasses} kelas</strong>.`;
        } else if (rule === 'tingkat') {
            const grades = [...document.querySelectorAll('.mp-grade-cb:checked')].map(c => `Kelas ${c.value}`);
            text = grades.length
                ? `Tingkat: <strong>${grades.join(', ')}</strong>`
                : '<em>Pilih setidaknya satu tingkat.</em>';
        } else if (rule === 'jurusan') {
            const jurusans = [...document.querySelectorAll('.mp-major-cb:checked')].map(c => c.value);
            text = jurusans.length
                ? `Jurusan: <strong>${jurusans.join(', ')}</strong>`
                : '<em>Pilih setidaknya satu jurusan.</em>';
        } else if (rule === 'tingkat_jurusan') {
            const grades   = [...document.querySelectorAll('.mp-tj-grade-cb:checked')].map(c => `Kelas ${c.value}`);
            const jurusans = [...document.querySelectorAll('.mp-tj-major-cb:checked')].map(c => c.value);
            if (grades.length && jurusans.length) {
                text = `<strong>${grades.join(', ')}</strong> &mdash; Jurusan: <strong>${jurusans.join(', ')}</strong>`;
            } else {
                text = '<em>Pilih tingkat dan jurusan.</em>';
            }
        } else if (rule === 'manual') {
            const cnt = document.querySelectorAll('.mp-manual-cb:checked').length;
            text = cnt
                ? `Dipilih <strong>${cnt} kelas</strong> secara manual.`
                : '<em>Pilih setidaknya satu kelas.</em>';
        }

        previewEl.innerHTML = text;
    }

    // ─── Open modal (add) ─────────────────────────────────────────
    document.getElementById('mp-add-btn').addEventListener('click', () => openModal());

    // ─── Open modal (edit) ────────────────────────────────────────
    document.querySelectorAll('.mp-btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            openModal({
                id,
                name: btn.dataset.name,
                description: btn.dataset.description,
                is_active: btn.dataset.active === '1',
            });
            // Load existing class assignments
            loadClassOptions(id);
        });
    });

    // ─── Delete ───────────────────────────────────────────────────
    document.querySelectorAll('.mp-btn-del').forEach(btn => {
        btn.addEventListener('click', () => {
            const id   = btn.dataset.id;
            const name = btn.dataset.name;
            if (!confirm(`Hapus mata pelajaran "${name}"? Semua penugasan kelas akan ikut dihapus.`)) return;
            destroySubject(id, btn.closest('tr'));
        });
    });

    // ─── Close modal ─────────────────────────────────────────────
    document.getElementById('mp-modal-close').addEventListener('click', closeModal);
    document.getElementById('mp-cancel-btn').addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

    // ─── Form submit ──────────────────────────────────────────────
    form.addEventListener('submit', async e => {
        e.preventDefault();
        if (!nameEl.value.trim()) {
            showToast('Nama mata pelajaran wajib diisi.', 'error');
            // switch to info tab
            document.querySelector('[data-tab="info"]').click();
            nameEl.focus();
            return;
        }

        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan…';

        const fd  = new FormData(form);
        const sid = subjectId.value;
        const url = sid ? `${D.updateUrl}/${sid}` : D.storeUrl;
        const method = sid ? 'PUT' : 'POST';

        // Build plain object so we can send JSON (FormData sends _method correctly)
        const body = new URLSearchParams();
        for (const [k, v] of fd.entries()) {
            if (k === '_subject_id') continue;
            body.append(k, v);
        }
        if (sid) body.set('_method', 'PUT');

        try {
            const res  = await fetch(url, {
                method: 'POST',        // always POST, spoofed via _method
                headers: {
                    'X-CSRF-TOKEN': D.csrfToken,
                    'Accept': 'application/json',
                },
                body,
            });
            const data = await res.json();
            if (!res.ok) {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : (data.message || 'Terjadi kesalahan.');
                showToast(msg, 'error');
                return;
            }
            showToast(data.message || 'Berhasil.', 'success');
            closeModal();
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast('Gagal terhubung ke server.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan';
        }
    });

    // ─── loadClassOptions (for edit) ─────────────────────────────
    async function loadClassOptions(id) {
        try {
            const res  = await fetch(`${D.classOptionsUrl}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': D.csrfToken },
            });
            const data = await res.json();
            if (!data.ok) return;

            const ids = data.classIds.map(Number);

            // Switch to manual mode and check the right boxes
            document.querySelector('[data-rule="manual"]').click();

            document.querySelectorAll('.mp-manual-cb').forEach(cb => {
                cb.checked = ids.includes(Number(cb.value));
            });
            updatePreview();
        } catch (_) { /* silent */ }
    }

    // ─── destroySubject ───────────────────────────────────────────
    async function destroySubject(id, row) {
        try {
            const res  = await fetch(`${D.destroyUrl}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': D.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: '_method=DELETE&_token=' + D.csrfToken,
            });
            const data = await res.json();
            if (!res.ok) { showToast(data.message || 'Gagal menghapus.', 'error'); return; }
            showToast(data.message || 'Berhasil dihapus.', 'success');
            row?.remove();
        } catch (_) {
            showToast('Gagal terhubung ke server.', 'error');
        }
    }

    // ─── helpers ─────────────────────────────────────────────────
    function openModal(subject = null) {
        resetModal();
        if (subject) {
            titleEl.textContent = 'Edit Mata Pelajaran';
            subjectId.value  = subject.id;
            methodEl.value   = 'PUT';
            nameEl.value     = subject.name;
            descEl.value     = subject.description || '';
            activeEl.checked = subject.is_active;
            activeLbl.textContent = subject.is_active ? 'Aktif' : 'Non-Aktif';
        } else {
            titleEl.textContent = 'Tambah Mata Pelajaran';
        }
        // always start on Info tab when opening
        document.querySelector('[data-tab="info"]').click();
        overlay.classList.add('open');
        nameEl.focus();
    }

    function closeModal() {
        overlay.classList.remove('open');
    }

    function resetModal() {
        form.reset();
        subjectId.value  = '';
        methodEl.value   = 'POST';
        activeLbl.textContent = 'Aktif';
        // reset rule to semua
        document.querySelector('[data-rule="semua"]').click();
        document.querySelectorAll('.mp-rule-pill').forEach(p => p.classList.remove('active'));
        document.querySelector('[data-rule="semua"]').classList.add('active');
        document.querySelectorAll('.mp-rule-panel').forEach(p => p.classList.remove('active'));
        document.querySelector('[data-rulepanel="semua"]').classList.add('active');
        ruleInput.value = 'semua';
        updatePreview();
    }

    let toastTimer;
    function showToast(msg, type = '') {
        clearTimeout(toastTimer);
        toast.textContent = msg;
        toast.className = 'mp-toast show ' + type;
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    // ─── Initial preview ─────────────────────────────────────────
    updatePreview();

})();
