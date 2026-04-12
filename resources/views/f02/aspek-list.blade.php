@extends('layouts.app')

@section('title', 'Validasi F02')

@section('content')
<div class="f02-container">
    <style>
        .f02-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .f02-header {
            margin-bottom: 40px;
        }

        .f02-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .f02-header p {
            color: #6B7280;
            font-size: 1rem;
        }

        .f02-status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .f02-status-draft {
            background: #FEF3C7;
            color: #92400E;
        }

        .f02-status-selesai {
            background: #DCFCE7;
            color: #166534;
        }

        .f02-aspek-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 40px;
        }

        .f02-aspek-row {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .f02-aspek-row:hover {
            border-color: #4F46E5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        }

        .f02-aspek-row-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .f02-aspek-info {
            flex: 1;
        }

        .f02-aspek-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .f02-aspek-meta {
            display: flex;
            gap: 30px;
            font-size: 0.95rem;
            color: #6B7280;
        }

        .f02-aspek-progress {
            text-align: right;
            min-width: 200px;
        }

        .f02-progress-bar {
            width: 100%;
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .f02-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4F46E5, #7C3AED);
            transition: width 0.3s ease;
        }

        .f02-progress-text {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }

        .f02-actions {
            display: flex;
            gap: 12px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .f02-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .f02-btn-primary {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
        }

        .f02-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        .f02-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .f02-modal-overlay {
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

        .f02-modal-overlay.active {
            display: flex;
        }

        .f02-modal-dialog {
            background: white;
            border-radius: 12px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .f02-modal-dialog h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 16px;
        }

        .f02-modal-dialog p {
            color: #6B7280;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .f02-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .f02-modal-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .f02-modal-btn-cancel {
            background: #F3F4F6;
            color: #374151;
        }

        .f02-modal-btn-cancel:hover {
            background: #E5E7EB;
        }

        .f02-modal-btn-confirm {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
        }

        .f02-modal-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .f02-toast {
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

        .f02-toast.success {
            background: linear-gradient(135deg, #10B981, #059669);
        }

        .f02-toast.error {
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
            .f02-container {
                max-width: 100%;
                padding: 35px 25px;
            }
        }

        @media (max-width: 768px) {
            .f02-container {
                padding: 25px 18px;
            }

            .f02-header h1 {
                font-size: 1.5rem;
            }

            .f02-aspek-row {
                padding: 16px;
            }

            .f02-aspek-row-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .f02-aspek-progress {
                width: 100%;
                text-align: left;
                margin-top: 12px;
            }

            .f02-aspek-meta {
                flex-direction: column;
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .f02-container {
                padding: 20px 15px;
            }

            .f02-header h1 {
                font-size: 1.3rem;
            }

            .f02-header p {
                font-size: 0.9rem;
            }

            .f02-aspek-name {
                font-size: 1rem;
            }

            .f02-aspek-meta {
                font-size: 0.85rem;
            }
        }
    </style>

    {{-- Header --}}
    <div class="f02-header">
        <h1>🔍 Validasi F02</h1>
        <p>Periode {{ $validasi->periode->tahun }} - {{ $validasi->upp->nama }}</p>
    </div>

    {{-- Status Badge --}}
    <div>
        @if($validasi->status === 'draft')
            <span class="f02-status-badge f02-status-draft">📝 Draft - Sedang Divalidasi</span>
        @elseif($validasi->status === 'selesai')
            <span class="f02-status-badge f02-status-selesai">✓ Selesai - Validasi Selesai</span>
        @endif
    </div>

    {{-- Aspek List --}}
    <div class="f02-aspek-list">
        @foreach($aspeks as $aspekData)
            <div class="f02-aspek-row" 
                 data-aspek-id="{{ $aspekData['aspek']->id }}"
                 data-validasi-id="{{ $validasi->id }}"
                 data-clickable="true">
                <div class="f02-aspek-row-content">
                    <div class="f02-aspek-info">
                        <div class="f02-aspek-name">{{ $aspekData['aspek']->nama }}</div>
                        <div class="f02-aspek-meta">
                            <span>📌 Indikator: {{ $aspekData['total_indikators'] }}</span>
                            <span>✓ Progress: {{ $aspekData['filled_indikators'] }}/{{ $aspekData['total_indikators'] }} divalidasi</span>
                            <span>💯 Skor Mentah: {{ (int)$aspekData['skor_mentah'] }}</span>
                        </div>
                    </div>
                    <div class="f02-aspek-progress">
                        <div class="f02-progress-bar">
                            <div class="f02-progress-fill" style="width: {{ $aspekData['progress'] }}%"></div>
                        </div>
                        <div class="f02-progress-text">{{ $aspekData['progress'] }}%</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Actions --}}
    <div class="f02-actions">
        <button type="button" class="f02-btn f02-btn-primary" id="btnValidate">
            ✓ Validasi & Submit
        </button>
    </div>

    {{-- Validation Confirmation Modal --}}
    <div class="f02-modal-overlay" id="validateModal">
        <div class="f02-modal-dialog">
            <h3>Konfirmasi Validasi</h3>
            <p>Apakah Anda yakin semua indikator sudah divalidasi? Setelah diklik, hasil validasi tidak bisa diubah lagi.</p>
            <div class="f02-modal-actions">
                <button type="button" class="f02-modal-btn f02-modal-btn-cancel" id="btnCancelValidate">
                    Batal
                </button>
                <button type="button" class="f02-modal-btn f02-modal-btn-confirm" id="btnConfirmValidate">
                    Lanjutkan Validasi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('validateModal');
    const btnValidate = document.getElementById('btnValidate');
    const btnCancel = document.getElementById('btnCancelValidate');
    const btnConfirm = document.getElementById('btnConfirmValidate');

    // Add click handlers to aspek rows
    document.querySelectorAll('.f02-aspek-row').forEach(row => {
        row.addEventListener('click', function() {
            const validasiId = this.dataset.validasiId;
            const aspekId = this.dataset.aspekId;
            const detailUrl = "{{ route('f02.validasi-detail', ['validasi' => 'PLACEHOLDER', 'aspek' => 'ASPEK']) }}"
                .replace('PLACEHOLDER', validasiId)
                .replace('ASPEK', aspekId);
            window.location.href = detailUrl;
        });
    });

    // Open modal
    if (btnValidate) {
        btnValidate.addEventListener('click', function() {
            modal.classList.add('active');
        });
    }

    // Close modal
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            modal.classList.remove('active');
        });
    }

    // Close on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    // Confirm validate
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function() {
            validateF02();
        });
    }
});

function validateF02() {
    const validasiId = document.querySelector('[data-validasi-id]').dataset.validasiId;
    const validateUrl = "{{ route('f02.finalize-validation', ['validasi' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', validasiId);
    
    fetch(validateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            validasi_id: validasiId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Validasi F02 berhasil disimpan', 'success');
            setTimeout(() => {
                window.location.href = "{{ route('f02.index') }}";
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
    toast.className = `f02-toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection
