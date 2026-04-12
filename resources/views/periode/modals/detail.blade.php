{{-- Detail Periode Modal --}}
<div class="periode-modal-overlay" id="periodeDetailModal">
    <div class="periode-modal">
        <div class="periode-modal-header">
            <h3 class="periode-modal-title">Detail Periode</h3>
            <button class="periode-modal-close" onclick="closeModal('periodeDetailModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="periode-modal-body">
            <div style="display: grid; gap: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">ID Periode</div>
                        <div style="font-size: 15px; color: #0f172a; font-weight: 500;" id="periode-detail-id">-</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Status</div>
                        <span class="periode-badge periode-badge-active" id="periode-detail-status">
                            <span class="periode-badge-dot"></span>
                            <span id="periode-detail-status-text">Aktif</span>
                        </span>
                    </div>
                </div>

                <div>
                    <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Nama Periode</div>
                    <div style="font-size: 15px; color: #0f172a; font-weight: 500;" id="periode-detail-nama">-</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Tahun</div>
                        <div style="font-size: 15px; color: #0f172a; font-weight: 500;" id="periode-detail-tahun">-</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Aktif</div>
                        <div style="font-size: 15px; color: #0f172a; font-weight: 500;" id="periode-detail-aktif">-</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Tanggal Mulai</div>
                        <div style="font-size: 15px; color: #0f172a; font-weight: 500;" id="periode-detail-mulai">-</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Tanggal Selesai</div>
                        <div style="font-size: 15px; color: #0f172a; font-weight: 500;" id="periode-detail-selesai">-</div>
                    </div>
                </div>

                <div>
                    <div style="font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Status Penerimaan Input</div>
                    <div style="font-size: 15px; color: #0f172a; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;" id="periode-detail-status-pengisian">
                        <span>-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="periode-modal-footer">
            <button type="button" class="periode-btn periode-btn-secondary" onclick="closeModal('periodeDetailModal')">Tutup</button>
            <button type="button" class="periode-btn periode-btn-primary" onclick="closeModal('periodeDetailModal'); openEditModal(window.currentDetailData || {})">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Periode
            </button>
        </div>
    </div>
</div>
