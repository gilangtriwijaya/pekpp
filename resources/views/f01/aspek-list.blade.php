@extends('layouts.app')

@section('title', 'Penilaian F01')

@section('content')
<div class="f01-container">
    <style>
        .f01-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .f01-header {
            margin-bottom: 40px;
        }

        .f01-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .f01-header p {
            color: #6B7280;
            font-size: 1rem;
        }

        .f01-status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .f01-status-draft {
            background: #FEF3C7;
            color: #92400E;
        }

        .f01-status-submit {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .f01-status-selesai {
            background: #DCFCE7;
            color: #166534;
        }

        .f01-aspek-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 40px;
        }

        .f01-aspek-row {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .f01-aspek-row:hover {
            border-color: #4F46E5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        }

        .f01-aspek-row.disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .f01-aspek-row.disabled:hover {
            border-color: #E5E7EB;
            box-shadow: none;
        }

        .f01-aspek-row-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .f01-aspek-info {
            flex: 1;
        }

        .f01-aspek-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .f01-aspek-meta {
            display: flex;
            gap: 30px;
            font-size: 0.95rem;
            color: #6B7280;
        }

        .f01-aspek-progress {
            text-align: right;
            min-width: 200px;
        }

        .f01-progress-bar {
            width: 100%;
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .f01-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4F46E5, #7C3AED);
            transition: width 0.3s ease;
        }

        .f01-progress-text {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }

        .f01-actions {
            display: flex;
            gap: 12px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .f01-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .f01-btn-primary {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
        }

        .f01-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        .f01-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .f01-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .f01-modal-overlay.active {
            display: flex;
        }

        .f01-modal-dialog {
            background: white;
            border-radius: 12px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .f01-modal-dialog h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 16px;
        }

        .f01-modal-dialog p {
            color: #6B7280;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .f01-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .f01-modal-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .f01-modal-btn-cancel {
            background: #F3F4F6;
            color: #374151;
        }

        .f01-modal-btn-cancel:hover {
            background: #E5E7EB;
        }

        .f01-modal-btn-confirm {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
        }

        .f01-modal-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .f01-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-size: 0.95rem;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .f01-toast.success {
            background: linear-gradient(135deg, #10B981, #059669);
        }

        .f01-toast.error {
            background: linear-gradient(135deg, #EF4444, #DC2626);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 1024px) {
            .f01-container {
                max-width: 100%;
                padding: 35px 25px;
            }
        }

        @media (max-width: 768px) {
            .f01-container {
                padding: 25px 18px;
            }

            .f01-header h1 {
                font-size: 1.5rem;
            }

            .f01-aspek-row {
                padding: 16px;
            }

            .f01-aspek-row-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .f01-aspek-progress {
                width: 100%;
                text-align: left;
                margin-top: 12px;
            }

            .f01-aspek-meta {
                flex-direction: column;
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .f01-container {
                padding: 20px 15px;
            }

            .f01-header h1 {
                font-size: 1.3rem;
            }

            .f01-header p {
                font-size: 0.9rem;
            }

            .f01-aspek-name {
                font-size: 1rem;
            }

            .f01-aspek-meta {
                font-size: 0.85rem;
            }
        }

        /* ====== Indikator Detail Modal ====== */
        .f01-indikator-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.2s ease;
        }

        .f01-indikator-modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .f01-indikator-modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .f01-indikator-modal-header {
            padding: 24px;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .f01-indikator-modal-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 8px 0;
        }

        .f01-indikator-modal-close {
            background: none;
            border: none;
            font-size: 2rem;
            color: #9CA3AF;
            cursor: pointer;
            transition: color 0.2s;
        }

        .f01-indikator-modal-close:hover {
            color: #1F2937;
        }

        .f01-indikator-modal-tabs {
            display: flex;
            gap: 8px;
            padding: 16px 24px;
            border-bottom: 1px solid #E5E7EB;
            overflow-x: auto;
            flex-wrap: wrap;
        }

        .f01-indikator-modal-tab {
            padding: 10px 16px;
            background: white;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #6B7280;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .f01-indikator-modal-tab:hover {
            background: #F3F4F6;
            border-color: #4F46E5;
            color: #4F46E5;
        }

        .f01-indikator-modal-tab.active {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
            border-color: transparent;
        }

        .f01-indikator-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .f01-indikator-detail-section {
            margin-bottom: 24px;
        }

        .f01-indikator-detail-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 16px;
        }

        .f01-question-display {
            margin-bottom: 20px;
            padding: 16px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
        }

        .f01-question-display-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .f01-question-display-answer {
            color: #374151;
            padding: 10px 12px;
            background: white;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .f01-f02-data-section {
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
        }

        .f01-f02-skor {
            font-size: 1.3rem;
            font-weight: 700;
            color: #166534;
            margin-bottom: 8px;
        }

        .f01-f02-catatan {
            background: white;
            border: 1px solid #BAE6FD;
            border-radius: 6px;
            padding: 12px;
            margin-top: 8px;
            font-size: 0.9rem;
            color: #374151;
            line-height: 1.5;
        }

        .f01-bukti-dukung-display {
            background: white;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
        }

        .f01-bukti-dukung-title {
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .f01-bukti-dukung-link {
            color: #4F46E5;
            word-break: break-all;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .f01-bukti-dukung-link:hover {
            text-decoration: underline;
        }
    </style>

    {{-- Header --}}
    <div class="f01-header">
        <h1>📋 Penilaian F01</h1>
        <p>Periode {{ $pengisian->periode->tahun }} - {{ $pengisian->upp->nama }}</p>
    </div>

    {{-- Status Badge --}}
    <div>
        @if($pengisian->status === 'draft')
            <span class="f01-status-badge f01-status-draft">📝 Draft - Sedang Diisi</span>
        @elseif($pengisian->status === 'submitted')
            <span class="f01-status-badge f01-status-submit">⏳ Submitted - Menunggu Validasi F02</span>
        @elseif($pengisian->status === 'completed')
            <span class="f01-status-badge f01-status-selesai">✓ Selesai - Validasi Selesai</span>
        @endif
    </div>

    {{-- Period Status Warning --}}
    @if(!$isAcceptingInput && $pengisian->status === 'draft')
    <div style="background: #FEE2E2; border-left: 4px solid #DC2626; padding: 16px; border-radius: 6px; margin-bottom: 24px;">
        <div style="color: #991B1B; font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <span>⚠️</span>
            <span>Periode Tidak Menerima Input Baru</span>
        </div>
        <div style="color: #7F1D1D; font-size: 0.9rem;">
            @if($periodStatus === 'locked')
                Periode ini sedang dikunci dan tidak menerima input baru. Anda dapat melihat data yang sudah diisi. Hubungi administrator jika ada pertanyaan.
            @else
                Periode ini telah ditutup dan tidak menerima input baru. Data hanya dapat dilihat. Hubungi administrator jika ada pertanyaan.
            @endif
        </div>
    </div>
    @endif

    {{-- Aspek List --}}
    <div class="f01-aspek-list">
        @foreach($aspeks as $aspekData)
            <div class="f01-aspek-row {{ $isReadOnly ? 'disabled' : '' }}" 
                 data-aspek-id="{{ $aspekData['aspek']->id }}"
                 data-pengisian-id="{{ $pengisian->id }}"
                 data-clickable="{{ $isReadOnly ? 'false' : 'true' }}">
                <div class="f01-aspek-row-content">
                    <div class="f01-aspek-info">
                        <div class="f01-aspek-name">{{ $aspekData['aspek']->nama }}</div>
                        <div class="f01-aspek-meta">
                            <span>📌 Indikator: {{ $aspekData['total_indikators'] }}</span>
                            @if($aspekData['has_f02_data'])
                                <span>✓ Divalidasi: {{ $aspekData['filled_indikators'] }}/{{ $aspekData['total_indikators'] }}</span>
                            @else
                                <span>✓ Progress: {{ $aspekData['filled_indikators'] }}/{{ $aspekData['total_indikators'] }} lengkap</span>
                            @endif
                        </div>
                    </div>
                    <div class="f01-aspek-progress">
                        @if($aspekData['has_f02_data'])
                            {{-- Show score for forms with F02 validation --}}
                            <div class="f01-aspek-score">
                                <div style="font-size: 1.3rem; font-weight: 700; color: #166534;">{{ $aspekData['skor_mentah'] }}</div>
                                <div style="font-size: 0.85rem; color: #6B7280;">Skor F02</div>
                            </div>
                        @else
                            {{-- Show progress for draft/submitted forms without F02 data --}}
                            <div class="f01-progress-bar">
                                <div class="f01-progress-fill" style="width: {{ $aspekData['progress'] }}%"></div>
                            </div>
                            <div class="f01-progress-text">{{ $aspekData['progress'] }}%</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Actions --}}
    <div class="f01-actions">
        @if($pengisian->status === 'draft')
            <button type="button" class="f01-btn f01-btn-primary" id="btnFinalize" {{ !$isAcceptingInput ? 'disabled' : '' }}>
                🔒 Finalisasi & Submit
            </button>
            @if(!$isAcceptingInput)
            <div style="margin-top: 12px; font-size: 0.9rem; color: #DC2626;">
                ⚠️ Periode tidak menerima input. Tombol disabled.
            </div>
            @endif
        @else
            <button type="button" class="f01-btn f01-btn-primary" disabled>
                {{ $pengisian->status === 'submitted' ? '⏳ Sedang Divalidasi' : '✓ Selesai' }}
            </button>
        @endif
    </div>

    {{-- Indikator Detail Modal (Read-only dengan F02 data) --}}
    <div class="f01-indikator-modal-overlay" id="indikatorDetailModal">
        <div class="f01-indikator-modal-content">
            {{-- Modal Header --}}
            <div class="f01-indikator-modal-header">
                <div>
                    <h2 id="modalIndikatorTitle">Detail Indikator</h2>
                    <p id="modalIndikatorMeta" style="color: #6B7280; font-size: 0.9rem; margin: 0;"></p>
                </div>
                <button type="button" class="f01-indikator-modal-close" id="btnCloseModal">&times;</button>
            </div>

            {{-- Indikator Tabs untuk aspek yang dipilih --}}
            <div class="f01-indikator-modal-tabs" id="indikatorModalTabs"></div>

            {{-- Modal Body --}}
            <div class="f01-indikator-modal-body" id="modalBody">
                <div style="text-align: center; color: #9CA3AF; padding: 40px 20px;">
                    <div style="font-size: 1.1rem;">⏳ Memuat data indikator...</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Finalize Confirmation Modal --}}
    <div class="f01-modal-overlay" id="finalizeModal">
        <div class="f01-modal-dialog">
            <h3>Konfirmasi Finalisasi</h3>
            <p>Apakah Anda yakin? Setelah diklik, pengisian tidak bisa diedit lagi dan akan masuk ke validasi F02.</p>
            <div class="f01-modal-actions">
                <button type="button" class="f01-modal-btn f01-modal-btn-cancel" id="btnCancelFinalize">
                    Batal
                </button>
                <button type="button" class="f01-modal-btn f01-modal-btn-confirm" id="btnConfirmFinalize">
                    Lanjutkan Finalisasi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('finalizeModal');
    const btnFinalize = document.getElementById('btnFinalize');
    const btnCancel = document.getElementById('btnCancelFinalize');
    const btnConfirm = document.getElementById('btnConfirmFinalize');
    const indikatorModal = document.getElementById('indikatorDetailModal');
    const btnCloseModal = document.getElementById('btnCloseModal');

    // Add click handlers to aspek rows
    const showRoutePattern = "{{ route('f01.show', ['pengisian' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', ':id');
    
    document.querySelectorAll('.f01-aspek-row').forEach(row => {
        row.addEventListener('click', function() {
            const isClickable = this.dataset.clickable === 'true';
            const pengisianId = this.dataset.pengisianId;
            const aspekId = this.dataset.aspekId;
            
            if (isClickable) {
                // Draft forms: navigate to detailed form editor
                const targetUrl = showRoutePattern.replace(':id', pengisianId) + '?aspek=' + aspekId;
                window.location.href = targetUrl;
            } else {
                // Read-only forms: navigate to detail page
                const detailUrl = `{{ url('/') }}/f01/${pengisianId}/aspek/${aspekId}/detail`;
                window.location.href = detailUrl;
            }
        });
    });

    // Open finalize modal
    if (btnFinalize) {
        btnFinalize.addEventListener('click', function() {
            modal.classList.add('active');
        });
    }

    // Close finalize modal
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            modal.classList.remove('active');
        });
    }

    // Close finalize modal on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    // Close indikator detail modal
    if (btnCloseModal) {
        btnCloseModal.addEventListener('click', function() {
            indikatorModal.classList.remove('active');
        });
    }

    // Close indikator modal on overlay click
    indikatorModal.addEventListener('click', function(e) {
        if (e.target === indikatorModal) {
            indikatorModal.classList.remove('active');
        }
    });

    // Confirm finalize
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function() {
            finalizePengisian();
        });
    }
});

async function showIndikatorDetailModal(pengisianId, aspekId) {
    const indikatorModal = document.getElementById('indikatorDetailModal');
    const modalTitle = document.getElementById('modalIndikatorTitle');
    const modalMeta = document.getElementById('modalIndikatorMeta');
    const tabsContainer = document.getElementById('indikatorModalTabs');
    const bodyContainer = document.getElementById('modalBody');

    // Show loading state
    indikatorModal.classList.add('active');
    bodyContainer.innerHTML = '<div style="text-align: center; color: #9CA3AF; padding: 40px 20px;"><div style="font-size: 1.1rem;">⏳ Memuat data indikator...</div></div>';

    try {
        // Fetch aspek list untuk modal
        const response = await fetch(`/api/f01/${pengisianId}/aspek-list-modal`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            bodyContainer.innerHTML = '<div style="color: #EF4444; padding: 40px 20px;">Error: ' + (data.message || 'Gagal memuat data') + '</div>';
            return;
        }

        // Find aspek data
        const aspekData = data.data.find(a => a.id === parseInt(aspekId));
        if (!aspekData) {
            bodyContainer.innerHTML = '<div style="color: #EF4444; padding: 40px 20px;">Error: Aspek tidak ditemukan</div>';
            return;
        }

        // Set modal title and meta
        modalTitle.textContent = aspekData.nama;
        modalMeta.textContent = `${aspekData.indikators.length} Indikator`;

        // Create tabs
        tabsContainer.innerHTML = '';
        aspekData.indikators.forEach((ind, idx) => {
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.className = `f01-indikator-modal-tab ${idx === 0 ? 'active' : ''}`;
            tab.dataset.indikatorId = ind.id;
            tab.textContent = `${ind.urutan}. ${ind.nama.substring(0, 30)}${ind.nama.length > 30 ? '...' : ''}`;
            tab.onclick = (e) => {
                e.preventDefault();
                loadIndikatorDetail(pengisianId, ind.id, aspekData.indikators);
            };
            tabsContainer.appendChild(tab);
        });

        // Load first indikator
        if (aspekData.indikators.length > 0) {
            await loadIndikatorDetail(pengisianId, aspekData.indikators[0].id, aspekData.indikators);
        }
    } catch (error) {
        console.error('Error:', error);
        bodyContainer.innerHTML = '<div style="color: #EF4444; padding: 40px 20px;">Error: ' + error.message + '</div>';
    }
}

async function loadIndikatorDetail(pengisianId, indikatorId, indikators) {
    const bodyContainer = document.getElementById('modalBody');
    const tabsContainer = document.getElementById('indikatorModalTabs');

    // Update active tab
    tabsContainer.querySelectorAll('.f01-indikator-modal-tab').forEach(tab => tab.classList.remove('active'));
    const activeTab = tabsContainer.querySelector(`[data-indikator-id="${indikatorId}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }

    // Show loading state
    bodyContainer.innerHTML = '<div style="text-align: center; color: #9CA3AF; padding: 40px 20px;"><div style="font-size: 1.1rem;">⏳ Memuat detail indikator...</div></div>';

    try {
        const response = await fetch(`/api/f01/${pengisianId}/indikator/${indikatorId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();

        if (!result.success) {
            bodyContainer.innerHTML = '<div style="color: #EF4444; padding: 40px 20px;">Error: ' + (result.message || 'Gagal memuat data') + '</div>';
            return;
        }

        // Build HTML content
        let html = '';

        // Indikator description
        if (result.indikator.deskripsi) {
            html += `<div class="f01-indikator-detail-section">
                <div class="f01-indikator-detail-title">📌 Deskripsi Indikator</div>
                <div style="color: #374151; line-height: 1.6;">${result.indikator.deskripsi}</div>
            </div>`;
        }

        // Questions
        html += '<div class="f01-indikator-detail-section">';
        html += '<div class="f01-indikator-detail-title">❓ Pertanyaan & Jawaban</div>';

        if (result.pertanyaan.length === 0) {
            html += '<div style="color: #9CA3AF; text-align: center; padding: 20px 0;">Tidak ada pertanyaan</div>';
        } else {
            result.pertanyaan.forEach((q, idx) => {
                const jawaban = q.jawaban ? (Array.isArray(q.jawaban) ? q.jawaban.join(', ') : q.jawaban) : '(Belum dijawab)';
                html += `<div class="f01-question-display">
                    <div class="f01-question-display-label">${q.urutan}. ${q.label}</div>
                    <div class="f01-question-display-answer">${jawaban}</div>
                </div>`;
            });
        }
        html += '</div>';

        // Bukti Dukung
        if (result.bukti_dukung_url) {
            html += `<div class="f01-bukti-dukung-display">
                <div class="f01-bukti-dukung-title">🔗 Bukti Dukung</div>
                <a href="${result.bukti_dukung_url}" target="_blank" class="f01-bukti-dukung-link">${result.bukti_dukung_url}</a>
            </div>`;
        }

        // F02 Validation Data
        if (result.f02_data) {
            html += `<div class="f01-f02-data-section">
                <div style="font-weight: 600; color: #1976D2; margin-bottom: 8px;">📋 Validasi F02</div>
                <div class="f01-f02-skor">Skor: ${result.f02_data.nilai}</div>`;
            
            if (result.f02_data.catatan) {
                html += `<div style="margin-top: 12px;">
                    <div style="font-weight: 600; color: #1976D2; margin-bottom: 6px;">Catatan:</div>
                    <div class="f01-f02-catatan">${result.f02_data.catatan}</div>
                </div>`;
            }
            
            html += '</div>';
        }

        bodyContainer.innerHTML = html;
    } catch (error) {
        console.error('Error:', error);
        bodyContainer.innerHTML = '<div style="color: #EF4444; padding: 40px 20px;">Error: ' + error.message + '</div>';
    }
}

function goToAspekDetail(pengisianId, aspekId) {
    const showRoutePattern = "{{ route('f01.show', ['pengisian' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', ':id');
    const targetUrl = showRoutePattern.replace(':id', pengisianId) + '?aspek=' + aspekId;
    window.location.href = targetUrl;
}

function finalizePengisian() {
    const pengisianId = {{ $pengisian->id }};
    const finalizeRoutePattern = "{{ route('f01.finalize', ['pengisian' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', ':id');
    const finalizeUrl = finalizeRoutePattern.replace(':id', pengisianId);
    
    fetch(finalizeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            pengisian_id: pengisianId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Pengisian berhasil difinalisasi dan masuk ke validasi F02', 'success');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat memproses', 'error');
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `f01-toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection
