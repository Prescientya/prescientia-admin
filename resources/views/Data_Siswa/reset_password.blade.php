{{-- =====================================================
     MODAL: Reset Password Siswa
     @include('Data_Siswa.reset_password')
     ===================================================== --}}
<div class="modal-overlay" id="modalResetPasswordSiswa">
    <div class="modal" style="max-width:460px;">

        <div class="modal-header">
            <h3 class="modal-title">Reset Password Siswa</h3>
            <button class="modal-close" data-close-modal="modalResetPasswordSiswa" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="delete-body">
            <div class="delete-icon" style="background:rgba(37,99,235,.12);color:var(--primary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7h16"/>
                    <path d="M7 7V5a5 5 0 0 1 10 0v2"/>
                    <rect x="5" y="7" width="14" height="14" rx="2"/>
                    <path d="M12 11v4"/>
                </svg>
            </div>

            <p class="delete-title">Reset password ke NIS siswa?</p>

            <div class="delete-student-card">
                <div class="ds-avatar" style="width:40px;height:40px;font-size:1rem;" id="resetStudentInitial">?</div>
                <div>
                    <p class="delete-student-name" id="resetStudentName">–</p>
                    <p class="delete-student-nis" id="resetStudentNis">–</p>
                </div>
            </div>

            <p class="delete-warning">
                Password akan dikembalikan menjadi <strong>NIS</strong> dan siswa wajib mengubahnya kembali saat login berikutnya.
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalResetPasswordSiswa">Batal</button>
            <form id="resetStudentForm" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7h16"/>
                        <path d="M7 7V5a5 5 0 0 1 10 0v2"/>
                        <rect x="5" y="7" width="14" height="14" rx="2"/>
                        <path d="M12 11v4"/>
                    </svg>
                    Ya, Reset Password
                </button>
            </form>
        </div>

    </div>
</div>
