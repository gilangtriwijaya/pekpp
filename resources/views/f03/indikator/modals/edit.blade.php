<div id="f03indikatorEditModal" class="f03indikator-modal">
    <div class="f03indikator-modal-content">
        <div class="f03indikator-modal-header">
            <h3>Edit Indikator F03</h3>
            <button type="button" class="f03indikator-modal-close" onclick="closeModal('f03indikatorEditModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="submitEditForm(event)">
            <input type="hidden" id="edit-id" name="id" value="">
            <input type="hidden" id="edit-periode_id" name="periode_id" value="">
            <div class="f03indikator-modal-body">
                <div class="f03indikator-form-group">
                    <label for="edit-aspek_id" class="f03indikator-form-label">Aspek <span class="f03indikator-required">*</span></label>
                    <select id="edit-aspek_id" name="f03_aspek_id" class="f03indikator-form-control" required onchange="updatePeriodeId('edit')">
                        <option value="">-- Pilih Aspek --</option>
                        @foreach($aspeks as $a)
                        <option value="{{ $a->id }}" data-periode-id="{{ $a->periode_id }}">{{ $a->kode }} - {{ $a->nama }} ({{ $a->periode->nama ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="f03indikator-form-group">
                    <label for="edit-pertanyaan" class="f03indikator-form-label">Pertanyaan <span class="f03indikator-required">*</span></label>
                    <textarea id="edit-pertanyaan" name="pertanyaan" class="f03indikator-form-control f03indikator-textarea" rows="3" required></textarea>
                </div>

                <div class="f03indikator-form-group">
                    <label for="edit-tipe_jawaban" class="f03indikator-form-label">Tipe Jawaban <span class="f03indikator-required">*</span></label>
                    <select id="edit-tipe_jawaban" name="tipe_jawaban" class="f03indikator-form-control" required onchange="updatePilihanJawaban('edit')">
                        <option value="radio">Pilihan Ganda (Radio)</option>
                        <option value="checkbox">Kotak Centang (Checkbox)</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="likert_5">Skala Likert 1-5</option>
                        <option value="rating">Skala Rating</option>
                        <option value="text">Teks Bebas (Input)</option>
                        <option value="textarea">Teks Panjang (Textarea)</option>
                    </select>
                </div>

                <div class="f03indikator-form-group">
                    <label for="edit-pilihan_jawaban" class="f03indikator-form-label">Pilihan Jawaban (JSON)</label>
                    <textarea id="edit-pilihan_jawaban" name="pilihan_jawaban" class="f03indikator-form-control f03indikator-textarea" rows="3"></textarea>
                    <small class="f03indikator-help-text">Format JSON array untuk pilihan ganda, atau biarkan kosong untuk likert</small>
                </div>

                <div class="f03indikator-form-group">
                    <label for="edit-urutan" class="f03indikator-form-label">Urutan <span class="f03indikator-required">*</span></label>
                    <input type="number" id="edit-urutan" name="urutan" class="f03indikator-form-control" placeholder="1, 2, 3, dst" min="1" required>
                    <small class="f03indikator-help-text">Nomor urut pertanyaan dalam aspek ini</small>
                </div>

                <div class="f03indikator-form-group f03indikator-form-check">
                    <input type="checkbox" id="edit-aktif" name="aktif" class="f03indikator-form-check-input" value="1">
                    <label for="edit-aktif" class="f03indikator-form-check-label">Aktif</label>
                </div>
            </div>
            <div class="f03indikator-modal-footer">
                <button type="button" class="f03indikator-btn f03indikator-btn-secondary" onclick="closeModal('f03indikatorEditModal')">Batal</button>
                <button type="submit" class="f03indikator-btn f03indikator-btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<style>
    .f03indikator-modal {
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
    .f03indikator-modal.show { 
        display: flex !important;
    }
    .f03indikator-modal-content {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 550px;
        max-height: 90vh;
        overflow-y: auto;
        flex-shrink: 0;
    }
    .f03indikator-modal-header {
        padding: 20px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .f03indikator-modal-header h3 { margin: 0; font-size: 18px; font-weight: 600; color: #1F2937; }
    .f03indikator-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #6B7280;
        padding: 0;
        width: 30px;
        height: 30px;
    }
    .f03indikator-modal-close:hover { color: #1F2937; }
    .f03indikator-modal-body { padding: 20px; }
    .f03indikator-form-group { margin-bottom: 16px; }
    .f03indikator-form-label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; color: #374151; }
    .f03indikator-required { color: #DC2626; }
    .f03indikator-form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.2s;
    }
    .f03indikator-form-control:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .f03indikator-textarea { resize: vertical; }
    .f03indikator-help-text { display: block; color: #6B7280; font-size: 12px; margin-top: 4px; }
    .f03indikator-form-check { display: flex; align-items: center; }
    .f03indikator-form-check-input { width: 18px; height: 18px; cursor: pointer; }
    .f03indikator-form-check-label { margin-left: 8px; cursor: pointer; font-size: 14px; color: #374151; }
    .f03indikator-modal-footer {
        padding: 20px;
        border-top: 1px solid #E5E7EB;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .f03indikator-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .f03indikator-btn-primary {
        background-color: #3B82F6;
        color: white;
    }
    .f03indikator-btn-primary:hover { background-color: #2563EB; }
    .f03indikator-btn-secondary {
        background-color: #E5E7EB;
        color: #374151;
    }
    .f03indikator-btn-secondary:hover { background-color: #D1D5DB; }
