@extends('layouts.app')
@section('title','F03 Dashboard UPP')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .f03-dashboard { padding: 24px 20px; max-width: 1400px; margin: 0 auto; }
    .f03-dashboard-header { margin-bottom: 30px; }
    .f03-dashboard-title { font-size: 28px; font-weight: 700; color: #1F2937; margin-bottom: 8px; }
    .f03-dashboard-subtitle { color: #6B7280; font-size: 15px; }
    
    .f03-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .f03-stat-card {
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #667eea;
    }
    .f03-stat-label { font-size: 13px; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 10px; }
    .f03-stat-value { font-size: 32px; font-weight: 700; color: #1F2937; }
    .f03-stat-subtitle { font-size: 12px; color: #9CA3AF; margin-top: 8px; }
    
    .f03-token-section {
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .f03-token-title { font-size: 16px; font-weight: 700; color: #1F2937; margin-bottom: 16px; }
    
    .f03-token-content { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    
    .f03-token-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .f03-token-row {
        display: flex;
        justify-content: space-between;
        padding: 12px;
        background-color: #F9FAFB;
        border-radius: 6px;
        font-size: 14px;
    }
    .f03-token-row label { font-weight: 600; color: #374151; }
    .f03-token-row .value { color: #667eea; font-family: 'Courier New', monospace; font-weight: 600; }
    
    .f03-qr-section { text-align: center; }
    .f03-qr-title { font-size: 13px; font-weight: 600; color: #6B7280; margin-bottom: 12px; }
    .f03-qr-section svg { max-width: 200px; height: auto; }
    
    .f03-copy-btn {
        background-color: #E5E7EB;
        color: #374151;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .f03-copy-btn:hover { background-color: #D1D5DB; }
    .f03-copy-btn.copied { background-color: #DCFCE7; color: #166534; }
    
    .f03-scores-section {
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .f03-scores-title { font-size: 16px; font-weight: 700; color: #1F2937; margin-bottom: 24px; }
    
    .f03-score-bars {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    
    .f03-score-item {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .f03-score-label { font-size: 13px; font-weight: 600; color: #374151; width: 180px; flex-shrink: 0; text-align: left; }
    
    .f03-score-bar-container {
        flex: 1;
        height: 24px;
        background-color: #E5E7EB;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .f03-score-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 8px;
        color: white;
        font-size: 12px;
        font-weight: 600;
    }
    
    .f03-responses-section {
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .f03-responses-title { font-size: 16px; font-weight: 700; color: #1F2937; margin-bottom: 20px; }
    
    .f03-responses-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .f03-responses-table thead tr {
        background-color: #F9FAFB;
        border-bottom: 2px solid #E5E7EB;
    }
    
    .f03-responses-table th {
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }
    
    .f03-responses-table td {
        padding: 12px;
        border-bottom: 1px solid #E5E7EB;
        font-size: 14px;
        color: #374151;
    }
    
    .f03-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .f03-badge-success {
        background-color: #DCFCE7;
        color: #166534;
    }
    
    .f03-badge-warning {
        background-color: #FEF3C7;
        color: #92400E;
    }
    
    .f03-badge-danger {
        background-color: #FEE2E2;
        color: #991B1B;
    }
    
    .f03-action-btn {
        background: none;
        border: none;
        color: #3B82F6;
        cursor: pointer;
        font-size: 14px;
        transition: color 0.2s;
    }
    
    .f03-action-btn:hover { color: #2563EB; }
    
    .f03-empty-state {
        text-align: center;
        padding: 40px;
        color: #9CA3AF;
    }
    
    @media (max-width: 768px) {
        .f03-token-content { grid-template-columns: 1fr; }
        .f03-stats-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="f03-dashboard">
    <div class="f03-dashboard-header">
        <h1 class="f03-dashboard-title">Dashboard F03 - {{ $upp->nama ?? 'UPP' }}</h1>
        <p class="f03-dashboard-subtitle">Periode: {{ $periode->nama ?? '-' }} ({{ $periode->tahun ?? '-' }})</p>
    </div>

    <!-- Stats Grid -->
    <div class="f03-stats-grid">
        <div class="f03-stat-card">
            <div class="f03-stat-label">Total Respons</div>
            <div class="f03-stat-value">{{ $totalResponses ?? 0 }}</div>
            <div class="f03-stat-subtitle">dari {{ $token->allow_multiple_responses ? '∞' : '1' }} yang diizinkan</div>
        </div>
        <div class="f03-stat-card">
            <div class="f03-stat-label">Respons Unik</div>
            <div class="f03-stat-value">{{ $uniqueResponses ?? 0 }}</div>
            <div class="f03-stat-subtitle">Responden berbeda</div>
        </div>
        <div class="f03-stat-card">
            <div class="f03-stat-label">Duplikat</div>
            <div class="f03-stat-value">{{ $duplicateResponses ?? 0 }}</div>
            <div class="f03-stat-subtitle">Respons dari device sama</div>
        </div>
        <div class="f03-stat-card">
            <div class="f03-stat-label">Skor Rata-rata</div>
            <div class="f03-stat-value">{{ number_format($averageScore ?? 0, 2) }}</div>
            <div class="f03-stat-subtitle">Dari skala 1-5</div>
        </div>
    </div>

    <!-- Token Section -->
    <div class="f03-token-section">
        <div class="f03-token-title">Token & URL Publikasi</div>
        <div class="f03-token-content">
            <div class="f03-token-info">
                <div class="f03-token-row">
                    <label>Token:</label>
                    <span class="value">{{ $token->token }}</span>
                </div>
                <div class="f03-token-row">
                    <label>URL:</label>
                </div>
                <input type="text" value="{{ route('f03.public.form', ['token' => $token->token]) }}" readonly style="width: 100%; padding: 8px; border: 1px solid #D1D5DB; border-radius: 4px; font-size: 12px; color: #667eea;">
                <button class="f03-copy-btn" onclick="copyToClipboard(this)">Salin URL</button>
                <div class="f03-token-row">
                    <label>Status:</label>
                    <span class="value">{{ $token->isExpired() ? '❌ Kadaluarsa' : '✅ Aktif' }}</span>
                </div>
                @if($token->expired_date)
                <div class="f03-token-row">
                    <label>Berakhir:</label>
                    <span class="value">{{ $token->expired_date->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
            <div class="f03-qr-section">
                <div class="f03-qr-title">QR Code untuk Akses</div>
                <div id="qrCodeContainer" style="display: flex; justify-content: center; min-height: 220px; align-items: center;">
                    @if($token->qr_code)
                        {!! $token->qr_code !!}
                    @else
                        <p style="color: #9CA3AF; font-size: 13px;">Membuat QR Code...</p>
                    @endif
                </div>
                <div style="display: flex; justify-content: center; margin-top: 16px;">
                    <button id="downloadQrBtn" onclick="downloadQrCode()" style="background-color: #3B82F6; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; display: none;">
                        📥 Download QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scores Section -->
    @if($aspectScores && count($aspectScores) > 0)
    <div class="f03-scores-section">
        <div class="f03-scores-title">Skor Per Aspek</div>
        <div class="f03-score-bars">
            @foreach($aspectScores as $aspek => $score)
            <div class="f03-score-item">
                <div class="f03-score-label">{{ $aspek }}</div>
                <div class="f03-score-bar-container">
                    <div class="f03-score-bar-fill" style="width: {{ ($score / 5) * 100 }}%;">
                        {{ number_format($score, 2) }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Responses List -->
    <div class="f03-responses-section">
        <div class="f03-responses-title">Daftar Respons Terakhir</div>
        @if($responses && count($responses) > 0)
        <table class="f03-responses-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Skor</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($responses as $response)
                <tr>
                    <td>{{ $response->response_date->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ number_format($response->getAverageScoreAttribute(), 2) }}</strong></td>
                    <td>
                        @if($response->is_duplicate)
                        <span class="f03-badge f03-badge-warning">Duplikat</span>
                        @else
                        <span class="f03-badge f03-badge-success">Unik</span>
                        @endif
                    </td>
                    <td>
                        <button class="f03-action-btn" onclick="viewDetail({{ $response->id }})">Lihat Detail</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="f03-empty-state">
            <p>Belum ada respons</p>
        </div>
        @endif
    </div>
</div>

<script>
    function copyToClipboard(btn) {
        const input = btn.previousElementSibling;
        input.select();
        document.execCommand('copy');
        
        btn.textContent = 'Tersalin!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.textContent = 'Salin URL';
            btn.classList.remove('copied');
        }, 2000);
    }

    function viewDetail(responseId) {
        fetch(`{{ url('/f03/api/response') }}/${responseId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            let html = `
                <div style="max-height: 600px; overflow-y: auto; padding: 20px;">
                    <h3 style="margin-bottom: 20px; color: #1F2937; font-size: 18px; font-weight: 700;">Detail Respons</h3>
                    <p style="color: #6B7280; font-size: 13px; margin-bottom: 20px;">
                        <strong>Tanggal:</strong> ${new Date(data.pengisian.response_date).toLocaleString('id-ID')}
                    </p>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #F9FAFB; border-bottom: 2px solid #E5E7EB;">
                                <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151;">Aspek</th>
                                <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151;">Pertanyaan</th>
                                <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #374151;">Respons</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.jawaban.forEach(j => {
                html += `
                    <tr style="border-bottom: 1px solid #E5E7EB;">
                        <td style="padding: 12px; font-size: 13px; color: #374151;">${j.aspek}</td>
                        <td style="padding: 12px; font-size: 13px; color: #374151;">${j.indikator}</td>
                        <td style="padding: 12px; font-size: 13px; color: #374151; font-weight: 600;">${j.response_value || '-'}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            // Show modal
            const modal = document.createElement('div');
            modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;';
            modal.innerHTML = `
                <div style="background: white; border-radius: 8px; width: 90%; max-width: 900px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #E5E7EB;">
                        <h2 style="margin: 0; color: #1F2937; font-size: 18px; font-weight: 700;">Detail Respons</h2>
                        <button onclick="this.closest('[style*=position: fixed]').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6B7280;">×</button>
                    </div>
                    ${html}
                </div>
            `;
            document.body.appendChild(modal);
            modal.addEventListener('click', function(e) {
                if (e.target === this) this.remove();
            });
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Gagal memuat detail respons: ' + err.message);
        });
    }

    // QR Code generation polling
    document.addEventListener('DOMContentLoaded', function() {
        const tokenId = {{ $token->id }};
        const container = document.getElementById('qrCodeContainer');
        
        // Check if QR code is not yet generated
        const hasQr = container.querySelector('svg');
        if (hasQr) {
            // QR already exists, show download button
            document.getElementById('downloadQrBtn').style.display = 'inline-block';
        } else {
            generateQrCode(tokenId);
        }
    });

    function generateQrCode(tokenId) {
        const container = document.getElementById('qrCodeContainer');
        fetch(`{{ url('/f03/api/qr-code') }}/${tokenId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.qr_code) {
                // QR generated, display it
                container.innerHTML = data.qr_code;
                // Show download button
                document.getElementById('downloadQrBtn').style.display = 'inline-block';
            } else {
                // Not ready yet, retry in 500ms
                console.log('QR not ready, retrying...');
                setTimeout(() => generateQrCode(tokenId), 500);
            }
        })
        .catch(err => {
            console.error('QR generation error:', err);
            container.innerHTML = '<p style="color: #ef4444; font-size: 13px;">Gagal membuat QR Code</p>';
        });
    }

    function downloadQrCode() {
        const container = document.getElementById('qrCodeContainer');
        const svg = container.querySelector('svg');
        
        if (!svg) {
            alert('QR Code tidak tersedia');
            return;
        }
        
        // Convert SVG to canvas and download as PNG
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const svgData = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(svgBlob);
        
        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            URL.revokeObjectURL(url);
            
            // Download as PNG
            canvas.toBlob(blob => {
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `qr-code-${new Date().getTime()}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);
            });
        };
        
        img.src = url;
    }
</script>

@endsection
