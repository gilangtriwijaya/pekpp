<div id="f03aspekCreateModal" class="f03aspek-modal">
    <div class="f03aspek-modal-content">
        <div class="f03aspek-modal-header">
            <h3>Buat Aspek F03 Baru</h3>
            <button type="button" class="f03aspek-modal-close" onclick="closeModal('f03aspekCreateModal')">&times;</button>
        </div>
        <form id="createForm" onsubmit="submitCreateForm(event)">
            <div class="f03aspek-modal-body">
                <div class="f03aspek-form-group">
                    <label for="create-periode" class="f03aspek-form-label">Periode <span class="f03aspek-required">*</span></label>
                    <select id="create-periode" name="periode_id" class="f03aspek-form-control" required>
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periodes as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->tahun }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="f03aspek-form-group">
                    <label for="create-nama" class="f03aspek-form-label">Nama Aspek <span class="f03aspek-required">*</span></label>
                    <input type="text" id="create-nama" name="nama" class="f03aspek-form-control" placeholder="e.g., Kepemimpinan" required>
                </div>

                <div class="f03aspek-form-group">
                    <label for="create-bobot" class="f03aspek-form-label">Bobot (%)</label>
                    <input type="number" id="create-bobot" name="bobot" class="f03aspek-form-control" min="0" max="100" step="0.01" value="0" placeholder="0" title="Bobot persentase (0-100)">
                    <small class="f03aspek-text-muted">Nilai 0-100 untuk pembobotan skor (opsional)</small>
                </div>

                <div class="f03aspek-form-group">
                    <label for="create-keterangan" class="f03aspek-form-label">Keterangan</label>
                    <textarea id="create-keterangan" name="keterangan" class="f03aspek-form-control f03aspek-textarea" rows="2" placeholder="Catatan atau penjelasan aspek ini"></textarea>
                </div>

                <div class="f03aspek-form-group f03aspek-form-check">
                    <input type="checkbox" id="create-aktif" name="aktif" class="f03aspek-form-check-input" value="1" checked>
                    <label for="create-aktif" class="f03aspek-form-check-label">Aktif</label>
                </div>
            </div>
            <div class="f03aspek-modal-footer">
                <button type="button" class="f03aspek-btn f03aspek-btn-secondary" onclick="closeModal('f03aspekCreateModal')">Batal</button>
                <button type="submit" class="f03aspek-btn f03aspek-btn-primary">Buat Aspek</button>
            </div>
        </form>
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
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .f03aspek-modal-close:hover { color: #1F2937; }
    .f03aspek-modal-body { padding: 20px; }
    .f03aspek-form-group { margin-bottom: 16px; }
    .f03aspek-form-label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; color: #374151; }
    .f03aspek-required { color: #DC2626; }
    .f03aspek-form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.2s;
    }
    .f03aspek-form-control:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .f03aspek-textarea { resize: vertical; }
    .f03aspek-form-check { display: flex; align-items: center; }
    .f03aspek-form-check-input { width: 18px; height: 18px; cursor: pointer; }
    .f03aspek-form-check-label { margin-left: 8px; cursor: pointer; font-size: 14px; color: #374151; }
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
</style>
