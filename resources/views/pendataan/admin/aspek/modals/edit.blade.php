<div id="pdAspekEditModal" class="pd-modal">
    <div class="pd-modal-content">
        <div class="pd-modal-header">
            <h3>Edit Aspek Pendataan</h3>
            <button type="button" class="pd-modal-close" onclick="closeModal('pdAspekEditModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="submitEditForm(event)">
            <input type="hidden" id="edit-id" name="id">
            <div class="pd-modal-body">
                <div class="pd-form-group">
                    <label class="pd-form-label">Periode <span class="pd-required">*</span></label>
                    <select id="edit-periode" name="periode_id" class="pd-form-control" required>
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periodes as $p)
                        <option value="{{ $p->id }}">{{ $p->nama ?? $p->tahun }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓ Aktif' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:12px;">
                    <div class="pd-form-group" style="flex:1;">
                        <label class="pd-form-label">Nama Aspek <span class="pd-required">*</span></label>
                        <input type="text" id="edit-nama" name="nama" class="pd-form-control" required>
                    </div>
                    <div class="pd-form-group" style="width:120px;">
                        <label class="pd-form-label">Kode</label>
                        <input type="text" id="edit-kode" name="kode" class="pd-form-control" maxlength="20">
                    </div>
                </div>
                <div class="pd-form-group">
                    <label class="pd-form-label">Urutan</label>
                    <input type="number" id="edit-urutan" name="urutan" class="pd-form-control" min="0">
                </div>
                <div class="pd-form-group">
                    <label class="pd-form-label">Keterangan</label>
                    <textarea id="edit-keterangan" name="keterangan" class="pd-form-control pd-textarea" rows="2"></textarea>
                </div>
                <div class="pd-form-check">
                    <input type="checkbox" id="edit-aktif" name="aktif" value="1">
                    <label for="edit-aktif">Aktif</label>
                </div>
            </div>
            <div class="pd-modal-footer">
                <button type="button" class="pd-btn pd-btn-secondary" onclick="closeModal('pdAspekEditModal')">Batal</button>
                <button type="submit" class="pd-btn pd-btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
