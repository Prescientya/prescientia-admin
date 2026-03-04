/**
 * Kehadiran Guru Per Jam – period_main.js
 */
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const STATUSES = [
        { value: 'hadir',     label: 'Hadir',     color: '#16a34a' },
        { value: 'sakit',     label: 'Sakit',      color: '#d97706' },
        { value: 'izin',      label: 'Izin',       color: '#2563eb' },
        { value: 'dinas',     label: 'Dinas',      color: '#0d9488' },
        { value: 'alpa',      label: 'Alpa',       color: '#dc2626' },
        { value: 'terlambat', label: 'Terlambat',  color: '#7c3aed' },
    ];

    function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

    let picker = document.getElementById('statusPicker');
    let currentCell = null;

    function createPicker() {
        if (picker) return;
        picker = document.createElement('div');
        picker.id = 'statusPicker';
        picker.className = 'status-picker';
        STATUSES.forEach(s => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'status-picker__item';
            btn.dataset.status = s.value;
            btn.innerHTML = `<span class="status-picker__dot" style="background:${s.color};"></span>${s.label}`;
            btn.addEventListener('click', () => selectStatus(s.value));
            picker.appendChild(btn);
        });
        document.body.appendChild(picker);
    }

    function openPicker(cell) {
        createPicker();
        currentCell = cell;
        const rect = cell.getBoundingClientRect();
        picker.style.top  = (rect.bottom + 4) + 'px';
        picker.style.left = rect.left + 'px';
        picker.classList.add('open');
        const pRect = picker.getBoundingClientRect();
        if (pRect.right > window.innerWidth) picker.style.left = (window.innerWidth - pRect.width - 8) + 'px';
        if (pRect.bottom > window.innerHeight) picker.style.top = (rect.top - pRect.height - 4) + 'px';
    }

    function closePicker() {
        if (picker) picker.classList.remove('open');
        currentCell = null;
    }

    async function selectStatus(newStatus) {
        if (!currentCell) return;
        const cell = currentCell;
        const teacherId = cell.dataset.teacherId;
        const periodId  = cell.dataset.periodId;
        const dateVal   = document.getElementById('filterDate')?.value;

        closePicker();
        cell.classList.add('saving');
        const oldClass = cell.className;
        const oldText  = cell.textContent;

        cell.className = `status-cell status-cell--${newStatus}`;
        cell.textContent = capitalize(newStatus);

        try {
            const res = await fetch(cell.dataset.storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    teacher_id: teacherId,
                    class_period_id: periodId,
                    date: dateVal,
                    status: newStatus,
                }),
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.message || 'Failed');
            cell.classList.remove('saving');
            if (typeof PSC !== 'undefined' && PSC.toast) PSC.toast('Status disimpan', 'success');
        } catch (err) {
            cell.className = oldClass;
            cell.textContent = oldText;
            cell.classList.remove('saving');
            if (typeof PSC !== 'undefined' && PSC.toast) PSC.toast('Gagal menyimpan: ' + err.message, 'error');
        }
    }

    function initCells() {
        document.querySelectorAll('.status-cell').forEach(cell => {
            cell.addEventListener('click', function (e) {
                e.stopPropagation();
                if (currentCell === this) closePicker();
                else openPicker(this);
            });
        });
    }

    document.addEventListener('click', function (e) {
        if (picker && !picker.contains(e.target) && !e.target.classList.contains('status-cell')) closePicker();
    });

    function initAutoFill() {
        const btn = document.getElementById('btnAutoFill');
        if (!btn) return;
        btn.addEventListener('click', async function () {
            const dateVal = document.getElementById('filterDate')?.value;
            const url = btn.dataset.url;

            if (!dateVal) {
                if (typeof PSC !== 'undefined' && PSC.toast) PSC.toast('Pilih tanggal terlebih dahulu', 'error');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Memproses...';
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ date: dateVal }),
                });
                const data = await res.json();
                if (data.ok) {
                    if (typeof PSC !== 'undefined' && PSC.toast) PSC.toast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    if (typeof PSC !== 'undefined' && PSC.toast) PSC.toast(data.message || 'Gagal auto-fill', 'error');
                }
            } catch (err) {
                if (typeof PSC !== 'undefined' && PSC.toast) PSC.toast('Gagal: ' + err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg> Auto-isi dari Absensi Harian`;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCells();
        initAutoFill();
    });
})();
