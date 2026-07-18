<div id="pdAspekCreateModal" class="pd-modal">
    <div class="pd-modal-content">
        <div class="pd-modal-header">
            <h3>Tambah Aspek Pendataan</h3>
            <button type="button" class="pd-modal-close" onclick="closeModal('pdAspekCreateModal')">&times;</button>
        </div>
        <form id="createForm" onsubmit="submitCreateForm(event)">
            <div class="pd-modal-body">
                <div class="pd-form-group">
                    <label class="pd-form-label">Periode <span class="pd-required">*</span></label>
                    <select id="create-periode" name="periode_id" class="pd-form-control" required>
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periodes as $p)
                        <option value="{{ $p->id }}">{{ $p->nama ?? $p->tahun }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓ Aktif' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:12px;">
                    <div class="pd-form-group" style="flex:1;">
                        <label class="pd-form-label">Nama Aspek <span class="pd-required">*</span></label>
                        <input type="text" id="create-nama" name="nama" class="pd-form-control" placeholder="Nama aspek" required>
                    </div>
                    <div class="pd-form-group" style="width:120px;">
                        <label class="pd-form-label">Kode</label>
                        <input type="text" id="create-kode" name="kode" class="pd-form-control" placeholder="A1" maxlength="20">
                    </div>
                </div>
                <div class="pd-form-group">
                    <label class="pd-form-label">Urutan</label>
                    <input type="number" id="create-urutan" name="urutan" class="pd-form-control" min="0" value="0" placeholder="0">
                </div>
                <div class="pd-form-group">
                    <label class="pd-form-label">Keterangan</label>
                    <textarea id="create-keterangan" name="keterangan" class="pd-form-control pd-textarea" rows="2" placeholder="Keterangan singkat tentang aspek ini"></textarea>
                </div>
                <div class="pd-form-check">
                    <input type="checkbox" id="create-aktif" name="aktif" value="1" checked>
                    <label for="create-aktif">Aktif</label>
                </div>
            </div>
            <div class="pd-modal-footer">
                <button type="button" class="pd-btn pd-btn-secondary" onclick="closeModal('pdAspekCreateModal')">Batal</button>
                <button type="submit" class="pd-btn pd-btn-primary">Simpan Aspek</button>
            </div>
        </form>
    </div>
</div>

<style>
.pd-modal { display:none !important; position:fixed !important; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center; padding:20px; }
.pd-modal.show { display:flex !important; }
.pd-modal-content { background:white; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.15); width:100%; max-width:520px; max-height:90vh; overflow-y:auto; }
.pd-modal-header { padding:20px 24px; border-bottom:1px solid #E5E7EB; display:flex; justify-content:space-between; align-items:center; }
.pd-modal-header h3 { margin:0; font-size:17px; font-weight:700; color:#1F2937; }
.pd-modal-close { background:none; border:none; font-size:26px; cursor:pointer; color:#9CA3AF; line-height:1; }
.pd-modal-close:hover { color:#1F2937; }
.pd-modal-body { padding:20px 24px; }
.pd-modal-footer { padding:16px 24px; border-top:1px solid #E5E7EB; display:flex; gap:10px; justify-content:flex-end; }
.pd-form-group { margin-bottom:16px; }
.pd-form-label { display:block; margin-bottom:6px; font-size:14px; font-weight:500; color:#374151; }
.pd-required { color:#DC2626; }
.pd-form-control { width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; font-size:14px; font-family:inherit; box-sizing:border-box; }
.pd-form-control:focus { outline:none; border-color:#4F46E5; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.pd-textarea { resize:vertical; }
.pd-form-check { display:flex; align-items:center; gap:8px; font-size:14px; }
.pd-form-check input { width:16px; height:16px; cursor:pointer; }
</style>
