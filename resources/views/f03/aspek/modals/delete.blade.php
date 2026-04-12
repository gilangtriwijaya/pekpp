<div id="f03aspekDeleteModal" class="f03aspek-modal">
    <div class="f03aspek-modal-content">
        <div class="f03aspek-modal-header">
            <h3>Konfirmasi Hapus</h3>
            <button type="button" class="f03aspek-modal-close" onclick="closeModal('f03aspekDeleteModal')">&times;</button>
        </div>
        <div class="f03aspek-modal-body">
            <p class="f03aspek-delete-message">Apakah Anda yakin ingin menghapus aspek ini? Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="f03aspek-modal-footer">
            <button type="button" class="f03aspek-btn f03aspek-btn-secondary" onclick="closeModal('f03aspekDeleteModal')">Batal</button>
            <button type="button" class="f03aspek-btn f03aspek-btn-danger" onclick="confirmDelete()">Hapus</button>
        </div>
    </div>
</div>

<style>
    .f03aspek-modal {
        display: none !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, 0.5) !important;
        z-index: 99999 !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        overflow-y: auto !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .f03aspek-modal.show { 
        display: flex !important;
    }
    .f03aspek-modal-content {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        flex-shrink: 0;
    }
    .f03aspek-modal-header {
        padding: 20px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .f03aspek-modal-header h3 { margin: 0; font-size: 18px; font-weight: 600; color: #1F2937; }
    .f03aspek-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #6B7280;
        padding: 0;
        width: 30px;
        height: 30px;
    }
    .f03aspek-modal-close:hover { color: #1F2937; }
    .f03aspek-modal-body { padding: 20px; }
    .f03aspek-modal-footer {
        padding: 20px;
        border-top: 1px solid #E5E7EB;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .f03aspek-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .f03aspek-btn-primary {
        background-color: #3B82F6;
        color: white;
    }
    .f03aspek-btn-primary:hover { background-color: #2563EB; }
    .f03aspek-btn-secondary {
        background-color: #E5E7EB;
        color: #374151;
    }
    .f03aspek-btn-secondary:hover { background-color: #D1D5DB; }
    .f03aspek-delete-message {
        margin: 0;
        font-size: 14px;
        color: #374151;
        line-height: 1.5;
    }
    .f03aspek-btn-danger {
        background-color: #EF4444;
        color: white;
    }
    .f03aspek-btn-danger:hover { background-color: #DC2626; }
</style>
