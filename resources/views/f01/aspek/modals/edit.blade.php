{{-- Edit Aspek Modal --}}
<div class="aspek-modal-overlay" id="aspekEditModal">
    <div class="aspek-modal">
        <div class="aspek-modal-header">
            <h3 class="aspek-modal-title">Edit Aspek</h3>
            <button class="aspek-modal-close" onclick="closeModal('aspekEditModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="editForm" onsubmit="submitEditForm(event)">
            @csrf
            <input type="hidden" id="edit-id" name="id">
            <div class="aspek-modal-body">
                <div class="aspek-form-row">
                    <div class="aspek-form-group">
                        <label class="aspek-form-label" for="edit-periode">
                            Periode <span class="required">*</span>
                        </label>
                        <select 
                            class="aspek-form-input" 
                            id="edit-periode" 
                            name="periode_id"
                            required
                        >
                            <option value="">-- Pilih Periode --</option>
                            @foreach($periodes ?? [] as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->tahun }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="aspek-form-group">
                        <label class="aspek-form-label" for="edit-kode">
                            Kode <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="aspek-form-input" 
                            id="edit-kode" 
                            name="kode" 
                            placeholder="Mis: A1, A2..."
                            required
                        >
                    </div>
                </div>

                <div class="aspek-form-row">
                    <div class="aspek-form-group">
                        <label class="aspek-form-label" for="edit-nama">
                            Nama Aspek <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="aspek-form-input" 
                            id="edit-nama" 
                            name="nama" 
                            placeholder="Masukkan nama aspek"
                            required
                        >
                    </div>
                    <div class="aspek-form-group">
                        <label class="aspek-form-label" for="edit-domain">
                            Domain <span class="required">*</span>
                        </label>
                        <select 
                            class="aspek-form-input" 
                            id="edit-domain" 
                            name="domain"
                            required
                        >
                            <option value="">-- Pilih Domain --</option>
                            <option value="internal">Internal</option>
                            <option value="publik">Publik</option>
                        </select>
                    </div>                    <div class="aspek-form-group">
                        <label class="aspek-form-label" for="edit-bobot">
                            Bobot <span class="required">*</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input 
                                type="number" 
                                class="aspek-form-input" 
                                id="edit-bobot" 
                                name="bobot" 
                                placeholder="0"
                                min="0" 
                                max="100" 
                                step="0.01"
                                required
                            >
                            <span style="font-size: 14px; color: #6B7280; font-weight: 500; white-space: nowrap;">%</span>
                        </div>
                        <small style="color: #9CA3AF; font-size: 12px; margin-top: 4px;">Nilai 0-100</small>
                    </div>                </div>

                <div class="aspek-form-group">
                    <label class="aspek-form-label" for="edit-keterangan">
                        Keterangan
                    </label>
                    <textarea 
                        class="aspek-form-input" 
                        id="edit-keterangan" 
                        name="keterangan" 
                        placeholder="Masukkan keterangan aspek"
                        rows="3"
                    ></textarea>
                </div>

                <div class="aspek-form-group" style="margin-top: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer;">
                        <input type="hidden" name="aktif" value="0">
                        <input type="checkbox" id="edit-aktif" name="aktif" value="1" style="cursor: pointer;">
                        Aktifkan Aspek Ini
                    </label>
                </div>
            </div>
            <div class="aspek-modal-footer">
                <button type="button" class="aspek-btn aspek-btn-secondary" onclick="closeModal('aspekEditModal')">Batal</button>
                <button type="submit" class="aspek-btn aspek-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>


