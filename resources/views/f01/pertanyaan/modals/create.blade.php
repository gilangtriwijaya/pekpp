<!-- Create/Edit Modal -->
<div class="pertanyaan-modal-overlay" id="pertanyaanCreateModal" 
     role="dialog" 
     aria-labelledby="pertanyaan-createModalLabel" 
     aria-hidden="true">
    <div class="pertanyaan-modal pertanyaan-modal-lg">
        <div class="pertanyaan-modal-header">
            <h5 class="pertanyaan-modal-title" id="pertanyaan-createModalLabel">Tambah Pertanyaan Baru</h5>
            <button class="pertanyaan-modal-close" 
                    onclick="hideModal('pertanyaanCreateModal')"
                    aria-label="Tutup dialog"
                    type="button">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <form id="pertanyaanForm">
            @csrf
            <div class="pertanyaan-modal-body">
                <!-- Aspek Selection (NEW) -->
                <div class="pertanyaan-form-group">
                    <label for="pertanyaan-aspek" class="pertanyaan-form-label">Pilih Aspek <span class="required" aria-label="required">*</span></label>
                    <select id="pertanyaan-aspek" name="aspek_id" class="pertanyaan-form-input" aria-describedby="aspek-hint" required onchange="loadIndicatorsByAspek()">
                        <option value="">-- Pilih Aspek --</option>
                        @foreach($aspeks as $asp)
                            <option value="{{ $asp->id }}">[{{ $asp->kode }}] {{ Str::limit($asp->nama, 50) }}</option>
                        @endforeach
                    </select>
                    <small id="aspek-hint" class="pertanyaan-text-muted">Pilih aspek terlebih dahulu untuk memfilter indikator</small>
                </div>

                <!-- Indikator Selection (DEPENDENT) -->
                <div class="pertanyaan-form-group">
                    <label for="pertanyaan-indikator" class="pertanyaan-form-label">Pilih Indikator <span class="required" aria-label="required">*</span></label>
                    <select id="pertanyaan-indikator" name="indikator_id" class="pertanyaan-form-input" aria-describedby="indikator-hint" required disabled>
                        <option value="">-- Pilih Indikator --</option>
                    </select>
                    <small id="indikator-hint" class="pertanyaan-text-muted">Pertanyaan akan terikat pada indikator yang dipilih</small>
                </div>

                <!-- Kode & Urutan -->
                <div class="pertanyaan-form-row">
                    <div class="pertanyaan-form-group">
                        <label for="pertanyaan-kode" class="pertanyaan-form-label">Kode Pertanyaan</label>
                        <input type="text" id="pertanyaan-kode" name="kode" class="pertanyaan-form-input" placeholder="Contoh: Q1 (opsional)">
                        <small class="pertanyaan-text-muted">Jika kosong, auto-generate</small>
                    </div>
                    <div class="pertanyaan-form-group">
                        <label for="pertanyaan-urutan" class="pertanyaan-form-label">Urutan</label>
                        <input type="number" id="pertanyaan-urutan" name="urutan" class="pertanyaan-form-input" min="1" placeholder="Opsional">
                        <small class="pertanyaan-text-muted">Jika kosong, auto-generate</small>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="pertanyaan-form-group">
                    <label for="pertanyaan-label" class="pertanyaan-form-label">Pertanyaan <span class="required" aria-label="required">*</span></label>
                    <textarea id="pertanyaan-label" name="label" class="pertanyaan-form-input" rows="3" placeholder="Ketik pertanyaan lengkap..." required aria-describedby="label-hint"></textarea>
                    <small id="label-hint" class="pertanyaan-text-muted">Masukkan pertanyaan secara jelas dan lengkap</small>
                </div>

                <!-- Question Type -->
                <div class="pertanyaan-form-group">
                    <label for="pertanyaan-tipeInput" class="pertanyaan-form-label">Tipe Pertanyaan <span class="required" aria-label="required">*</span></label>
                    <select id="pertanyaan-tipeInput" name="tipe_input" class="pertanyaan-form-input" required onchange="updateTipeInputUI('pertanyaan-tipeInput')">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="text">📝 Teks Pendek</option>
                        <option value="textarea">📄 Teks Panjang</option>
                        <option value="number">🔢 Angka</option>
                        <option value="radio">⭕ Pilihan Ganda</option>
                        <option value="checkbox">☑️ Pilihan Banyak</option>
                        <option value="select">📋 Dropdown</option>
                        <option value="yesno">✅ Ya/Tidak</option>
                        <option value="skala">📊 Skala/Rating</option>
                    </select>
                </div>

                <!-- Min/Max (untuk number & skala) -->
                <div id="pertanyaan-minMaxGroup" class="pertanyaan-form-hidden">
                    <div class="pertanyaan-form-row">
                        <div class="pertanyaan-form-group">
                            <label class="pertanyaan-form-label">Nilai Minimum</label>
                            <input type="number" id="pertanyaan-min" name="min" class="pertanyaan-form-input" placeholder="1">
                        </div>
                        <div class="pertanyaan-form-group">
                            <label class="pertanyaan-form-label">Nilai Maksimum</label>
                            <input type="number" id="pertanyaan-max" name="max" class="pertanyaan-form-input" placeholder="10">
                        </div>
                    </div>
                </div>

                <!-- Options (untuk radio, checkbox, select) -->
                <div id="pertanyaan-opsiGroup" class="pertanyaan-form-hidden">
                    <label class="pertanyaan-form-label">Opsi Jawaban</label>
                    <div id="pertanyaan-opsiContainer" class="pertanyaan-opsi-container"></div>
                    <button type="button" class="pertanyaan-btn-tambah-opsi" onclick="addOpsiInput()">
                        <i class="fas fa-plus me-2"></i>Tambah Opsi Jawaban
                    </button>
                </div>

                <!-- Conditional Questions (untuk yesno type) -->
                <div id="pertanyaan-conditionalGroup" class="pertanyaan-form-hidden">
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; border-left: 4px solid #0d6efd;">
                        <label class="pertanyaan-form-label" style="margin-bottom: 15px;">
                            <i class="fas fa-code-branch me-2"></i>Pertanyaan Lanjutan (Conditional)
                        </label>
                        <small class="pertanyaan-text-muted d-block mb-3">
                            Tambahkan pertanyaan yang akan muncul berdasarkan jawaban Ya/Tidak pada pertanyaan ini.
                        </small>
                        
                        <div id="pertanyaan-conditionalContainer"></div>
                        
                        <button type="button" class="pertanyaan-btn pertanyaan-btn-sm" onclick="addConditionalQuestion()" 
                                style="background-color: #198754; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                            <i class="fas fa-plus me-1"></i>Tambah Pertanyaan Lanjutan
                        </button>
                    </div>
                </div>

                <!-- Skip if Answer (Sequential Skip Logic) -->
                <div id="pertanyaan-skipGroup" class="pertanyaan-form-hidden">
                    <div style="background-color: #fff3cd; padding: 15px; border-radius: 4px; border-left: 4px solid #ff6b6b;">
                        <label for="pertanyaan-skipIfAnswer" class="pertanyaan-form-label" style="margin-bottom: 10px;">
                            <i class="fas fa-forward me-2"></i>Jika dijawab dengan
                        </label>
                        <small class="pertanyaan-text-muted d-block mb-3">
                            Jika pertanyaan dijawab dengan opsi ini, semua pertanyaan berikutnya dalam indikator yang sama akan di-skip.
                        </small>
                        
                        <select id="pertanyaan-skipIfAnswer" name="skip_if_answer" class="pertanyaan-form-input" onchange="updateSkipDropdown('pertanyaan-skipIfAnswer')">
                            <option value="">-- Tidak ada skip --</option>
                        </select>
                    </div>
                </div>

                <!-- Checkboxes -->
                <div class="pertanyaan-form-divider">
                    <div class="pertanyaan-form-checkbox">
                        <input type="checkbox" id="pertanyaan-wajib" name="wajib" value="1" class="pertanyaan-form-checkbox-input">
                        <label for="pertanyaan-wajib" class="pertanyaan-form-checkbox-label">
                            Wajib Diisi
                        </label>
                    </div>
                    
                    <div class="pertanyaan-form-checkbox">
                        <input type="checkbox" id="pertanyaan-aktif" name="aktif" value="1" checked class="pertanyaan-form-checkbox-input">
                        <label for="pertanyaan-aktif" class="pertanyaan-form-checkbox-label">
                            Pertanyaan Aktif
                        </label>
                    </div>

                    <div class="pertanyaan-form-checkbox" id="pertanyaan-allowLainnyaGroup" style="display: none;">
                        <input type="checkbox" id="pertanyaan-allowLainnya" name="allow_lainnya" value="1" class="pertanyaan-form-checkbox-input">
                        <label for="pertanyaan-allowLainnya" class="pertanyaan-form-checkbox-label">
                            Izinkan Opsi "Lainnya" (untuk Pilihan Banyak)
                        </label>
                    </div>
                </div>
            </div>

            <div class="pertanyaan-modal-footer">
                <button type="button" class="pertanyaan-btn pertanyaan-btn-secondary" onclick="hideModal('pertanyaanCreateModal')">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="submit" id="pertanyaan-submitBtn" class="pertanyaan-btn pertanyaan-btn-primary">
                    <i class="fas fa-check me-1"></i>Simpan Pertanyaan
                </button>
            </div>
        </form>
    </div>
</div>
