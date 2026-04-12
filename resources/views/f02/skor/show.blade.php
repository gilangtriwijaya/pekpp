@extends('layouts.app')

@section('title', 'Kelola Skor - ' . $aspek->nama)

@section('content')
<div class="f02-skor-show-container">
    <style>
        .f02-skor-show-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .f02-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .f02-header-info h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1F2937;
            margin: 0 0 4px 0;
        }

        .f02-header-info p {
            color: #6B7280;
            margin: 0;
            font-size: 0.95rem;
        }

        .f02-back-btn {
            padding: 8px 16px;
            background: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .f02-back-btn:hover {
            background: #E5E7EB;
        }

        .f02-indikator-row {
            background: white;
            padding: 20px 24px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border: 1px solid #E5E7EB;
        }

        .f02-ind-info {
            flex: 1;
        }

        .f02-ind-name {
            font-size: 1rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 4px;
        }

        .f02-ind-meta {
            font-size: 0.85rem;
            color: #6B7280;
        }

        .f02-ind-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 8px;
        }

        .f02-ind-status.configured {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .f02-ind-status.empty {
            background: #FEF3C7;
            color: #92400E;
        }

        .f02-ind-actions {
            display: flex;
            gap: 8px;
        }

        .f02-edit-btn {
            padding: 8px 16px;
            background: #4F46E5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .f02-edit-btn:hover {
            background: #4338CA;
        }

        {{-- Modal Styles --}}
        .f02-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .f02-modal-overlay.active {
            display: flex;
        }

        .f02-modal-dialog {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .f02-modal-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 16px;
        }

        .f02-modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: #1F2937;
        }

        .f02-modal-subtext {
            color: #6B7280;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .f02-skor-input-group {
            margin-bottom: 20px;
        }

        .f02-skor-label {
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
            display: block;
        }

        .f02-skor-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            font-size: 0.95rem;
            min-height: 60px;
            resize: vertical;
        }

        .f02-skor-textarea:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .f02-modal-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #E5E7EB;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .f02-btn-cancel {
            padding: 10px 20px;
            background: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .f02-btn-cancel:hover {
            background: #E5E7EB;
        }

        .f02-btn-save {
            padding: 10px 20px;
            background: #4F46E5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .f02-btn-save:hover {
            background: #4338CA;
        }

        .f02-btn-save:disabled {
            background: #D1D5DB;
            cursor: not-allowed;
        }
    </style>

    {{-- Header --}}
    <div class="f02-header-bar">
        <div class="f02-header-info">
            <h1>{{ $aspek->nama }}</h1>
            <p>Periode: {{ $periode->tahun }} {{ $periode->nama }} • {{ count($indikators) }} indikator</p>
        </div>
        <button class="f02-back-btn" onclick="window.location.href = '{{ route('f02.skor.index') }}'">
            ← Kembali
        </button>
    </div>

    {{-- Indikator List --}}
    <div class="f02-indikator-list">
        @forelse($indikators as $item)
            @php
            $ind = $item['indikator'];
            $skor = $item['skor'];
            @endphp
            <div class="f02-indikator-row">
                <div class="f02-ind-info">
                    <div class="f02-ind-name">
                        {{ $ind->urutan }}. {{ $ind->nama }}
                        @if($skor)
                            <span class="f02-ind-status configured">✓ Sudah dikonfigurasi</span>
                        @else
                            <span class="f02-ind-status empty">⚠ Belum dikonfigurasi</span>
                        @endif
                    </div>
                    <div class="f02-ind-meta">
                        ID: {{ $ind->id }}
                    </div>
                </div>
                <div class="f02-ind-actions">
                    <button class="f02-edit-btn" onclick="openSkorModal({{ $ind->id }})">
                        ✎ Atur Skor
                    </button>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #6B7280;">
                <p>Tidak ada indikator</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal: Edit Skor --}}
<div class="f02-modal-overlay" id="skorModal">
    <div class="f02-modal-dialog">
        <div class="f02-modal-header">
            <h2 id="skorModalTitle">Atur Skor Indikator</h2>
            <p class="f02-modal-subtext" id="skorModalSubtext"></p>
        </div>

        <form id="skorForm">
            @csrf
            <input type="hidden" id="indikatorId" name="indikator_id">

            <div class="f02-skor-input-group">
                <label class="f02-skor-label">Skor 0 - Tidak Memenuhi</label>
                <textarea class="f02-skor-textarea" name="skor_0" id="skor_0" placeholder="Masukkan narasi untuk skor 0..."></textarea>
            </div>

            <div class="f02-skor-input-group">
                <label class="f02-skor-label">Skor 1 - Narasi</label>
                <textarea class="f02-skor-textarea" name="skor_1" id="skor_1" placeholder="Masukkan narasi untuk skor 1..."></textarea>
            </div>

            <div class="f02-skor-input-group">
                <label class="f02-skor-label">Skor 2 - Narasi</label>
                <textarea class="f02-skor-textarea" name="skor_2" id="skor_2" placeholder="Masukkan narasi untuk skor 2..."></textarea>
            </div>

            <div class="f02-skor-input-group">
                <label class="f02-skor-label">Skor 3 - Narasi</label>
                <textarea class="f02-skor-textarea" name="skor_3" id="skor_3" placeholder="Masukkan narasi untuk skor 3..."></textarea>
            </div>

            <div class="f02-skor-input-group">
                <label class="f02-skor-label">Skor 4 - Narasi</label>
                <textarea class="f02-skor-textarea" name="skor_4" id="skor_4" placeholder="Masukkan narasi untuk skor 4..."></textarea>
            </div>

            <div class="f02-skor-input-group">
                <label class="f02-skor-label">Skor 5 - Narasi</label>
                <textarea class="f02-skor-textarea" name="skor_5" id="skor_5" placeholder="Masukkan narasi untuk skor 5..."></textarea>
            </div>

            <div class="f02-modal-footer">
                <button type="button" class="f02-btn-cancel" onclick="closeSkorModal()">Tutup</button>
                <button type="submit" class="f02-btn-save" id="skorSaveBtn">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSkorModal(indikatorId) {
    const modal = document.getElementById('skorModal');
    const form = document.getElementById('skorForm');
    
    // Reset form
    form.reset();
    document.getElementById('indikatorId').value = indikatorId;

    // Fetch existing skor
    fetch(`{{ route('f02.skor.get', ['indikatorId' => ':id']) }}`.replace(':id', indikatorId))
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('skorModalTitle').textContent = 
                    'Atur Skor: ' + data.indikator.nama;
                document.getElementById('skorModalSubtext').textContent = 
                    'Indikator #' + data.indikator.id;
                
                // Populate form
                document.getElementById('skor_0').value = data.skor.skor_0 || '';
                document.getElementById('skor_1').value = data.skor.skor_1 || '';
                document.getElementById('skor_2').value = data.skor.skor_2 || '';
                document.getElementById('skor_3').value = data.skor.skor_3 || '';
                document.getElementById('skor_4').value = data.skor.skor_4 || '';
                document.getElementById('skor_5').value = data.skor.skor_5 || '';

                // Show modal
                modal.classList.add('active');
            }
        })
        .catch(() => alert('Error loading skor'));
}

function closeSkorModal() {
    document.getElementById('skorModal').classList.remove('active');
}

document.getElementById('skorForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(document.getElementById('skorForm'));
    const btn = document.getElementById('skorSaveBtn');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const response = await fetch('{{ route('f02.skor.save') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            alert('✓ Skor berhasil disimpan');
            closeSkorModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal menyimpan'));
        }
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    }
});

// Close modal on overlay click
document.getElementById('skorModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('skorModal')) {
        closeSkorModal();
    }
});
</script>
@endsection
