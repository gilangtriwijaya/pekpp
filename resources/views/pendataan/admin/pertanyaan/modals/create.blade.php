<div id="pdPertanyaanCreateModal" class="pd-modal">
    <div class="pd-modal-content" style="max-width:600px;">
        <div class="pd-modal-header">
            <h3>Tambah Pertanyaan Pendataan</h3>
            <button type="button" class="pd-modal-close" onclick="closeModal('pdPertanyaanCreateModal')">&times;</button>
        </div>
        <form id="createPForm" onsubmit="submitCreateForm(event)">
            {{-- Hidden field untuk opsi JSON --}}
            <input type="hidden" id="create-opsi-hidden" name="opsi_jawaban">

            <div class="pd-modal-body">
                <div class="pd-form-group">
                    <label class="pd-form-label">Aspek <span class="pd-required">*</span></label>
                    <select id="create-aspek" name="pendataan_aspek_id" class="pd-form-control" required>
                        <option value="">-- Pilih Aspek --</option>
                        @foreach($aspeks as $a)
                        <option value="{{ $a->id }}">{{ $a->urutan }}. {{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pd-form-group">
                    <label class="pd-form-label">Label / Teks Pertanyaan <span class="pd-required">*</span></label>
                    <textarea id="create-label" name="label" class="pd-form-control pd-textarea" rows="3" placeholder="Tulis pertanyaan di sini..." required></textarea>
                </div>

                <div style="display:flex; gap:12px;">
                    <div class="pd-form-group" style="flex:1;">
                        <label class="pd-form-label">Tipe Input <span class="pd-required">*</span></label>
                        <select id="create-tipe" name="tipe_input" class="pd-form-control" required onchange="toggleOpsi('create')">
                            <option value="text">Text (Teks Bebas)</option>
                            <option value="textarea">Textarea (Teks Panjang)</option>
                            <option value="number">Number (Angka)</option>
                            <option value="radio">Radio (Pilihan Tunggal)</option>
                            <option value="checkbox">Checkbox (Pilihan Ganda)</option>
                            <option value="select">Select (Dropdown)</option>
                            <option value="yesno">Yes/No</option>
                            <option value="date">Date (Tanggal)</option>
                        </select>
                    </div>
                    <div class="pd-form-group" style="width:110px;">
                        <label class="pd-form-label">Kode</label>
                        <input type="text" id="create-kode" name="kode" class="pd-form-control" maxlength="20" placeholder="P1">
                    </div>
                    <div class="pd-form-group" style="width:100px;">
                        <label class="pd-form-label">Urutan</label>
                        <input type="number" id="create-urutan" name="urutan" class="pd-form-control" min="0" value="0">
                    </div>
                </div>

                {{-- Opsi Jawaban - UI baris per baris --}}
                <div class="pd-form-group" id="create-opsi-group" style="display:none;">
                    <label class="pd-form-label">Pilihan Jawaban</label>
                    <div id="create-opsi-list" class="opsi-list">
                        {{-- Baris opsi akan di-generate oleh JS --}}
                    </div>
                    <button type="button" class="opsi-add-btn" onclick="addOpsiRow('create')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Pilihan
                    </button>
                    <small style="color:#9CA3AF; display:block; margin-top:6px;">Tambahkan pilihan jawaban yang akan ditampilkan kepada pengguna.</small>
                </div>

                <div style="display:flex; gap:20px; margin-top:8px;">
                    <div class="pd-form-check">
                        <input type="checkbox" id="create-wajib" name="wajib" value="1">
                        <label for="create-wajib">Wajib Diisi</label>
                    </div>
                    <div class="pd-form-check">
                        <input type="checkbox" id="create-aktif" name="aktif" value="1" checked>
                        <label for="create-aktif">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="pd-modal-footer">
                <button type="button" class="pd-btn pd-btn-secondary" onclick="closeModal('pdPertanyaanCreateModal')">Batal</button>
                <button type="submit" class="pd-btn pd-btn-primary">Simpan Pertanyaan</button>
            </div>
        </form>
    </div>
</div>

<style>
.pd-modal { display:none !important; position:fixed !important; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center; padding:20px; overflow-y:auto; }
.pd-modal.show { display:flex !important; }
.pd-modal-content { background:white; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.15); width:100%; max-height:90vh; overflow-y:auto; }
.pd-modal-header { padding:20px 24px; border-bottom:1px solid #E5E7EB; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:white; z-index:1; }
.pd-modal-header h3 { margin:0; font-size:17px; font-weight:700; color:#1F2937; }
.pd-modal-close { background:none; border:none; font-size:26px; cursor:pointer; color:#9CA3AF; line-height:1; }
.pd-modal-close:hover { color:#1F2937; }
.pd-modal-body { padding:20px 24px; }
.pd-modal-footer { padding:16px 24px; border-top:1px solid #E5E7EB; display:flex; gap:10px; justify-content:flex-end; position:sticky; bottom:0; background:white; }
.pd-form-group { margin-bottom:16px; }
.pd-form-label { display:block; margin-bottom:6px; font-size:14px; font-weight:500; color:#374151; }
.pd-required { color:#DC2626; }
.pd-form-control { width:100%; padding:9px 12px; border:1px solid #D1D5DB; border-radius:6px; font-size:14px; font-family:inherit; box-sizing:border-box; }
.pd-form-control:focus { outline:none; border-color:#4F46E5; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.pd-textarea { resize:vertical; }
.pd-form-check { display:flex; align-items:center; gap:8px; font-size:14px; }
.pd-form-check input { width:16px; height:16px; cursor:pointer; }
/* Opsi rows */
.opsi-list { display:flex; flex-direction:column; gap:8px; margin-bottom:10px; }
.opsi-row { display:flex; align-items:center; gap:8px; }
.opsi-row-num { font-size:13px; color:#9CA3AF; min-width:20px; text-align:right; font-weight:500; }
.opsi-row input { flex:1; padding:8px 12px; border:1px solid #D1D5DB; border-radius:6px; font-size:14px; font-family:inherit; }
.opsi-row input:focus { outline:none; border-color:#4F46E5; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.opsi-delete-btn { background:none; border:1px solid #FCA5A5; border-radius:5px; color:#EF4444; cursor:pointer; padding:6px 8px; display:flex; align-items:center; transition:all 0.15s; flex-shrink:0; }
.opsi-delete-btn:hover { background:#FEF2F2; border-color:#EF4444; }
.opsi-add-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#F0F9FF; border:1px dashed #60A5FA; border-radius:7px; color:#2563EB; font-size:13px; font-weight:500; cursor:pointer; transition:all 0.2s; }
.opsi-add-btn:hover { background:#DBEAFE; border-color:#3B82F6; }
</style>
