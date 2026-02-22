{{-- =====================================================
     MODAL: Detail Guru (AJAX-populated)
     @include('Data_Guru.detail')
     ===================================================== --}}
<div class="modal-overlay" id="modalDetail">
    <div class="modal modal--lg">

        <div class="modal-header">
            <h3 class="modal-title">Detail Data Guru</h3>
            <button class="modal-close" data-close-modal="modalDetail" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Header with avatar --}}
        <div class="dg-detail-header">
            <div class="dg-detail-avatar" id="dg-detailAvatar">?</div>
            <div>
                <p class="dg-detail-name" id="dg-detailName">–</p>
                <p class="dg-detail-nip"  id="dg-detailNip">–</p>
                <div style="margin-top:6px;">
                    <span class="badge badge--active" id="dg-detailStatus">Aktif</span>
                </div>
            </div>
        </div>

        {{-- Grid data --}}
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-item__label">Email</div>
                <div class="detail-item__value" id="dg-detailEmail">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Jenis Kelamin</div>
                <div class="detail-item__value" id="dg-detailGender">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Tanggal Lahir</div>
                <div class="detail-item__value" id="dg-detailDob">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">No. HP</div>
                <div class="detail-item__value" id="dg-detailPhone">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Terdaftar Sejak</div>
                <div class="detail-item__value" id="dg-detailCreated">–</div>
            </div>
            <div class="detail-item">
                <div class="detail-item__label">Mata Pelajaran</div>
                <div class="detail-item__value subject-chips" id="dg-detailMapel">–</div>
            </div>
            <div class="detail-item detail-item--full">
                <div class="detail-item__label">Alamat</div>
                <div class="detail-item__value" id="dg-detailAddress">–</div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn--ghost" data-close-modal="modalDetail">Tutup</button>
        </div>

    </div>
</div>
