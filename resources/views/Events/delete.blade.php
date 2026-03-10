{{-- Hapus Event Modal --}}
<div class="modal-overlay" id="modalDeleteEvent">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 class="modal-title modal-title--danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline;vertical-align:-2px;margin-right:6px;">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
                Hapus Event
            </h3>
            <button class="modal-close" data-close-modal="modalDeleteEvent">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="delete-body">
                <div class="delete-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </div>
                <p style="font-weight:700;font-size:1rem;margin:0 0 4px;">Yakin menghapus event ini?</p>
                <p style="font-size:0.85rem;color:var(--text-muted);margin:0 0 14px;">Data yang dihapus tidak dapat dikembalikan.</p>

                <div class="delete-kelas-card">
                    <div class="delete-kelas-initial" id="deleteEventInitial">E</div>
                    <div style="text-align:left;">
                        <p id="deleteEventTitle" style="font-weight:700;margin:0;font-size:0.95rem;"></p>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:2px 0 0;">Event ini akan dihapus permanen</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDeleteEvent">Batal</button>
            <form id="deleteEventForm"
                  action=""
                  data-base-action="{{ route('events.destroy', '__ID__') }}"
                  method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger">
                    Ya, Hapus Event
                </button>
            </form>
        </div>
    </div>
</div>
