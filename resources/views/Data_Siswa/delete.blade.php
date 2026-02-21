{{-- =====================================================
     MODAL: Konfirmasi Hapus Siswa
     @include('Data_Siswa.delete')
     ===================================================== --}}
<div class="modal-overlay" id="modalDelete">
    <div class="modal" style="max-width:440px;">

        <div class="modal-header">
            <h3 class="modal-title">Hapus Data Siswa</h3>
            <button class="modal-close" data-close-modal="modalDelete" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="delete-body">
            {{-- Warning icon --}}
            <div class="delete-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
            </div>

            <p class="delete-title">Yakin menghapus data ini?</p>

            {{-- Info siswa yang akan dihapus --}}
            <div class="delete-student-card">
                <div class="ds-avatar" style="width:40px;height:40px;font-size:1rem;" id="deleteStudentInitial">?</div>
                <div>
                    <p class="delete-student-name" id="deleteStudentName">–</p>
                    <p class="delete-student-nis"  id="deleteStudentNis">–</p>
                </div>
            </div>

            <p class="delete-warning">
                Menghapus data siswa akan mennonaktifkan akun login mereka.
                Data yang dihapus <strong>tidak dapat dikembalikan</strong>.
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDelete">Batal</button>
            <form id="deleteForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    Ya, Hapus
                </button>
            </form>
        </div>

    </div>
</div>
