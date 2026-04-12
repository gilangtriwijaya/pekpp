<!-- Detail Modal -->
<div class="indikator-modal-overlay" id="indikatorDetailModal" 
     role="dialog" 
     aria-labelledby="indikator-detailModalLabel" 
     aria-hidden="true">
    <div class="indikator-modal indikator-modal-lg">
        <div class="indikator-modal-header">
            <h5 class="indikator-modal-title" id="indikator-detailModalLabel">
                <i class="fas fa-info-circle me-2" aria-hidden="true"></i>Detail Indikator
            </h5>
            <button class="indikator-modal-close" 
                    onclick="hideModal('indikatorDetailModal')"
                    aria-label="Tutup dialog"
                    type="button">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="indikator-modal-body" id="indikator-detailContent" role="region" aria-live="polite">
            <!-- Detail content will be populated here -->
        </div>

        <div class="indikator-modal-footer">
            <button type="button" class="indikator-btn indikator-btn-secondary" onclick="hideModal('indikatorDetailModal')">
                <i class="fas fa-times me-1" aria-hidden="true"></i>Tutup
            </button>
        </div>
    </div>
</div>

<style>
.detail-group {
    margin-bottom: 1.5rem;
}

.detail-label {
    font-weight: 600;
    color: #374151;
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.detail-value {
    color: #666;
    margin: 0;
    padding-left: 1rem;
    border-left: 3px solid #3b82f6;
    line-height: 1.6;
    word-break: break-word;
}
</style>
