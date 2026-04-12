<!-- Delete Confirmation Modal -->
<div class="pertanyaan-modal-overlay" id="pertanyaanDeleteModal" 
     role="dialog" 
     aria-labelledby="pertanyaan-deleteModalLabel" 
     aria-hidden="true">
    <div class="pertanyaan-modal pertanyaan-modal-sm">
        <div class="pertanyaan-modal-header pertanyaan-modal-header-danger">
            <h5 class="pertanyaan-modal-title" id="pertanyaan-deleteModalLabel">
                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>Hapus Pertanyaan
            </h5>
            <button class="pertanyaan-modal-close" 
                    onclick="hideModal('pertanyaanDeleteModal')"
                    aria-label="Tutup dialog"
                    type="button">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="pertanyaan-modal-body">
            <p><strong>Apakah Anda yakin ingin menghapus pertanyaan ini?</strong></p>
            <div id="pertanyaan-deleteText" class="alert alert-warning mb-3" style="border-left: 4px solid #dc2626;" role="alert">
                <strong style="color: #dc2626;">Pertanyaan akan dihapus</strong>
            </div>
            <p class="pertanyaan-text-muted small mb-0">
                <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                Tindakan ini tidak dapat dibatalkan jika tidak ada jawaban terkait.
            </p>
        </div>

        <div class="pertanyaan-modal-footer">
            <button type="button" class="pertanyaan-btn pertanyaan-btn-secondary" onclick="hideModal('pertanyaanDeleteModal')">
                <i class="fas fa-times me-1" aria-hidden="true"></i>Batal
            </button>
            <button type="button" id="pertanyaan-delete-btn" class="pertanyaan-btn pertanyaan-btn-danger" onclick="executeDelete()">
                <i class="fas fa-trash me-1" aria-hidden="true"></i>Hapus Selamanya
            </button>
        </div>
    </div>
</div>
