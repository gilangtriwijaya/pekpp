@extends('layouts.app')
@section('title','F03 Admin Dashboard')
@section('content')

<style>
    .f03-admin-dashboard { padding: 24px 20px; max-width: 1400px; margin: 0 auto; }
    .f03-admin-header { margin-bottom: 30px; }
    .f03-admin-title { font-size: 28px; font-weight: 700; color: #1F2937; margin-bottom: 10px; }
    
    .f03-filter-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    
    .f03-filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .f03-filter-label { font-size: 13px; font-weight: 600; color: #374151; }
    .f03-filter-input {
        padding: 8px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
        min-width: 150px;
    }
    .f03-filter-input:focus { outline: none; border-color: #667eea; }
    
    .f03-filter-btn {
        background-color: #667eea;
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .f03-filter-btn:hover { background-color: #5568d3; }
    
    .f03-global-stats {
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
    
    .f03-rankings-section {
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .f03-rankings-title { font-size: 18px; font-weight: 700; color: #1F2937; margin-bottom: 24px; }
    
    .f03-ranking-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .f03-ranking-item {
        display: flex;
        align-items: center;
        padding: 16px;
        background-color: #F9FAFB;
        border-radius: 6px;
        border-left: 4px solid #E5E7EB;
        transition: all 0.2s;
    }
    
    .f03-ranking-item:hover {
        background-color: #F3F4F6;
        border-left-color: #667eea;
    }
    
    .f03-ranking-position {
        font-size: 24px;
        font-weight: 700;
        color: #667eea;
        min-width: 50px;
        text-align: center;
    }
    
    .f03-ranking-info {
        flex: 1;
        margin-left: 20px;
    }
    
    .f03-ranking-name {
        font-size: 15px;
        font-weight: 600;
        color: #1F2937;
    }
    
    .f03-ranking-meta {
        font-size: 13px;
        color: #6B7280;
        margin-top: 4px;
    }
    
    .f03-ranking-score {
        font-size: 20px;
        font-weight: 700;
        color: #667eea;
        min-width: 100px;
        text-align: right;
    }
    
    .f03-ranking-bar {
        width: 100%;
        height: 6px;
        background-color: #E5E7EB;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 8px;
    }
    
    .f03-ranking-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    .f03-medal {
        font-size: 20px;
        margin-right: 8px;
    }
    
    .f03-ranking-target {
        display: inline-block;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
        margin-top: 4px;
    }
    
    .f03-target-met { background-color: #DCFCE7; color: #166534; }
    .f03-target-unmet { background-color: #FEE2E2; color: #991B1B; }
    
    .f03-empty-state {
        text-align: center;
        padding: 40px;
        color: #9CA3AF;
    }

    /* Clickable stat card */
    .f03-stat-card-clickable {
        cursor: pointer;
        transition: transform 0.18s, box-shadow 0.18s;
    }
    .f03-stat-card-clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.13);
    }
    .f03-stat-card-clickable .f03-stat-click-hint {
        font-size: 11px;
        margin-top: 6px;
        opacity: 0.7;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Modal overlay */
    .f03-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .f03-modal-overlay.active { display: flex; }

    .f03-modal-box {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        width: 100%;
        max-width: 720px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        animation: f03ModalIn 0.22s ease;
    }
    @keyframes f03ModalIn {
        from { opacity: 0; transform: scale(0.93) translateY(16px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .f03-modal-header {
        padding: 20px 24px 16px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .f03-modal-title { font-size: 18px; font-weight: 700; color: #1F2937; }
    .f03-modal-subtitle { font-size: 13px; color: #6B7280; margin-top: 3px; }
    .f03-modal-close {
        background: none; border: none; cursor: pointer;
        color: #9CA3AF; font-size: 22px; line-height: 1;
        padding: 2px 6px; border-radius: 4px;
        transition: color 0.15s, background 0.15s;
    }
    .f03-modal-close:hover { color: #1F2937; background: #F3F4F6; }

    .f03-modal-summary {
        padding: 16px 24px;
        border-bottom: 1px solid #F3F4F6;
        display: flex;
        gap: 24px;
    }
    .f03-modal-stat {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .f03-modal-stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #9CA3AF; }
    .f03-modal-stat-value { font-size: 22px; font-weight: 700; color: #1F2937; }

    .f03-modal-body {
        overflow-y: auto;
        padding: 16px 24px 24px;
        flex: 1;
    }

    .f03-modal-list { display: flex; flex-direction: column; gap: 10px; }
    .f03-modal-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        background: #F9FAFB;
        transition: background 0.15s;
    }
    .f03-modal-item:hover { background: #F3F4F6; }
    .f03-modal-item-rank {
        font-size: 13px;
        font-weight: 700;
        color: #667eea;
        min-width: 28px;
        text-align: center;
    }
    .f03-modal-item-info { flex: 1; }
    .f03-modal-item-name { font-size: 14px; font-weight: 600; color: #1F2937; }
    .f03-modal-item-meta { font-size: 12px; color: #6B7280; margin-top: 2px; }
    .f03-modal-item-score {
        font-size: 15px;
        font-weight: 700;
        min-width: 70px;
        text-align: right;
    }
    .score-met { color: #10B981; }
    .score-notmet { color: #EF4444; }

    @media (max-width: 768px) {
        .f03-filter-section { flex-direction: column; align-items: stretch; }
        .f03-filter-input { width: 100%; }
        .f03-ranking-score { display: none; }
        .f03-ranking-item { flex-wrap: wrap; }
        .f03-modal-summary { flex-wrap: wrap; gap: 14px; }
    }
</style>

<div class="f03-admin-dashboard">
    <div class="f03-admin-header">
        <h1 class="f03-admin-title">F03 Admin Dashboard - Ranking UPP</h1>
    </div>

    <!-- Filter Section -->
    <div class="f03-filter-section">
        <div class="f03-filter-group">
            <label class="f03-filter-label">Periode</label>
            <select id="periodeFilter" class="f03-filter-input" onchange="filterRankings()">
                @foreach($periodes as $p)
                <option value="{{ $p->id }}" {{ (string)$p->id === (string)$periodeId ? 'selected' : '' }}>
                    {{ $p->nama }} ({{ $p->tahun }}){{ $p->is_aktif ? ' ★ Aktif' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <button class="f03-filter-btn" onclick="filterRankings()">Terapkan Filter</button>
    </div>

    <!-- Global Stats -->
    <div class="f03-global-stats">
        <div class="f03-stat-card">
            <div class="f03-stat-label">Total UPP</div>
            <div class="f03-stat-value">{{ $totalUpps ?? 0 }}</div>
        </div>
        <div class="f03-stat-card">
            <div class="f03-stat-label">Total Respons</div>
            <div class="f03-stat-value">{{ $totalResponses ?? 0 }}</div>
        </div>
        <div class="f03-stat-card">
            <div class="f03-stat-label">Rata-rata Skor</div>
            <div class="f03-stat-value">{{ number_format($averageScore ?? 0, 2) }}</div>
        </div>
        <div class="f03-stat-card f03-stat-card-clickable" style="border-left-color: #10B981;" onclick="openModal('met')" title="Klik untuk lihat daftar UPP">
            <div class="f03-stat-label">Memenuhi Standar</div>
            <div class="f03-stat-value">{{ $targetMetCount ?? 0 }} <span style="font-size: 14px; font-weight: normal; color: #6B7280;">UPP</span></div>
            <div class="f03-stat-subtitle" style="color: #10B981;">{{ $totalUpps > 0 ? number_format(($targetMetCount ?? 0) / ($totalUpps ?? 1) * 100, 1) : 0 }}% dari Total</div>
            <div class="f03-stat-click-hint" style="color:#10B981;">🔍 Klik untuk detail</div>
        </div>
        <div class="f03-stat-card f03-stat-card-clickable" style="border-left-color: #EF4444;" onclick="openModal('notmet')" title="Klik untuk lihat daftar UPP">
            <div class="f03-stat-label">Belum Memenuhi</div>
            <div class="f03-stat-value">{{ ($totalUpps ?? 0) - ($targetMetCount ?? 0) }} <span style="font-size: 14px; font-weight: normal; color: #6B7280;">UPP</span></div>
            <div class="f03-stat-subtitle" style="color: #EF4444;">{{ $totalUpps > 0 ? number_format((($totalUpps ?? 0) - ($targetMetCount ?? 0)) / ($totalUpps ?? 1) * 100, 1) : 0 }}% dari Total</div>
            <div class="f03-stat-click-hint" style="color:#EF4444;">🔍 Klik untuk detail</div>
        </div>
    </div>

    <!-- Rankings Section -->
    <div class="f03-rankings-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="f03-rankings-title" style="margin-bottom: 0;">Daftar Progres Pengisian F03</h2>
            <div style="display: flex; gap: 12px;">
                <button onclick="exportRankingsToCSV()" style="padding: 8px 16px; background-color: #10B981; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#059669'" onmouseout="this.style.backgroundColor='#10B981'">
                    📥 Export CSV
                </button>
                <button onclick="exportRankingsToJPG()" style="padding: 8px 16px; background-color: #3B82F6; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#2563EB'" onmouseout="this.style.backgroundColor='#3B82F6'">
                    📷 Export JPG (Paginated)
                </button>
                <button onclick="exportRankingsToPDF()" style="padding: 8px 16px; background-color: #F59E0B; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#D97706'" onmouseout="this.style.backgroundColor='#F59E0B'">
                    📄 Export PDF
                </button>
            </div>
        </div>
        
        @if($rankings && count($rankings) > 0)
        <div class="f03-ranking-list" id="rankingListForExport">
            @foreach($rankings as $index => $ranking)
            <div class="f03-ranking-item">
                <div class="f03-ranking-position">
                    <span class="f03-medal">
                        @if($index == 0)
                            🥇
                        @elseif($index == 1)
                            🥈
                        @elseif($index == 2)
                            🥉
                        @else
                            #{{ $index + 1 }}
                        @endif
                    </span>
                </div>
                <div class="f03-ranking-info">
                    <div class="f03-ranking-name">{{ $ranking['upp_nama'] ?? 'Unknown' }}</div>
                    <div class="f03-ranking-meta">
                        {{ $ranking['total_responses'] ?? 0 }} respons
                        @if(($ranking['target_responden'] ?? 0) > 0)
                            | Target: {{ $ranking['target_responden'] }}
                        @endif
                    </div>
                    @if(($ranking['target_responden'] ?? 0) > 0)
                        <span class="f03-ranking-target {{ ($ranking['total_responses'] ?? 0) >= ($ranking['target_responden'] ?? 0) ? 'f03-target-met' : 'f03-target-unmet' }}">
                            @if(($ranking['total_responses'] ?? 0) >= ($ranking['target_responden'] ?? 0))
                                ✓ Target Terpenuhi
                            @else
                                ✗ Target Belum Terpenuhi ({{ ($ranking['target_responden'] ?? 0) - ($ranking['total_responses'] ?? 0) }} kurang)
                            @endif
                        </span>
                    @endif
                    <div class="f03-ranking-bar">
                        <div class="f03-ranking-bar-fill" style="width: {{ ($ranking['target_responden'] ?? 0) > 0 ? (($ranking['total_responses'] ?? 0) /($ranking['target_responden'] ?? 1) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="f03-ranking-score">{{ number_format($ranking['average_score'] ?? 0, 2) }}/5.00</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="f03-empty-state">
            <p>Belum ada data ranking. Buat aspek dan indikator terlebih dahulu.</p>
        </div>
        @endif
    </div>
</div>

{{-- ===== MODAL: Memenuhi Standar ===== --}}
<div class="f03-modal-overlay" id="modal-met" onclick="closeModalOnBg(event, 'met')">
    <div class="f03-modal-box">
        <div class="f03-modal-header">
            <div>
                <div class="f03-modal-title">✅ UPP Memenuhi Standar</div>
                <div class="f03-modal-subtitle">Daftar UPP yang telah memenuhi target responden</div>
            </div>
            <button class="f03-modal-close" onclick="closeModal('met')" aria-label="Tutup">&times;</button>
        </div>
        <div class="f03-modal-summary">
            <div class="f03-modal-stat">
                <span class="f03-modal-stat-label">Jumlah UPP</span>
                <span class="f03-modal-stat-value" style="color:#10B981;">{{ count($targetMetUpps ?? []) }}</span>
            </div>
            <div class="f03-modal-stat">
                <span class="f03-modal-stat-label">Rata-rata Total Skor</span>
                <span class="f03-modal-stat-value" style="color:#667eea;">{{ number_format($avgScoreMet ?? 0, 2) }}<span style="font-size:13px;font-weight:400;color:#9CA3AF;">/5.00</span></span>
            </div>
        </div>
        <div class="f03-modal-body">
            @if(count($targetMetUpps ?? []) > 0)
            <div class="f03-modal-list">
                @foreach($targetMetUpps as $i => $item)
                <div class="f03-modal-item">
                    <div class="f03-modal-item-rank">#{{ $i + 1 }}</div>
                    <div class="f03-modal-item-info">
                        <div class="f03-modal-item-name">{{ $item['upp_nama'] }}</div>
                        <div class="f03-modal-item-meta">
                            {{ $item['total_responses'] }} respons
                            @if(($item['target_responden'] ?? 0) > 0)
                                &middot; Target: {{ $item['target_responden'] }}
                                &middot; <span style="color:#10B981;font-weight:600;">✓ Terpenuhi</span>
                            @endif
                        </div>
                    </div>
                    <div class="f03-modal-item-score score-met">{{ number_format($item['average_score'], 2) }}<span style="font-size:11px;font-weight:400;color:#9CA3AF;">/5.00</span></div>
                </div>
                @endforeach
            </div>
            @else
            <div class="f03-empty-state">Tidak ada UPP yang memenuhi standar.</div>
            @endif
        </div>
    </div>
</div>

{{-- ===== MODAL: Belum Memenuhi ===== --}}
<div class="f03-modal-overlay" id="modal-notmet" onclick="closeModalOnBg(event, 'notmet')">
    <div class="f03-modal-box">
        <div class="f03-modal-header">
            <div>
                <div class="f03-modal-title">❌ UPP Belum Memenuhi Standar</div>
                <div class="f03-modal-subtitle">Daftar UPP yang belum memenuhi target responden</div>
            </div>
            <button class="f03-modal-close" onclick="closeModal('notmet')" aria-label="Tutup">&times;</button>
        </div>
        <div class="f03-modal-summary">
            <div class="f03-modal-stat">
                <span class="f03-modal-stat-label">Jumlah UPP</span>
                <span class="f03-modal-stat-value" style="color:#EF4444;">{{ count($targetNotMetUpps ?? []) }}</span>
            </div>
            <div class="f03-modal-stat">
                <span class="f03-modal-stat-label">Rata-rata Total Skor</span>
                <span class="f03-modal-stat-value" style="color:#667eea;">{{ number_format($avgScoreNotMet ?? 0, 2) }}<span style="font-size:13px;font-weight:400;color:#9CA3AF;">/5.00</span></span>
            </div>
        </div>
        <div class="f03-modal-body">
            @if(count($targetNotMetUpps ?? []) > 0)
            <div class="f03-modal-list">
                @foreach($targetNotMetUpps as $i => $item)
                <div class="f03-modal-item">
                    <div class="f03-modal-item-rank" style="color:#EF4444;">#{{ $i + 1 }}</div>
                    <div class="f03-modal-item-info">
                        <div class="f03-modal-item-name">{{ $item['upp_nama'] }}</div>
                        <div class="f03-modal-item-meta">
                            {{ $item['total_responses'] }} respons
                            @if(($item['target_responden'] ?? 0) > 0)
                                &middot; Target: {{ $item['target_responden'] }}
                                &middot; <span style="color:#EF4444;font-weight:600;">✗ Kurang {{ $item['target_responden'] - $item['total_responses'] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="f03-modal-item-score score-notmet">{{ number_format($item['average_score'], 2) }}<span style="font-size:11px;font-weight:400;color:#9CA3AF;">/5.00</span></div>
                </div>
                @endforeach
            </div>
            @else
            <div class="f03-empty-state">Semua UPP telah memenuhi standar. 🎉</div>
            @endif
        </div>
    </div>
</div>

<script>
    function filterRankings() {
        const periodeId = document.getElementById('periodeFilter').value;
        const url = new URL(window.location.href);
        
        if (periodeId) {
            url.searchParams.set('periode_id', periodeId);
        } else {
            url.searchParams.delete('periode_id');
        }
        
        window.location.href = url.toString();
    }

    function openModal(type) {
        const overlay = document.getElementById('modal-' + type);
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(type) {
        const overlay = document.getElementById('modal-' + type);
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function closeModalOnBg(event, type) {
        if (event.target === document.getElementById('modal-' + type)) {
            closeModal(type);
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('met');
            closeModal('notmet');
        }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    function exportRankingsToCSV() {
        const rankings = @json($rankings ?? []);
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Add header
        csvContent += "Peringkat,Nama UPP,Total Respons,Target,Status Target,Skor Rata-rata\n";
        
        // Add data rows
        rankings.forEach((ranking, index) => {
            const position = index + 1;
            const uppNama = (ranking.upp_nama || 'Unknown').replace(/,/g, ' ');
            const totalResponses = ranking.total_responses || 0;
            const targetResponden = ranking.target_responden || 0;
            const status = totalResponses >= targetResponden ? 'Target Terpenuhi' : 'Target Belum Terpenuhi';
            const averageScore = (ranking.average_score || 0).toFixed(2);
            
            csvContent += `${position},"${uppNama}",${totalResponses},${targetResponden},"${status}",${averageScore}\n`;
        });
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `Peringkat_F03_Skor_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    function exportRankingsToJPG() {
        const element = document.getElementById('rankingListForExport');
        const title = document.querySelector('.f03-rankings-title');
        const rankings = @json($rankings ?? []);
        
        const itemsPerPage = 15;
        const totalPages = Math.ceil(rankings.length / itemsPerPage);
        let currentPage = 0;
        
        const exportStatus = document.createElement('div');
        exportStatus.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: #1F2937; color: white; padding: 16px 24px; border-radius: 8px; z-index: 9999; font-size: 14px; min-width: 300px;">
                <div style="margin-bottom: 8px;"><strong>📥 Mengunduh halaman JPG...</strong></div>
                <div id="exportProgress" style="margin-bottom: 8px;">Halaman 1 dari ${totalPages}</div>
                <div style="width: 100%; height: 4px; background: #374151; border-radius: 2px; overflow: hidden;">
                    <div id="progressBar" style="height: 100%; background: #3B82F6; width: 0%; transition: width 0.3s;"></div>
                </div>
            </div>
        `;
        document.body.appendChild(exportStatus);
        
        function generatePage() {
            if (currentPage >= totalPages) {
                exportStatus.remove();
                alert(`✅ Semua ${totalPages} halaman berhasil diunduh!`);
                return;
            }
            
            // Create container for this page
            const container = document.createElement('div');
            container.style.padding = '24px';
            container.style.backgroundColor = 'white';
            container.style.width = '900px';
            container.style.fontFamily = 'system-ui, -apple-system, sans-serif';
            
            // Add title
            const titleClone = title.cloneNode(true);
            titleClone.style.fontSize = '18px';
            titleClone.style.marginBottom = '12px';
            container.appendChild(titleClone);
            
            // Add page info
            const pageInfo = document.createElement('div');
            pageInfo.style.fontSize = '12px';
            pageInfo.style.color = '#6B7280';
            pageInfo.style.marginBottom = '16px';
            pageInfo.textContent = `Halaman ${currentPage + 1} dari ${totalPages}`;
            container.appendChild(pageInfo);
            
            // Add items for this page
            const startIdx = currentPage * itemsPerPage;
            const endIdx = Math.min(startIdx + itemsPerPage, rankings.length);
            const pageItems = rankings.slice(startIdx, endIdx);
            
            // Create ranking list for this page
            const rankingList = document.createElement('div');
            rankingList.style.display = 'flex';
            rankingList.style.flexDirection = 'column';
            rankingList.style.gap = '10px';
            
            pageItems.forEach((ranking, idx) => {
                const position = startIdx + idx + 1;
                const medal = position === 1 ? '🥇' : position === 2 ? '🥈' : position === 3 ? '🥉' : `#${position}`;
                const totalResponses = ranking.total_responses || 0;
                const targetResponden = ranking.target_responden || 0;
                const status = totalResponses >= targetResponden ? '✓ Terpenuhi' : '✗ Belum';
                const averageScore = (ranking.average_score || 0).toFixed(2);
                
                const item = document.createElement('div');
                item.style.border = '1px solid #E5E7EB';
                item.style.borderRadius = '6px';
                item.style.padding = '10px 12px';
                item.style.backgroundColor = '#FAFAFA';
                item.style.display = 'flex';
                item.style.gap = '12px';
                item.style.alignItems = 'center';
                item.style.fontSize = '12px';
                
                item.innerHTML = `
                    <div style="min-width: 30px; text-align: center; font-weight: bold; font-size: 14px;">${medal}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 3px;">${ranking.upp_nama || 'Unknown'}</div>
                        <div style="color: #6B7280; font-size: 11px;">${totalResponses} respons (Target: ${targetResponden}) | ${status}</div>
                    </div>
                    <div style="text-align: right; font-weight: 600; color: #3B82F6;">${averageScore}/5.00</div>
                `;
                rankingList.appendChild(item);
            });
            
            container.appendChild(rankingList);
            document.body.appendChild(container);
            
            // Generate image
            html2canvas(container, {
                scale: 2,
                backgroundColor: '#ffffff',
                allowTaint: true,
                useCORS: true,
                logging: false
            }).then(canvas => {
                document.body.removeChild(container);
                
                // Download
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/jpeg', 0.92);
                link.download = `Peringkat_F03_Skor_hal${currentPage + 1}_${new Date().toISOString().slice(0, 10)}.jpg`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Update progress
                currentPage++;
                const progress = (currentPage / totalPages) * 100;
                document.getElementById('progressBar').style.width = progress + '%';
                document.getElementById('exportProgress').textContent = `Halaman ${currentPage} dari ${totalPages}`;
                
                // Next page with delay
                setTimeout(generatePage, 800);
            }).catch(error => {
                console.error('Error generating image:', error);
                document.body.removeChild(container);
                exportStatus.remove();
                alert('❌ Gagal mengekspor gambar. Silakan coba lagi.');
            });
        }
        
        generatePage();
    }
    
    function exportRankingsToPDF() {
        const rankings = @json($rankings ?? []);
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });
        
        const exportStatus = document.createElement('div');
        exportStatus.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: #1F2937; color: white; padding: 16px 24px; border-radius: 8px; z-index: 9999; font-size: 14px; min-width: 300px;">
                <div style="margin-bottom: 8px;"><strong>📄 Membuat file PDF...</strong></div>
                <div id="pdfProgress">Memproses data...</div>
            </div>
        `;
        document.body.appendChild(exportStatus);
        
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
        const margin = 10;
        let yPosition = margin;
        
        // Define column positions once (valid for all pages)
        const colX = [10, 18, 148, 163, 178];
        const colW = [8, 130, 15, 15, 15];
        
        // Title
        doc.setFontSize(16);
        doc.setFont(undefined, 'bold');
        doc.text('Progres Pengisian F03', margin, yPosition);
        yPosition += 8;
        
        // Date
        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        doc.text(`Tanggal: ${new Date().toLocaleDateString('id-ID')}`, margin, yPosition);
        yPosition += 10;
        
        // Table header - First page
        doc.setFontSize(7);
        doc.setFont(undefined, 'bold');
        doc.setFillColor(59, 130, 246);
        doc.setTextColor(255, 255, 255);
        const headerRowY = yPosition;
        
        for (let i = 0; i < colW.length; i++) {
            doc.rect(colX[i], headerRowY, colW[i], 8, 'F');
        }
        
        doc.text('No', 14, headerRowY + 5);
        doc.text('Unit Pelayanan Publik', 83, headerRowY + 5, { align: 'center' });
        doc.text('Total Isian', 155.5, headerRowY + 5, { align: 'center' });
        doc.text('Minimal Isian', 170.5, headerRowY + 5, { align: 'center' });
        doc.text('Total Skor', 185.5, headerRowY + 5, { align: 'center' });
        
        yPosition += 14;
        
        // Table rows
        doc.setTextColor(0, 0, 0);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(8);
        
        rankings.forEach((ranking, index) => {
            const position = index + 1;
            const totalResponses = ranking.total_responses || 0;
            const targetResponden = ranking.target_responden || 0;
            const averageScore = (ranking.average_score || 0).toFixed(2);
            
            // Check if we need new page
            if (yPosition > pageHeight - 15) {
                doc.addPage();
                yPosition = margin + 10;
                
                // Add header on new page
                doc.setFillColor(59, 130, 246);
                doc.setTextColor(255, 255, 255);
                doc.setFont(undefined, 'bold');
                doc.setFontSize(7);
                
                for (let i = 0; i < colW.length; i++) {
                    doc.rect(colX[i], yPosition - 8, colW[i], 8, 'F');
                }
                
                doc.text('No', 14, yPosition - 3);
                doc.text('Unit Pelayanan Publik', 83, yPosition - 3, { align: 'center' });
                doc.text('Total Isian', 155.5, yPosition - 3, { align: 'center' });
                doc.text('Minimal Isian', 170.5, yPosition - 3, { align: 'center' });
                doc.text('Total Skor', 185.5, yPosition - 3, { align: 'center' });
                
                yPosition += 4;
                doc.setTextColor(0, 0, 0);
                doc.setFont(undefined, 'normal');
                doc.setFontSize(8);
            }
            
            // Render row data
            doc.text(position.toString(), 14, yPosition, { align: 'center' });
            doc.text(ranking.upp_nama || 'Unknown', 19, yPosition);
            doc.text(totalResponses.toString(), 155.5, yPosition, { align: 'center' });
            doc.text(targetResponden.toString(), 170.5, yPosition, { align: 'center' });
            doc.text(averageScore, 185.5, yPosition, { align: 'center' });
            
            yPosition += 5;
        });
        
        // Footer
        doc.setFontSize(8);
        doc.setTextColor(100, 100, 100);
        const pageCount = doc.internal.pages.length - 1;
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.text(
                `Halaman ${i} dari ${pageCount}`,
                pageWidth / 2,
                pageHeight - 8,
                { align: 'center' }
            );
        }
        
        // Save PDF
        doc.save(`Progres_Pengisian_F03_${new Date().toISOString().slice(0, 10)}.pdf`);
        exportStatus.remove();
        alert(`✅ File PDF berhasil diunduh!`);
    }
</script>

@endsection

