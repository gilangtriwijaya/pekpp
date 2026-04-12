{{-- Edit Periode Modal --}}
<div class="periode-modal-overlay" id="periodeEditModal">
    <div class="periode-modal">
        <div class="periode-modal-header">
            <h3 class="periode-modal-title">Edit Periode</h3>
            <button class="periode-modal-close" onclick="closeModal('periodeEditModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form onsubmit="submitEditForm(event)" id="editForm">
            @csrf
            <input type="hidden" id="edit-id">
            <div class="periode-modal-body">
                <div class="periode-form-row">
                    <div class="periode-form-group">
                        <label class="periode-form-label" for="edit-nama">
                            Nama <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="periode-form-input" 
                            id="edit-nama" 
                            name="nama" 
                            placeholder="Contoh: Periode Penilaian 2026"
                            required
                        >
                    </div>
                    <div class="periode-form-group">
                        <label class="periode-form-label" for="edit-tahun">
                            Tahun <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            class="periode-form-input" 
                            id="edit-tahun" 
                            name="tahun" 
                            placeholder="2026"
                            min="1900"
                            max="2100"
                            required
                        >
                    </div>
                </div>

                <div class="periode-form-row">
                    <div class="periode-form-group">
                        <label class="periode-form-label" for="edit-mulai">
                            Tanggal Mulai <span class="required">*</span>
                        </label>
                        <input 
                            type="date" 
                            class="periode-form-input" 
                            id="edit-mulai" 
                            name="tanggal_mulai"
                            required
                        >
                    </div>
                    <div class="periode-form-group">
                        <label class="periode-form-label" for="edit-selesai">
                            Tanggal Selesai <span class="required">*</span>
                        </label>
                        <input 
                            type="date" 
                            class="periode-form-input" 
                            id="edit-selesai" 
                            name="tanggal_selesai"
                            required
                        >
                    </div>
                </div>

                <div class="periode-form-group">
                    <label class="periode-form-label" for="edit-target-f03">
                        Target Responden F03
                    </label>
                    <input 
                        type="number" 
                        class="periode-form-input" 
                        id="edit-target-f03" 
                        name="target_responden_f03" 
                        placeholder="0 untuk unlimited"
                        min="0"
                        value="0"
                    >
                    <small style="color: #6B7280; font-size: 12px; margin-top: 4px; display: block;">Jumlah responden minimal untuk F03 (0 = unlimited)</small>
                </div>

                <div class="periode-form-group">
                    <label class="periode-form-label" for="edit-status-pengisian">
                        Status Penerimaan Input
                    </label>
                    <select 
                        class="periode-form-input" 
                        id="edit-status-pengisian" 
                        name="status_pengisian"
                        style="cursor: pointer;"
                    >
                        <option value="open">🟢 Open - Menerima Input Baru</option>
                        <option value="locked">🔒 Locked - Input Terkunci</option>
                        <option value="closed">🗂️ Closed - Ditutup/Arsip</option>
                    </select>
                    <small style="color: #6B7280; font-size: 12px; margin-top: 4px; display: block;">
                        • Open: Periode menerima input baru dari pengisi<br>
                        • Locked: Data terlihat tapi input baru ditolak<br>
                        • Closed: Periode ditutup dan diarsipkan
                    </small>
                </div>

                <div class="periode-form-group" style="margin-top: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer;">
                        <input type="hidden" name="is_aktif" value="0">
                        <input type="checkbox" name="is_aktif" id="edit-aktif" value="1" style="cursor: pointer;">
                        Jadikan Periode Aktif
                    </label>
                </div>
            </div>
            <div class="periode-modal-footer">
                <button type="button" class="periode-btn periode-btn-secondary" onclick="closeModal('periodeEditModal')">Batal</button>
                <button type="submit" class="periode-btn periode-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Update Periode
                </button>
            </div>
        </form>
    </div>
</div>
