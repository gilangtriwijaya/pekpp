{{-- Detail Aspek Modal --}}
<div class="aspek-modal-overlay" id="aspekDetailModal">
    <div class="aspek-modal">
        <div class="aspek-modal-header">
            <h3 class="aspek-modal-title">Detail Aspek</h3>
            <button class="aspek-modal-close" onclick="closeModal('aspekDetailModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="aspek-modal-body">
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">ID</label>
                <p class="aspek-detail-value" id="aspek-detail-id">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Periode</label>
                <p class="aspek-detail-value" id="aspek-detail-periode">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Kode Aspek</label>
                <p class="aspek-detail-value" id="aspek-detail-kode">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Nama Aspek</label>
                <p class="aspek-detail-value" id="aspek-detail-nama">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Domain</label>
                <p class="aspek-detail-value" id="aspek-detail-domain">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Bobot</label>
                <p class="aspek-detail-value" id="aspek-detail-bobot">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Keterangan</label>
                <p class="aspek-detail-value" id="aspek-detail-keterangan">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Urutan</label>
                <p class="aspek-detail-value" id="aspek-detail-urutan">-</p>
            </div>
            <div class="aspek-detail-group">
                <label class="aspek-detail-label">Status</label>
                <p class="aspek-detail-value" id="aspek-detail-aktif">-</p>
            </div>
        </div>
        <div class="aspek-modal-footer">
            <button type="button" class="aspek-btn aspek-btn-secondary" onclick="closeModal('aspekDetailModal')">Tutup</button>
        </div>
    </div>
</div>


