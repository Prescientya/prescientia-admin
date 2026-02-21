{{-- =====================================================
     MODAL: Detail Siswa (AJAX-populated)
     @include('Data_Siswa.detail')
     ===================================================== --}}
<div class="modal-overlay" id="modalDetail">
    <div class="modal modal--lg">

        <div class="modal-header">
            <h3 class="modal-title">Detail Data Siswa</h3>
            <button class="modal-close" data-close-modal="modalDetail" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Header dengan avatar --}}
        <div class="detail-header">
            <div class="detail-avatar" id="detailAvatar">?</div>
            <div>
                <p class="detail-name" id="detailName">–</p>
                <p class="detail-nis"  id="detailNis">–</p>
                <div style="margin-top:6px;">
                    <span class="badge badge--active" id="detailStatus">Aktif</span>
                </div>
            </div>
        </div>

        {{-- Grid data --}}
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-item__label">Email</div>
                <div class="detail-item__value" id="detailEmail">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Jenis Kelamin</div>
                <div class="detail-item__value" id="detailGender">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Tanggal Lahir</div>
                <div class="detail-item__value" id="detailDob">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">No. HP</div>
                <div class="detail-item__value" id="detailPhone">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Kelas</div>
                <div class="detail-item__value" id="detailKelas">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Terdaftar Sejak</div>
                <div class="detail-item__value" id="detailCreated">–</div>
            </div>
            <div class="detail-item detail-item--full">
                <div class="detail-item__label">Alamat</div>
                <div class="detail-item__value" id="detailAddress">–</div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDetail">Tutup</button>
        </div>

    </div>
</div>
