{{-- Detail Event Modal --}}
<div class="modal-overlay" id="modalDetailEvent">
    <div class="modal modal--lg">
        <div class="modal-header">
            <h3 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="modal-icon">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Detail Event
            </h3>
            <button class="modal-close" data-close-modal="modalDetailEvent">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="detailEventBody">
            <div style="text-align:center;padding:32px 0;color:var(--text-muted);">
                <p>Memuat data...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDetailEvent">Tutup</button>
        </div>
    </div>
</div>
