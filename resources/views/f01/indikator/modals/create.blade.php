<!-- Create/Edit Modal -->
<div class="indikator-modal-overlay" id="indikatorCreateModal" 
     role="dialog" 
     aria-labelledby="indikator-createModalLabel" 
     aria-hidden="true">
    <div class="indikator-modal indikator-modal-lg">
        <div class="indikator-modal-header">
            <h5 class="indikator-modal-title" id="indikator-createModalLabel">Tambah Indikator Baru</h5>
            <button class="indikator-modal-close" 
                    onclick="hideModal('indikatorCreateModal')"
                    aria-label="Tutup dialog"
                    type="button">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <form id="indikatorForm">
            @csrf
            <div class="indikator-modal-body">
                <!-- Aspek Selection -->
                <div class="indikator-form-group">
                    <label for="indikator-aspek" class="indikator-form-label">Pilih Aspek <span class="required" aria-label="required">*</span></label>
                    <select id="indikator-aspek" name="aspek_id" class="indikator-form-input" aria-describedby="aspek-hint" required>
                        <option value="">-- Pilih Aspek --</option>
                        @foreach($aspeks as $aspek)
                            <option value="{{ $aspek->id }}">[{{ $aspek->kode }}] {{ Str::limit($aspek->nama, 50) }}</option>
                        @endforeach
                    </select>
                    <small id="aspek-hint" class="indikator-text-muted">Indikator akan terikat pada aspek yang dipilih</small>
                </div>

                <!-- Kode & Urutan -->
                <div class="indikator-form-row">
                    <div class="indikator-form-group">
                        <label for="indikator-kode" class="indikator-form-label">Kode Indikator</label>
                        <input type="text" id="indikator-kode" name="kode" class="indikator-form-input" placeholder="Contoh: I1 (opsional)">
                        <small class="indikator-text-muted">Jika kosong, auto-generate</small>
                    </div>
                    <div class="indikator-form-group">
                        <label for="indikator-urutan" class="indikator-form-label">Urutan</label>
                        <input type="number" id="indikator-urutan" name="urutan" class="indikator-form-input" min="1" placeholder="Opsional">
                        <small class="indikator-text-muted">Jika kosong, auto-generate</small>
                    </div>
                </div>

                <!-- Indikator Name -->
                <div class="indikator-form-group">
                    <label for="indikator-nama" class="indikator-form-label">Nama Indikator <span class="required" aria-label="required">*</span></label>
                    <input type="text" id="indikator-nama" name="nama" class="indikator-form-input" placeholder="Ketik nama indikator..." required aria-describedby="nama-hint">
                    <small id="nama-hint" class="indikator-text-muted">Masukkan nama indikator secara jelas dan singkat</small>
                </div>

                <!-- Description -->
                <div class="indikator-form-group">
                    <label for="indikator-deskripsi" class="indikator-form-label">Deskripsi (Opsional)</label>
                    <div style="position: relative;">
                        <textarea id="indikator-deskripsi" name="deskripsi" class="indikator-form-input" rows="3" maxlength="5000" placeholder="Penjelasan atau konteks indikator..." onkeyup="updateCharCount('indikator-deskripsi', 'deskripsi-char-count')"></textarea>
                        <small id="deskripsi-char-count" style="float: right; color: #888; font-size: 0.85rem; margin-top: 4px;">0 / 5000</small>
                    </div>
                    <small class="indikator-text-muted" style="display: block; clear: both; margin-top: 4px;">
                        Penjelasan tambahan tentang indikator ini. (Maksimal 5000 karakter)
                        <br>💡 <strong>Format Tips:</strong> 
                        <br>• Awal dengan <code>/n</code> = Narasi (rata kiri-kanan)
                        <br>• Tanpa <code>/n</code> = Poin bernomor (1, 2, 3, ...)
                        <br>• Penomoran reset setiap ada narasi
                    </small>
                </div>

                <!-- Bukti Dukung -->
                <div class="indikator-form-group">
                    <label for="indikator-bukti-dukung" class="indikator-form-label">Bukti Dukung (Opsional)</label>
                    <textarea id="indikator-bukti-dukung" name="bukti_dukung" class="indikator-form-input" rows="3" placeholder="Dokumen atau bukti yang mendukung indikator ini..."></textarea>
                    <small class="indikator-text-muted">Referensi bukti atau dokumen yang diperlukan</small>
                </div>

                <!-- Bobot -->
                <div class="indikator-form-row">
                    <div class="indikator-form-group">
                        <label for="indikator-bobot" class="indikator-form-label">Bobot (%)</label>
                        <input type="number" id="indikator-bobot" name="bobot" class="indikator-form-input" min="0" max="100" step="0.01" placeholder="Contoh: 25">
                        <small class="indikator-text-muted">Bobot penilaian (0-100)</small>
                    </div>
                    <div class="indikator-form-group">
                        <label for="indikator-aktif" class="indikator-form-label">
                            <input type="checkbox" id="indikator-aktif" name="aktif" value="1" checked style="margin-right: 4px;">
                            Status Aktif
                        </label>
                        <small class="indikator-text-muted">Aktifkan indikator ini</small>
                    </div>
                </div>
            </div>

            <div class="indikator-modal-footer">
                <button type="button" class="indikator-btn indikator-btn-secondary" onclick="hideModal('indikatorCreateModal')">
                    <i class="fas fa-times me-1" aria-hidden="true"></i>Batal
                </button>
                <button type="submit" class="indikator-btn indikator-btn-primary">
                    <i class="fas fa-save me-1" aria-hidden="true"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
