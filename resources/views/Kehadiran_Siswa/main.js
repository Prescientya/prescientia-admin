/**
 * Kehadiran Siswa – main.js
 * Handles: action dropdowns, detail modal, edit modal (status + time),
 *          delete confirm, manual input modal + autocomplete.
 */
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── Helpers ─────────────────────────────────────────── */
    function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function sourceLabel(source) {
        const map = {
            manual:          { label: 'Input Manual',    cls: 'source-badge--manual'      },
            digital_wifi:    { label: 'WiFi / Sensor',   cls: 'source-badge--wifi'        },
            guru_pengajar:   { label: 'Guru Pengajar',   cls: 'source-badge--manual'      },
            wali_kelas:      { label: 'Wali Kelas',      cls: 'source-badge--manual'      },
            self_report:     { label: 'Self Report',     cls: 'source-badge--self_report' },
        };
        const cfg = map[source] ?? { label: source ?? 'Tidak diketahui', cls: 'source-badge--manual' };
        return `<span class="source-badge ${cfg.cls}">${cfg.label}</span>`;
    }

    function statusBadge(status) {
        return `<span class="att-badge att-badge--${status}">${capitalize(status)}</span>`;
    }

    /* ════════════════════════════════════════════════════════
       1. DETAIL MODAL
    ════════════════════════════════════════════════════════ */
    const detailModal = document.getElementById('modalDetail');

    function initDetailModal() {
        document.querySelectorAll('[data-detail-btn]').forEach(btn => {
            btn.addEventListener('click', function () {
                const d = this.dataset;
                this.closest('.ka-dropdown')?.classList.remove('open');

                document.getElementById('detail-name').textContent      = d.name     || '–';
                document.getElementById('detail-nis').textContent       = d.nis      || '–';
                document.getElementById('detail-kelas').textContent     = d.kelas    || '–';
                document.getElementById('detail-tanggal').textContent   = d.tanggal  || '–';
                document.getElementById('detail-check-in').textContent  = d.checkIn  || '–';
                document.getElementById('detail-check-out').textContent = d.checkOut || '–';
                document.getElementById('detail-status').innerHTML      = statusBadge(d.status);
                document.getElementById('detail-source').innerHTML      = sourceLabel(d.source);

                detailModal?.classList.add('open');
            });
        });
    }

    /* ════════════════════════════════════════════════════════
       2. EDIT MODAL (status + waktu)
    ════════════════════════════════════════════════════════ */
    const editModal    = document.getElementById('modalEditAbsensi');
    const editForm     = document.getElementById('formEditAbsensi');
    const editStatus   = document.getElementById('editStatus');
    const editCheckIn  = document.getElementById('editCheckIn');
    const editCheckOut = document.getElementById('editCheckOut');
    let   currentEditId = null;

    function initEditModal() {
        document.querySelectorAll('[data-edit-btn]').forEach(btn => {
            btn.addEventListener('click', function () {
                const d = this.dataset;
                currentEditId = d.attId;
                document.getElementById('editModalName').textContent = d.name || '';
                editStatus.value   = d.status   || 'hadir';
                editCheckIn.value  = d.checkIn  || '';
                editCheckOut.value = d.checkOut || '';
                this.closest('.ka-dropdown')?.classList.remove('open');
                editModal?.classList.add('open');
            });
        });

        editForm?.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!currentEditId) return;

            const submitBtn = this.querySelector('[type="submit"]');
            submitBtn.disabled = true;

            try {
                const res = await fetch(`/attendance/siswa/${currentEditId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        status:         editStatus.value,
                        check_in_time:  editCheckIn.value  || null,
                        check_out_time: editCheckOut.value || null,
                    }),
                });

                if (!res.ok) throw new Error('Request failed');
                const data = await res.json();

                if (data.ok) {
                    // Update badge
                    const badge = document.getElementById('badge-' + currentEditId);
                    if (badge) {
                        badge.className   = `att-badge att-badge--${data.status}`;
                        badge.textContent = capitalize(data.status);
                    }
                    // Update time cells
                    const inEl  = document.getElementById('ci-' + currentEditId);
                    const outEl = document.getElementById('co-' + currentEditId);
                    if (inEl) {
                        inEl.textContent = data.check_in || '–';
                        inEl.className   = `att-time${data.check_in ? '' : ' att-time--empty'}`;
                    }
                    if (outEl) {
                        outEl.textContent = data.check_out || '–';
                        outEl.className   = `att-time${data.check_out ? '' : ' att-time--empty'}`;
                    }
                    // Refresh data-attrs on action buttons so next open is accurate
                    document.querySelectorAll(`[data-edit-btn][data-att-id="${currentEditId}"]`).forEach(b => {
                        b.dataset.status   = data.status;
                        b.dataset.checkIn  = data.check_in  ?? '';
                        b.dataset.checkOut = data.check_out ?? '';
                    });
                    document.querySelectorAll(`[data-detail-btn][data-att-id="${currentEditId}"]`).forEach(b => {
                        b.dataset.status   = data.status;
                        b.dataset.checkIn  = data.check_in  ?? '';
                        b.dataset.checkOut = data.check_out ?? '';
                    });

                    editModal.classList.remove('open');
                    document.body.style.overflow = '';
                    PSC.toast('Absensi berhasil diperbarui', 'success');
                }
            } catch {
                PSC.toast('Gagal menyimpan perubahan', 'error');
            } finally {
                submitBtn.disabled = false;
            }
        });
    }

    /* ════════════════════════════════════════════════════════
       3. DELETE
    ════════════════════════════════════════════════════════ */
    const deleteModal   = document.getElementById('modalDeleteConfirm');
    const confirmDelBtn = document.getElementById('confirmDeleteBtn');
    let   currentDeleteId = null;

    function initDeleteModal() {
        document.querySelectorAll('[data-delete-btn]').forEach(btn => {
            btn.addEventListener('click', function () {
                currentDeleteId = this.dataset.attId;
                document.getElementById('deleteStudentName').textContent = this.dataset.name || '';
                this.closest('.ka-dropdown')?.classList.remove('open');
                deleteModal?.classList.add('open');
            });
        });

        confirmDelBtn?.addEventListener('click', async function () {
            if (!currentDeleteId) return;
            this.disabled = true;

            try {
                const res = await fetch(`/attendance/siswa/${currentDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                });

                if (!res.ok) throw new Error('Request failed');
                const data = await res.json();

                if (data.ok) {
                    const row = document.getElementById('row-' + currentDeleteId);
                    if (row) {
                        row.style.cssText += ';transition:opacity .3s,transform .3s;opacity:0;transform:translateX(20px)';
                        setTimeout(() => row.remove(), 300);
                    }
                    deleteModal.classList.remove('open');
                    document.body.style.overflow = '';
                    PSC.toast('Data absensi dihapus', 'success');
                }
            } catch {
                PSC.toast('Gagal menghapus data', 'error');
            } finally {
                this.disabled = false;
            }
        });
    }

    /* ════════════════════════════════════════════════════════
       4. AUTOCOMPLETE (manual input modal)
    ════════════════════════════════════════════════════════ */
    function initAutocomplete() {
        const nameInput  = document.getElementById('manualNama');
        const list       = document.getElementById('autocompleteList');
        const hiddenId   = document.getElementById('manualStudentId');
        const kelasDisp  = document.getElementById('manualKelasDisplay');

        if (!nameInput || !list) return;

        let debounce;

        nameInput.addEventListener('input', function () {
            clearTimeout(debounce);
            const q = this.value.trim();

            if (!q) {
                list.classList.remove('open');
                list.innerHTML = '';
                if (hiddenId)  hiddenId.value = '';
                if (kelasDisp) kelasDisp.textContent = '–';
                return;
            }

            debounce = setTimeout(async () => {
                try {
                    const res  = await fetch(`/attendance/siswa/search?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    list.innerHTML = '';

                    if (data.length === 0) {
                        list.innerHTML = `<div class="autocomplete-empty">Tidak ada siswa bernama "<strong>${escHtml(q)}</strong>"</div>`;
                    } else {
                        data.forEach(s => {
                            const div = document.createElement('div');
                            div.className = 'autocomplete-item';
                            div.innerHTML = `
                                <span class="autocomplete-item__name">${escHtml(s.name)}</span>
                                <span class="autocomplete-item__meta">NIS ${escHtml(s.nis ?? '–')} &bull; ${escHtml(s.kelas ?? '–')}</span>
                            `;
                            div.addEventListener('mousedown', (e) => {
                                e.preventDefault();
                                nameInput.value = s.name;
                                if (hiddenId)  hiddenId.value = s.id;
                                if (kelasDisp) kelasDisp.textContent = s.kelas || '–';
                                list.classList.remove('open');
                            });
                            list.appendChild(div);
                        });
                    }

                    list.classList.add('open');
                } catch { /* ignore */ }
            }, 280);
        });

        nameInput.addEventListener('blur',  () => setTimeout(() => list.classList.remove('open'), 150));
        nameInput.addEventListener('focus', () => { if (list.children.length) list.classList.add('open'); });
    }

    /* ── Boot ────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        PSC.initModalClose();
        PSC.initOpenButtons();
        PSC.initActionDropdowns('.ka-action__btn', '.ka-dropdown');

        initDetailModal();
        initEditModal();
        initDeleteModal();
        initAutocomplete();
    });
})();
