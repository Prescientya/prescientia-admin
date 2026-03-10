/* ============================================================
   WIFI NETWORKS – main.js
   Module-specific only. Shared utilities → components/global.js
   ============================================================ */
(function () {
    'use strict';

    const { $, $$, openModal } = window.PSC;

    /* ── EDIT MODAL — populate fields ────────────────────── */
    function initEditButtons() {
        $$('[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const { id, ssid, bssid, ip } = btn.dataset;

                const form = $('#editForm');
                if (!form) return;

                form.action = form.dataset.baseAction.replace('__ID__', id);

                const inpSsid = form.querySelector('[name="ssid"]');
                const inpBssid = form.querySelector('[name="bssid"]');
                const inpIp   = form.querySelector('[name="ip_address"]');

                if (inpSsid)  inpSsid.value  = ssid;
                if (inpBssid) inpBssid.value = bssid;
                if (inpIp)    inpIp.value    = ip || '';

                openModal('modalEditWifi');
            });
        });
    }

    /* ── DELETE MODAL — populate info ────────────────────── */
    function initDeleteButtons() {
        $$('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const { id, ssid, bssid } = btn.dataset;

                const nameEl  = $('#deleteWifiName');
                const bssidEl = $('#deleteWifiBssid');
                const form    = $('#deleteForm');

                if (nameEl)  nameEl.textContent  = ssid;
                if (bssidEl) bssidEl.textContent = bssid;
                if (form)    form.action         = form.dataset.baseAction.replace('__ID__', id);

                openModal('modalDeleteWifi');
            });
        });
    }

    /* ── INIT ─────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        PSC.initActionDropdowns('.wn-action__btn', '.wn-dropdown');
        PSC.initModalClose();
        PSC.initOpenButtons();
        initEditButtons();
        initDeleteButtons();
        PSC.initAlerts();
        PSC.initFormSpinner();
    });

})();
