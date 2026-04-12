<!-- Delete Confirmation Modal -->
<div class="indikator-modal-overlay" id="indikatorDeleteModal" 
     role="dialog" 
     aria-labelledby="indikator-deleteModalLabel" 
     aria-hidden="true">
    <div class="indikator-modal indikator-modal-sm">
        <div class="indikator-modal-header indikator-modal-header-danger">
            <h5 class="indikator-modal-title" id="indikator-deleteModalLabel">
                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>Hapus Indikator
            </h5>
            <button class="indikator-modal-close" 
                    onclick="hideModal('indikatorDeleteModal')"
                    aria-label="Tutup dialog"
                    type="button">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="indikator-modal-body">
            <p><strong>Apakah Anda yakin ingin menghapus indikator ini?</strong></p>
            <div id="indikator-deleteText" class="alert alert-warning mb-3" style="border-left: 4px solid #dc2626;" role="alert">
                <strong style="color: #dc2626;">Indikator akan dihapus</strong>
            </div>
            <p class="indikator-text-muted small mb-0">
                <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                Indikator tidak dapat dihapus jika memiliki pertanyaan terkait.
            </p>
        </div>

        <div class="indikator-modal-footer">
            <button type="button" class="indikator-btn indikator-btn-secondary" onclick="hideModal('indikatorDeleteModal')">
                <i class="fas fa-times me-1" aria-hidden="true"></i>Batal
            </button>
            <button type="button" id="indikator-delete-btn" class="indikator-btn indikator-btn-danger" onclick="executeDelete()">
                <i class="fas fa-trash me-1" aria-hidden="true"></i>Hapus Selamanya
            </button>
        </div>
    </div>
</div>
