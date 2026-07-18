<div id="pdPertanyaanEditModal" class="pd-modal">
    <div class="pd-modal-content" style="max-width:600px;">
        <div class="pd-modal-header">
            <h3>Edit Pertanyaan Pendataan</h3>
            <button type="button" class="pd-modal-close" onclick="closeModal('pdPertanyaanEditModal')">&times;</button>
        </div>
        <form id="editPForm" onsubmit="submitEditForm(event)">
            <input type="hidden" id="edit-id" name="id">
            {{-- Hidden field untuk opsi JSON --}}
            <input type="hidden" id="edit-opsi-hidden" name="opsi_jawaban">

            <div class="pd-modal-body">
                <div class="pd-form-group">
                    <label class="pd-form-label">Aspek <span class="pd-required">*</span></label>
                    <select id="edit-aspek" name="pendataan_aspek_id" class="pd-form-control" required>
                        <option value="">-- Pilih Aspek --</option>
                        @foreach($aspeks as $a)
                        <option value="{{ $a->id }}">{{ $a->urutan }}. {{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pd-form-group">
                    <label class="pd-form-label">Label / Teks Pertanyaan <span class="pd-required">*</span></label>
                    <textarea id="edit-label" name="label" class="pd-form-control pd-textarea" rows="3" required></textarea>
                </div>

                <div style="display:flex; gap:12px;">
                    <div class="pd-form-group" style="flex:1;">
                        <label class="pd-form-label">Tipe Input <span class="pd-required">*</span></label>
                        <select id="edit-tipe" name="tipe_input" class="pd-form-control" required onchange="toggleOpsi('edit')">
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
                        <input type="text" id="edit-kode" name="kode" class="pd-form-control" maxlength="20">
                    </div>
                    <div class="pd-form-group" style="width:100px;">
                        <label class="pd-form-label">Urutan</label>
                        <input type="number" id="edit-urutan" name="urutan" class="pd-form-control" min="0">
                    </div>
                </div>

                {{-- Opsi Jawaban - UI baris per baris --}}
                <div class="pd-form-group" id="edit-opsi-group" style="display:none;">
                    <label class="pd-form-label">Pilihan Jawaban</label>
                    <div id="edit-opsi-list" class="opsi-list">
                        {{-- Diisi oleh JS saat modal dibuka --}}
                    </div>
                    <button type="button" class="opsi-add-btn" onclick="addOpsiRow('edit')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Pilihan
                    </button>
                    <small style="color:#9CA3AF; display:block; margin-top:6px;">Tambahkan pilihan jawaban yang akan ditampilkan kepada pengguna.</small>
                </div>

                <div style="display:flex; gap:20px; margin-top:8px;">
                    <div class="pd-form-check">
                        <input type="checkbox" id="edit-wajib" name="wajib" value="1">
                        <label for="edit-wajib">Wajib Diisi</label>
                    </div>
                    <div class="pd-form-check">
                        <input type="checkbox" id="edit-aktif" name="aktif" value="1">
                        <label for="edit-aktif">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="pd-modal-footer">
                <button type="button" class="pd-btn pd-btn-secondary" onclick="closeModal('pdPertanyaanEditModal')">Batal</button>
                <button type="submit" class="pd-btn pd-btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
