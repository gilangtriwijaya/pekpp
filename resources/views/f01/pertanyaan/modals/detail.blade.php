<!-- Detail Modal -->
<div class="pertanyaan-modal-overlay" id="pertanyaanDetailModal" 
     role="dialog" 
     aria-labelledby="pertanyaan-detailModalLabel" 
     aria-hidden="true">
    <div class="pertanyaan-modal pertanyaan-modal-lg">
        <div class="pertanyaan-modal-header">
            <h5 class="pertanyaan-modal-title" id="pertanyaan-detailModalLabel">
                <i class="fas fa-info-circle me-2" aria-hidden="true"></i>Detail Pertanyaan
            </h5>
            <button class="pertanyaan-modal-close" 
                    onclick="hideModal('pertanyaanDetailModal')"
                    aria-label="Tutup dialog"
                    type="button">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="pertanyaan-modal-body" id="pertanyaan-detailContent" role="region" aria-live="polite">
            <!-- Detail content will be populated here -->
        </div>

        <div class="pertanyaan-modal-footer">
            <button type="button" class="pertanyaan-btn pertanyaan-btn-secondary" onclick="hideModal('pertanyaanDetailModal')">
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

.detail-value ul {
    padding-left: 0;
    margin-bottom: 0;
    list-style: none;
}

.detail-value li {
    margin-bottom: 0.5rem;
    padding-left: 0;
}

.detail-value li:last-child {
    margin-bottom: 0;
}
</style>
