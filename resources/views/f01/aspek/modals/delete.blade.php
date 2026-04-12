{{-- Delete Confirmation Modal --}}
<div class="aspek-modal-overlay" id="aspekDeleteModal">
    <div class="aspek-modal">
        <div class="aspek-modal-header">
            <h3 class="aspek-modal-title">Hapus Aspek</h3>
            <button class="aspek-modal-close" onclick="closeModal('aspekDeleteModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="aspek-modal-body">
            <div class="aspek-delete-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div class="aspek-delete-message">
                <h4>Apakah Anda yakin?</h4>
                <p>Anda akan menghapus aspek <strong id="aspek-delete-item-name">-</strong>. Aksi ini tidak dapat dibatalkan jika aspek tidak terhubung dengan data lain.</p>
                <div id="aspek-delete-error-container" class="aspek-delete-message" style="display: none; margin-top: 12px;">
                    <div class="aspek-delete-message error-info" id="aspek-delete-error-message"></div>
                </div>
            </div>
        </div>
        <div class="aspek-modal-footer">
            <button type="button" class="aspek-btn aspek-btn-secondary" onclick="closeModal('aspekDeleteModal')">Batal</button>
            <button type="button" class="aspek-btn aspek-btn-danger" id="aspek-delete-btn" onclick="executeDelete();">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
                Hapus Aspek
            </button>
        </div>
    </div>
</div>
