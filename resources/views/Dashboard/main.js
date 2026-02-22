(function () {
    'use strict';

    const panel      = document.getElementById('classDetailPanel');
    const panelTitle = document.getElementById('classDetailTitle');
    const panelGrid  = document.getElementById('classDetailGrid');
    const closeBtn   = document.getElementById('classDetailClose');

    let activeLevel  = null;

    /* ── Render class detail cards ────────────────────────── */
    function renderClasses(classes) {
        panelGrid.innerHTML = classes.map(function (c) {
            return `
            <div class="cls-card">
                <div class="cls-card__header">
                    <span class="cls-card__name">${c.label}</span>
                    <span class="cls-card__total">${c.total_siswa} siswa</span>
                </div>
                <div class="cls-card__stats">
                    <div class="cls-stat">
                        <span class="cls-stat__num cls-stat__num--hadir">${c.hadir}</span>
                        <span class="cls-stat__lbl">Hadir</span>
                    </div>
                    <div class="cls-stat">
                        <span class="cls-stat__num cls-stat__num--terlambat">${c.terlambat}</span>
                        <span class="cls-stat__lbl">Terlambat</span>
                    </div>
                    <div class="cls-stat">
                        <span class="cls-stat__num cls-stat__num--sakit">${c.sakit}</span>
                        <span class="cls-stat__lbl">Sakit</span>
                    </div>
                    <div class="cls-stat">
                        <span class="cls-stat__num cls-stat__num--izin">${c.izin}</span>
                        <span class="cls-stat__lbl">Izin</span>
                    </div>
                    <div class="cls-stat">
                        <span class="cls-stat__num cls-stat__num--alpa">${c.alpa}</span>
                        <span class="cls-stat__lbl">Alpa</span>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    /* ── Open detail panel ────────────────────────────────── */
    function openPanel(card) {
        const level   = card.dataset.level;
        const classes = JSON.parse(card.dataset.classes || '[]');

        panelTitle.textContent = 'Detail Kelas ' + level;
        renderClasses(classes);

        panel.hidden = false;
        // Smooth scroll to panel
        setTimeout(function () {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 50);
    }

    /* ── Close detail panel ───────────────────────────────── */
    function closePanel() {
        panel.hidden = true;
        if (activeLevel) {
            document.querySelector('.level-card[data-level="' + activeLevel + '"]')
                    ?.classList.remove('active');
            activeLevel = null;
        }
    }

    /* ── Level card click ─────────────────────────────────── */
    document.querySelectorAll('.level-card').forEach(function (card) {
        card.addEventListener('click', function () {
            const level = card.dataset.level;

            // Clicking the already-active card closes the panel
            if (activeLevel === level) {
                closePanel();
                return;
            }

            // Deactivate previously active card
            document.querySelectorAll('.level-card').forEach(function (c) {
                c.classList.remove('active');
            });

            card.classList.add('active');
            activeLevel = level;
            openPanel(card);
        });
    });

    /* ── Close button ─────────────────────────────────────── */
    closeBtn.addEventListener('click', closePanel);

}());
