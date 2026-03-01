/* ============================================================
   EVENTS – main.js
   Page-specific logic for Event CRUD
   ============================================================ */
(function () {
    'use strict';

    const { $, $$, openModal, closeModal, initActionDropdowns } = window.PSC;

    /* ── Target Audience Toggle (Create / Edit pages) ──── */
    const targetSelect = $('#targetAudience');
    const targetSection = $('#targetKelasSection');

    if (targetSelect && targetSection) {
        function toggleTargetSection() {
            if (targetSelect.value === 'kelas') {
                targetSection.style.display = '';
            } else {
                targetSection.style.display = 'none';
            }
        }
        targetSelect.addEventListener('change', toggleTargetSection);
        // Initial state
        toggleTargetSection();
    }

    /* ── Action Dropdowns (Index page) ─────────────────── */
    initActionDropdowns('.ev-action__btn', '.ev-dropdown');

    /* ── Detail Button ─────────────────────────────────── */
    $$('[data-action="detail"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const body = $('#detailEventBody');

            // Show loading
            body.innerHTML = '<div style="text-align:center;padding:32px 0;color:var(--text-muted);"><p>Memuat data...</p></div>';
            openModal('modalDetailEvent');

            fetch(`/events/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Gagal memuat data');
                return r.json();
            })
            .then(data => {
                let targetsHtml = '-';
                if (data.targets && data.targets.length > 0) {
                    targetsHtml = data.targets.map(t => `<span class="ev-target-tag">${t}</span>`).join('');
                }

                const statusClass = data.status === 'Aktif' ? 'success' :
                                    data.status === 'Terjadwal' ? 'warning' : 'muted';

                body.innerHTML = `
                    <div class="ev-detail-grid">
                        <div class="ev-detail-item ev-detail-item--full">
                            <div class="ev-detail-label">Judul</div>
                            <div class="ev-detail-value" style="font-size:1.05rem;font-weight:700;">${escHtml(data.title)}</div>
                        </div>
                        <div class="ev-detail-item">
                            <div class="ev-detail-label">Tanggal Rilis</div>
                            <div class="ev-detail-value">${escHtml(data.release_date)}</div>
                        </div>
                        <div class="ev-detail-item">
                            <div class="ev-detail-label">Tanggal Selesai</div>
                            <div class="ev-detail-value">${escHtml(data.end_date)}</div>
                        </div>
                        <div class="ev-detail-item">
                            <div class="ev-detail-label">Status</div>
                            <div class="ev-detail-value">
                                <span class="ev-status ev-status--${statusClass}">${escHtml(data.status)}</span>
                            </div>
                        </div>
                        <div class="ev-detail-item">
                            <div class="ev-detail-label">Target</div>
                            <div class="ev-detail-value">${escHtml(data.target_audience)}</div>
                        </div>
                        ${data.targets && data.targets.length > 0 ? `
                        <div class="ev-detail-item ev-detail-item--full">
                            <div class="ev-detail-label">Detail Target</div>
                            <div class="ev-detail-value">${targetsHtml}</div>
                        </div>` : ''}
                        ${data.link ? `
                        <div class="ev-detail-item ev-detail-item--full">
                            <div class="ev-detail-label">Link</div>
                            <div class="ev-detail-value">
                                <a href="${escHtml(data.link)}" target="_blank" rel="noopener" style="color:var(--accent);text-decoration:underline;">${escHtml(data.link)}</a>
                            </div>
                        </div>` : ''}
                        ${data.description ? `
                        <div class="ev-detail-item ev-detail-item--full">
                            <div class="ev-detail-label">Deskripsi</div>
                            <div class="ev-detail-description">${escHtml(data.description)}</div>
                        </div>` : ''}
                        <div class="ev-detail-item ev-detail-item--full">
                            <div class="ev-detail-label">Dibuat</div>
                            <div class="ev-detail-value" style="font-size:0.82rem;color:var(--text-muted);">${escHtml(data.created_at)}</div>
                        </div>
                    </div>
                `;
            })
            .catch(err => {
                body.innerHTML = `<div style="text-align:center;padding:32px 0;color:#dc2626;"><p>Gagal memuat data event.</p></div>`;
            });
        });
    });

    /* ── Delete Button ─────────────────────────────────── */
    const deleteForm = $('#deleteEventForm');

    $$('[data-action="delete"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const title = btn.dataset.title;

            $('#deleteEventTitle').textContent = title;
            $('#deleteEventInitial').textContent = title ? title[0].toUpperCase() : 'E';

            if (deleteForm) {
                deleteForm.action = deleteForm.dataset.baseAction.replace('__ID__', id);
            }

            openModal('modalDeleteEvent');
        });
    });

    /* ── Utility ───────────────────────────────────────── */
    function escHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

})();
