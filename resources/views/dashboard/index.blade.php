@extends('layouts.app')

@section('title','PEKPP — Dashboard Utama')

@section('content')

<style>
    :root {
        --primary: #4F46E5;
        --success: #10B981;
        --warning: #F59E0B;
        --danger: #EF4444;
        --info: #3B82F6;
    }

    .dash-main {
        min-height: 100vh;
        padding: 15px 0;
        overflow-x: hidden;
    }

    .container {
        width: 100%;
        margin: 0;
        padding: 0 20px 0 15px;
        overflow-x: hidden;
    }

    .dash-header {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }

    .dash-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .dash-header p {
        color: #6B7280;
        font-size: 0.95rem;
        margin: 0;
    }



    /* Filter Section */
    #uppSelect {
        border-radius: 8px;
        padding: 12px;
        font-size: 0.95rem;
        background-color: white;
        transition: all 0.3s;
    }

    #uppSelect:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    #uppSelect option {
        padding: 8px 12px;
        line-height: 1.5;
    }

    /* Action Buttons Section */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
        padding: 15px;
        background: white;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .action-buttons-left {
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-filter {
        background-color: #f3f4f6;
        color: #6B7280;
        border: 1px solid #d1d5db;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    .btn-filter:hover {
        background-color: #e5e7eb;
        border-color: #9ca3af;
        color: #374151;
    }

    .action-info {
        font-size: 0.875rem;
        color: #6B7280;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 0;
    }

    @media (min-width: 640px) {
        .action-buttons {
            flex-direction: row;
            align-items: center;
            gap: 12px;
        }

        .action-buttons-left {
            flex-direction: row;
            gap: 12px;
        }

        .btn-filter {
            width: auto;
            flex-shrink: 0;
        }

        .action-info {
            margin-left: auto;
            justify-content: flex-end;
            padding: 0;
        }
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        justify-content: center;
        align-items: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-dialog {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 24px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .modal-close-btn {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close-btn:hover {
        opacity: 0.8;
    }

    .modal-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .form-check {
        padding: 12px 15px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-check:last-child {
        border-bottom: none;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4F46E5;
    }

    .form-check-label {
        cursor: pointer;
        margin: 0;
        color: #374151;
        user-select: none;
        flex: 1;
    }

    .select-all-check {
        padding: 12px 15px;
        background: #f0f4ff;
        border-radius: 8px;
        border: 1px solid #dbeafe;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .select-all-check .form-check-label {
        font-weight: 600;
        color: #1F2937;
    }

    .btn-modal {
        padding: 10px 24px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-cancel {
        background: white;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-modal-cancel:hover {
        background: #f3f4f6;
    }

    .btn-modal-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-modal-submit:hover {
        opacity: 0.9;
    }

    /* Export Buttons */
    .export-section {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .btn-export {
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-export-pdf {
        background: #2563EB;
        color: white;
    }

    .btn-export-pdf:hover {
        background: #1d4ed8;
    }

    .btn-export-csv {
        background: #60a5fa;
        color: white;
    }

    .btn-export-csv:hover {
        background: #3b82f6;
    }

    /* Summary Stats */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 35px;
        padding: 25px;
        background: white;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        border-left: 4px solid;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
        min-width: 0;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .stat-card.primary {
        border-left-color: #4F46E5;
    }

    .stat-card.success {
        border-left-color: #10B981;
    }

    .stat-card.warning {
        border-left-color: #F59E0B;
    }

    .stat-card.danger {
        border-left-color: #EF4444;
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6B7280;
        margin-bottom: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1F2937;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-unit {
        font-size: 0.85rem;
        color: #9CA3AF;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Charts */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 25px;
        margin-bottom: 35px;
        padding: 25px;
        background: white;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .chart-card {
        background: #f9fafb;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 15px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1F2937;
        min-width: 0;
        flex-shrink: 1;
    }

    .chart-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-chart-export {
        padding: 6px 12px;
        font-size: 0.8rem;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-chart-export:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .chart-container {
        position: relative;
        height: 400px;
        margin-bottom: 10px;
        overflow: hidden;
        width: 100%;
    }

    /* Data Table */
    .table-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        overflow-x: hidden;
    }

    .table-card h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
        table-layout: fixed;
    }

    .data-table thead th {
        background: #f9fafb;
        padding: 14px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
        word-break: break-word;
    }

    .data-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #f3f4f6;
        color: #4b5563;
        word-break: break-word;
    }

    .data-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-primary {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .badge-success {
        background: #DCFCE7;
        color: #166534;
    }

    .badge-warning {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-info {
        background: #E0F2FE;
        color: #0C4A6E;
    }

    .badge-secondary {
        background: #E5E7EB;
        color: #374151;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6B7280;
    }

    .empty-state i {
        font-size: 3.5rem;
        color: #D1D5DB;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }

        .filter-group {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dash-main {
            padding: 10px 0;
        }

        .container {
            padding: 0 10px 0 15px;
        }

        .dash-header {
            padding: 15px;
            border-radius: 8px;
        }

        .dash-header h1 {
            font-size: 1.5rem;
        }

        .charts-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .stats-container {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .stat-card {
            padding: 20px;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .stat-value {
            font-size: 2rem;
        }

        .stat-unit {
            font-size: 0.75rem;
        }

        .export-section {
            justify-content: stretch;
        }

        .btn-export {
            flex: 1;
            justify-content: center;
        }

        .filter-section {
            padding: 20px;
        }

        .data-table {
            font-size: 0.8rem;
        }

        .data-table thead th,
        .data-table tbody td {
            padding: 8px 6px;
        }

        .badge {
            padding: 4px 8px;
            font-size: 0.75rem;
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .chart-card {
            padding: 20px;
        }

        .table-card {
            padding: 20px;
        }

        .table-card h5 {
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
    }
</style>

<div class="dash-main">
    <div class="container">
        {{-- Header --}}
        <div class="dash-header">
            <h1><i class="fas fa-chart-line"></i> Dashboard Evaluasi Kinerja</h1>
            <p>Pemantauan & Evaluasi Kinerja Penyelenggaraan Pelayanan Publik — Periode <strong>{{ $periode->tahun }}</strong></p>
        </div>

        {{-- Main Content Sections --}}
            {{-- Error Messages --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <strong>Error:</strong> {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($error))
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-info-circle"></i> {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Action Buttons Section --}}
            @if(!empty($dashboardData['upps']) || ($isGlobalUser && $availableUpps->count() > 0))
                <div class="action-buttons">
                    <div class="action-buttons-left">
                        {{-- Filter Button - For Global Users with Multiple UPPs Selection --}}
                        @if($isGlobalUser && $availableUpps->count() > 0 && !empty($dashboardData['upps']))
                            <form method="GET" id="filterForm" style="display: none;">
                                <select name="upp_ids[]" id="uppSelect" multiple required>
                                    @foreach($availableUpps as $upp)
                                        <option value="{{ $upp->id }}"
                                            {{ in_array($upp->id, $selectedUppIds) ? 'selected' : '' }}>
                                            {{ $upp->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <button type="button" class="btn-filter" id="openUppModal">
                                <i class="fas fa-search"></i> Filter UPP
                            </button>
                        @endif

                        {{-- Download Button --}}
                        @if(!empty($dashboardData['upps']))
                            <button type="button" class="btn-export btn-export-pdf" id="exportDashboardBtn">
                                <i class="fas fa-download"></i> Download Semua Laporan
                            </button>
                        @endif
                    </div>

                    {{-- Info Display --}}
                    @if(!empty($dashboardData['upps']))
                        <div class="action-info">
                            @if($isGlobalUser && $availableUpps->count() > 0)
                                <span id="displayInfoText">
                                    @if(count($selectedUppIds) == $availableUpps->count())
                                        Menampilkan semua {{ count($selectedUppIds) }} UPP
                                    @else
                                        Menampilkan {{ count($selectedUppIds) }} dari {{ $availableUpps->count() }} UPP
                                    @endif
                                </span>
                            @else
                                <span>Menampilkan {{ count($dashboardData['upps']) }} UPP</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

                {{-- Data Available --}}
                @if(!empty($dashboardData['upps']))
                <div class="stats-container">
                    @if($isGlobalUser)
                        <div class="stat-card primary">
                            <div class="stat-label"><i class="fas fa-building"></i> Total Unit Pelayanan</div>
                            <div class="stat-value">{{ $dashboardData['summary']['total_upp'] }}</div>
                        </div>
                        <div class="stat-card warning">
                            <div class="stat-label"><i class="fas fa-file-alt"></i> Nilai F02</div>
                            <div class="stat-value">{{ number_format($dashboardData['summary']['avg_f02'], 1) }}</div>
                            <div class="stat-unit">Dokumentasi (0-100)</div>
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); font-size: 0.85rem; color: #6B7280;">
                                <strong>Kontribusi IPP (75%):</strong> {{ number_format($dashboardData['summary']['f02_ipp_contribution'], 1) }}
                            </div>
                        </div>
                        <div class="stat-card info">
                            <div class="stat-label"><i class="fas fa-users"></i> Nilai F03 </div>
                            <div class="stat-value">{{ number_format($dashboardData['summary']['avg_f03'], 2) }}</div>
                            <div class="stat-unit">Survey Kepuasan (1-5)</div>
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); font-size: 0.85rem; color: #6B7280;">
                                <strong>Kontribusi IPP (25%):</strong> {{ number_format($dashboardData['summary']['f03_ipp_contribution'], 2) }}
                            </div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-label"><i class="fas fa-star"></i> Indeks Pelayanan Publik</div>
                            <div class="stat-value">{{ number_format($dashboardData['summary']['avg_indeks'], 2) }}</div>
                            <div class="stat-unit" id="predicateSummary">Nilai Keseluruhan</div>
                        </div>
                        <div class="stat-card success" style="border-left: 4px solid #10B981;">
                            <div class="stat-label"><i class="fas fa-check-circle"></i> UPP Baik</div>
                            <div class="stat-value">{{ $dashboardData['summary']['upp_baik_count'] }}</div>
                            <div class="stat-unit">Status evaluasi baik (B- ke atas)</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%); border-left: 4px solid #EF4444;">
                            <div class="stat-label"><i class="fas fa-exclamation-triangle"></i> UPP Perlu Pembinaan</div>
                            <div class="stat-value" style="color: #DC2626;">{{ $dashboardData['summary']['upp_pembinaan_count'] }}</div>
                            <div class="stat-unit">Perlu perhatian khusus</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%); border-left: 4px solid #3B82F6;">
                            <div class="stat-label"><i class="fas fa-paper-plane"></i> Sudah Submit</div>
                            <div class="stat-value" style="color: #2563EB;">{{ $dashboardData['summary']['total_submitted'] }}</div>
                            <div class="stat-unit">Pengisian F01 terkirim</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(234, 88, 12, 0.1) 100%); border-left: 4px solid #F97316;">
                            <div class="stat-label"><i class="fas fa-hourglass-half"></i> Belum Validasi</div>
                            <div class="stat-value" style="color: #EA580C;">{{ $dashboardData['summary']['total_pending_validation'] }}</div>
                            <div class="stat-unit">Menunggu validasi F02</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(22, 163, 74, 0.1) 100%); border-left: 4px solid #22C55E;">
                            <div class="stat-label"><i class="fas fa-check-double"></i> Sudah Selesai</div>
                            <div class="stat-value" style="color: #16A34A;">{{ $dashboardData['summary']['total_validated'] }}</div>
                            <div class="stat-unit">Validasi F02 selesai</div>
                        </div>
                    @else
                        <div class="stat-card warning">
                            <div class="stat-label"><i class="fas fa-file-alt"></i> Nilai F02</div>
                            <div class="stat-value">{{ number_format($dashboardData['summary']['avg_f02'], 1) }}</div>
                            <div class="stat-unit">Dokumentasi (0-100)</div>
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); font-size: 0.85rem; color: #6B7280;">
                                <strong>Kontribusi IPP (75%):</strong> {{ number_format($dashboardData['summary']['f02_ipp_contribution'], 1) }}
                            </div>
                        </div>
                        <div class="stat-card info">
                            <div class="stat-label"><i class="fas fa-users"></i> Nilai F03 </div>
                            <div class="stat-value">{{ number_format($dashboardData['summary']['avg_f03'], 2) }}</div>
                            <div class="stat-unit">Survey Kepuasan (1-5)</div>
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); font-size: 0.85rem; color: #6B7280;">
                                <strong>Kontribusi IPP (25%):</strong> {{ number_format($dashboardData['summary']['f03_ipp_contribution'], 2) }}
                            </div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-label"><i class="fas fa-star"></i> Indeks Pelayanan Publik</div>
                            <div class="stat-value">{{ number_format($dashboardData['summary']['avg_indeks'], 2) }}</div>
                            <div class="stat-unit" id="predicateSummary">Nilai Keseluruhan</div>
                        </div>
                    @endif
                </div>

                {{-- Charts --}}
                <div class="charts-grid">
                    {{-- Comparison Chart --}}
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title"><i class="fas fa-chart-bar"></i> Perbandingan F02 & F03</div>
                            <div class="chart-actions">
                                @if($isGlobalUser && $availableUpps->count() > 0)
                                    <button type="button" class="btn-chart-export" onclick="openChartFilter('modalFilterComparison')">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                @endif
                                <button type="button" class="btn-chart-export" onclick="downloadChartImage('comparisonChart', 'F02_F03')">
                                    <i class="fas fa-image"></i> JPG
                                </button>
                                <button type="button" class="btn-chart-export" onclick="downloadChartCsv('comparison')">
                                    <i class="fas fa-file-csv"></i> CSV
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>

                    {{-- Indeks Chart --}}
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title"><i class="fas fa-chart-line"></i> Indeks Pelayanan Publik</div>
                            <div class="chart-actions">
                                @if($isGlobalUser && $availableUpps->count() > 0)
                                    <button type="button" class="btn-chart-export" onclick="openChartFilter('modalFilterIndeks')">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                @endif
                                <button type="button" class="btn-chart-export" onclick="downloadChartImage('indeksChart', 'Indeks_Pelayanan')">
                                    <i class="fas fa-image"></i> JPG
                                </button>
                                <button type="button" class="btn-chart-export" onclick="downloadChartCsv('indeks')">
                                    <i class="fas fa-file-csv"></i> CSV
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="indeksChart"></canvas>
                        </div>
                    </div>
                </div>


                {{-- F02 & F03 Aspek Charts Section --}}
                <div style="display: grid; grid-template-columns: 1fr; gap: 24px; margin-top: 30px; padding: 25px; background: white; border-radius: 10px; border: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);">
                    {{-- F02 Aspek Score Chart --}}
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title"><i class="fas fa-chart-bar"></i> F02 Validasi - Skor Per Aspek</div>
                            <div class="chart-actions">
                                @if($isGlobalUser && $availableUpps->count() > 0)
                                    <button type="button" class="btn-chart-export" onclick="openChartFilter('modalFilterF02Aspek')">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB;">
                            <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Total Validasi</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1F2937;">{{ $f02TotalValidasi ?? 0 }}</div>
                        </div>
                        <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB;">
                            <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Rata-rata Skor</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1F2937;">{{ number_format($f02AverageScore ?? 0, 2) }}</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="f02AspekChart"></canvas>
                        </div>
                    </div>

                    {{-- F03 Aspek Score Chart --}}
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title"><i class="fas fa-chart-bar"></i> F03 Survei - Skor Per Aspek</div>
                            <div class="chart-actions">
                                @if($isGlobalUser && $availableUpps->count() > 0)
                                    <button type="button" class="btn-chart-export" onclick="openChartFilter('modalFilterF03Aspek')">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB;">
                            <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Total Responden</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1F2937;">{{ $f03TotalResponses ?? 0 }}</div>
                        </div>
                        <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB;">
                            <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Rata-rata Skor</div>
                            <div style="font-size: 20px; font-weight: 700; color: #1F2937;">{{ number_format($f03AverageScore ?? 0, 2) }}</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="f03AspekChart"></canvas>
                        </div>
                    </div>
                </div>

            @else
                {{-- Empty State --}}
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h5>Tidak Ada Data</h5>
                    <p>
                        @if($availableUpps->count() === 0)
                            Anda tidak memiliki akses ke unit pelayanan manapun.
                        @else
                            Silakan pilih unit pelayanan untuk menampilkan data.
                        @endif
                    </p>
                </div>
            @endif
    </div>

            {{-- DATA TABLE --}}
                {{-- Data Table --}}
                <div class="table-card">
                    <h5><i class="fas fa-table"></i> Detail Data Per Unit Pelayanan</h5>
                    <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Unit Pelayanan</th>
                                    <th style="width: 15%; text-align: center;">F02</th>
                                    <th style="width: 15%; text-align: center;">F03</th>
                                    <th style="width: 15%; text-align: center;">Responden</th>
                                    <th style="width: 20%; text-align: center;">Indeks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($dashboardData['upps']))
                                    @foreach($dashboardData['upps'] as $upp)
                                        <tr>
                                            <td><strong>{{ $upp['upp_nama'] }}</strong></td>
                                            <td style="text-align: center;">
                                                <span class="badge badge-primary">{{ $upp['f02_nilai'] }}</span>
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="badge badge-info">{{ $upp['f03_rata_rata'] }}</span>
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="badge badge-secondary">{{ $upp['f03_jumlah_responden'] }}</span>
                                            </td>
                                            <td style="text-align: center;">
                                                @php
                                                    $indeks = $upp['indeks_nilai'];
                                                    if ($indeks >= 80) $badgeClass = 'badge-success';
                                                    elseif ($indeks >= 60) $badgeClass = 'badge-warning';
                                                    else $badgeClass = 'badge-danger';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $indeks }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">
                                            Tidak ada data unit pelayanan untuk ditampilkan
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                    </table>
                </div>


    {{-- Modal Filter UPP (Custom Modal) --}}
    @if($isGlobalUser && $availableUpps->count() > 0)
        <div class="modal-overlay" id="uppFilterModal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h5><i class="fas fa-building"></i> Pilih Unit Pelayanan</h5>
                    <button type="button" class="modal-close-btn" id="closeModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="select-all-check">
                        <input class="form-check-input" type="checkbox" id="selectAllUpp">
                        <label class="form-check-label" for="selectAllUpp">
                            Pilih Semua ({{ $availableUpps->count() }} UPP)
                        </label>
                    </div>
                    <div id="uppChecklistContainer">
                        @foreach($availableUppsWithScores as $upp)
                            <div class="form-check">
                                <input class="form-check-input upp-checkbox" type="checkbox" 
                                    id="upp_{{ $upp['id'] }}" 
                                    value="{{ $upp['id'] }}"
                                    {{ in_array($upp['id'], $selectedUppIds) ? 'checked' : '' }}>
                                <label class="form-check-label" for="upp_{{ $upp['id'] }}" style="display: flex; justify-content: space-between; width: 100%; align-items: center; gap: 10px;">
                                    <span>{{ $upp['nama'] }}</span>
                                    <span style="font-weight: bold; color: {{ $upp['ipp_score'] >= 3.01 ? '#10B981' : '#EF4444' }}; font-size: 0.85rem; background: #f3f4f6; padding: 2px 8px; border-radius: 4px;">{{ number_format($upp['ipp_score'], 2) }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" id="closeModalBtn">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn-modal btn-modal-submit" id="submitUppFilter">
                        <i class="fas fa-check"></i> Tampilkan Data
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Filter Comparison Chart --}}
        <div class="modal-overlay" id="modalFilterComparison">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h5><i class="fas fa-chart-bar"></i> Filter Perbandingan F02 & F03</h5>
                    <button type="button" class="modal-close-btn" onclick="closeChartFilter('modalFilterComparison')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="select-all-check">
                        <input class="form-check-input" type="checkbox" id="selectAllComparison">
                        <label class="form-check-label" for="selectAllComparison">
                            Pilih Semua UPP
                        </label>
                    </div>
                    <div id="comparisonChecklistContainer">
                        @foreach($availableUpps->whereIn('id', $selectedUppIds) as $upp)
                            <div class="form-check">
                                <input class="form-check-input comparison-checkbox" type="checkbox" 
                                    id="comp_upp_{{ $upp->id }}" 
                                    value="{{ $upp->id }}"
                                    checked>
                                <label class="form-check-label" for="comp_upp_{{ $upp->id }}">
                                    {{ $upp->nama }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" onclick="closeChartFilter('modalFilterComparison')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn-modal btn-modal-submit" onclick="applyComparisonFilter()">
                        <i class="fas fa-check"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Filter Indeks Chart --}}
        <div class="modal-overlay" id="modalFilterIndeks">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h5><i class="fas fa-chart-line"></i> Filter Indeks Pelayanan</h5>
                    <button type="button" class="modal-close-btn" onclick="closeChartFilter('modalFilterIndeks')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="select-all-check">
                        <input class="form-check-input" type="checkbox" id="selectAllIndeks">
                        <label class="form-check-label" for="selectAllIndeks">
                            Pilih Semua UPP
                        </label>
                    </div>
                    <div id="indeksChecklistContainer">
                        @foreach($availableUpps->whereIn('id', $selectedUppIds) as $upp)
                            <div class="form-check">
                                <input class="form-check-input indeks-checkbox" type="checkbox" 
                                    id="indeks_upp_{{ $upp->id }}" 
                                    value="{{ $upp->id }}"
                                    checked>
                                <label class="form-check-label" for="indeks_upp_{{ $upp->id }}">
                                    {{ $upp->nama }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" onclick="closeChartFilter('modalFilterIndeks')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn-modal btn-modal-submit" onclick="applyIndeksFilter()">
                        <i class="fas fa-check"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Filter F02 Aspek Chart --}}
        <div class="modal-overlay" id="modalFilterF02Aspek">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h5><i class="fas fa-chart-bar"></i> Filter F02 Validasi</h5>
                    <button type="button" class="modal-close-btn" onclick="closeChartFilter('modalFilterF02Aspek')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="select-all-check">
                        <input class="form-check-input" type="checkbox" id="selectAllF02Aspek">
                        <label class="form-check-label" for="selectAllF02Aspek">
                            Pilih Semua UPP
                        </label>
                    </div>
                    <div id="f02AspekChecklistContainer">
                        @foreach($availableUpps->whereIn('id', $selectedUppIds) as $upp)
                            <div class="form-check">
                                <input class="form-check-input f02aspek-checkbox" type="checkbox" 
                                    id="f02_upp_{{ $upp->id }}" 
                                    value="{{ $upp->id }}"
                                    checked>
                                <label class="form-check-label" for="f02_upp_{{ $upp->id }}">
                                    {{ $upp->nama }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" onclick="closeChartFilter('modalFilterF02Aspek')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn-modal btn-modal-submit" onclick="applyF02AspekFilter()">
                        <i class="fas fa-check"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Filter F03 Aspek Chart --}}
        <div class="modal-overlay" id="modalFilterF03Aspek">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h5><i class="fas fa-chart-pie"></i> Filter F03 Survei</h5>
                    <button type="button" class="modal-close-btn" onclick="closeChartFilter('modalFilterF03Aspek')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="select-all-check">
                        <input class="form-check-input" type="checkbox" id="selectAllF03Aspek">
                        <label class="form-check-label" for="selectAllF03Aspek">
                            Pilih Semua UPP
                        </label>
                    </div>
                    <div id="f03AspekChecklistContainer">
                        @foreach($availableUpps->whereIn('id', $selectedUppIds) as $upp)
                            <div class="form-check">
                                <input class="form-check-input f03aspek-checkbox" type="checkbox" 
                                    id="f03_upp_{{ $upp->id }}" 
                                    value="{{ $upp->id }}"
                                    checked>
                                <label class="form-check-label" for="f03_upp_{{ $upp->id }}">
                                    {{ $upp->nama }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" onclick="closeChartFilter('modalFilterF03Aspek')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn-modal btn-modal-submit" onclick="applyF03AspekFilter()">
                        <i class="fas fa-check"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    let comparisonChart = null;
    let indeksChart = null;
    let f02AspekChart = null;
    let f03AspekChart = null;
    const chartDataCache = @json(isset($dashboardData['upps']) ? $dashboardData['upps'] : []);
    const summaryData = @json(isset($dashboardData['summary']) ? $dashboardData['summary'] : []);

    document.addEventListener('DOMContentLoaded', function() {
        if (chartDataCache.length > 0) {
            initializeCharts();
        }

        // Custom Modal Logic
        const moduleOverlay = document.getElementById('uppFilterModal');
        const openModalBtn = document.getElementById('openUppModal');
        const closeModalBtn = document.getElementById('closeModal');
        const closeModalBtnFooter = document.getElementById('closeModalBtn');
        const submitModalBtn = document.getElementById('submitUppFilter');
        const selectAllCheckbox = document.getElementById('selectAllUpp');
        const uppCheckboxes = document.querySelectorAll('.upp-checkbox');

        // Open Modal
        if (openModalBtn && moduleOverlay) {
            openModalBtn.addEventListener('click', function() {
                moduleOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Close Modal
        const closeModal = () => {
            if (moduleOverlay) {
                moduleOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        };

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeModal);
        }
        if (closeModalBtnFooter) {
            closeModalBtnFooter.addEventListener('click', closeModal);
        }

        // Close modal when clicking outside
        if (moduleOverlay) {
            moduleOverlay.addEventListener('click', function(e) {
                if (e.target === moduleOverlay) {
                    closeModal();
                }
            });
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Select All functionality
        if (selectAllCheckbox && uppCheckboxes.length > 0) {
            selectAllCheckbox.addEventListener('change', function() {
                uppCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectAllCheckbox();
            });

            // Individual checkbox change
            uppCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAllCheckbox);
            });

            // Initialize on page load
            updateSelectAllCheckbox();

            // Submit filter
            if (submitModalBtn) {
                submitModalBtn.addEventListener('click', function() {
                    const selectedValues = Array.from(uppCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    if (selectedValues.length === 0) {
                        alert('Pilih minimal satu UPP!');
                        return;
                    }

                    // Update hidden form
                    const uppSelect = document.getElementById('uppSelect');
                    Array.from(uppSelect.options).forEach(option => {
                        option.selected = selectedValues.includes(option.value);
                    });

                    // Save preference via AJAX before submitting form
                    fetch('{{ route("dashboard.save-preferred-upps") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            upp_ids: selectedValues
                        })
                    }).then(response => {
                        // Whether success or error, submit the form to update the view
                        document.getElementById('filterForm').submit();
                    }).catch(error => {
                        console.error('Error saving preference:', error);
                        // Still submit form to update view even if save failed
                        document.getElementById('filterForm').submit();
                    });
                });
            }

            function updateSelectAllCheckbox() {
                const allChecked = Array.from(uppCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(uppCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;

                // Update info text with count
                const checkedCount = Array.from(uppCheckboxes).filter(cb => cb.checked).length;
                const totalCount = uppCheckboxes.length;
                const displayInfoText = document.getElementById('displayInfoText');
                if (displayInfoText) {
                    if (checkedCount === totalCount) {
                        displayInfoText.textContent = 'Menampilkan semua ' + totalCount + ' UPP';
                    } else {
                        displayInfoText.textContent = 'Menampilkan ' + checkedCount + ' dari ' + totalCount + ' UPP';
                    }
                }
            }
        }
    });

    function getPredikat(nilai) {
        if (nilai >= 4.51 && nilai <= 5) return 'Pelayanan Prima';
        if (nilai >= 4.01 && nilai <= 4.5) return 'Sangat Baik';
        if (nilai >= 3.51 && nilai <= 4) return 'Baik';
        if (nilai >= 3.01 && nilai <= 3.5) return 'Baik (Dengan Catatan)';
        if (nilai >= 2.51 && nilai <= 3) return 'Cukup';
        if (nilai >= 2.01 && nilai <= 2.5) return 'Cukup (Dengan Catatan)';
        if (nilai >= 1.51 && nilai <= 2) return 'Buruk';
        if (nilai >= 1.01 && nilai <= 1.5) return 'Sangat Buruk';
        if (nilai >= 0 && nilai <= 1) return 'Gagal';
        return 'N/A';
    }

    function initializeCharts() {
        // Helper function to extract email prefix and convert to uppercase
        const getEmailPrefix = (email) => {
            if (!email) return 'UNKNOWN';
            return email.split('@')[0].toUpperCase();
        };

        const labels = chartDataCache.map(d => getEmailPrefix(d.user_email));
        const f02Data = chartDataCache.map(d => parseFloat(d.f02_nilai));
        const f03Data = chartDataCache.map(d => parseFloat(d.f03_rata_rata));
        const indeksData = chartDataCache.map(d => parseFloat(d.indeks_nilai));

        // Update summary predicate
        if (summaryData && summaryData.avg_indeks) {
            const predicateSummary = document.getElementById('predicateSummary');
            if (predicateSummary) {
                predicateSummary.textContent = getPredikat(summaryData.avg_indeks);
            }
        }

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12, weight: 500 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 },
                    cornerRadius: 6,
                    callbacks: {
                        afterLabel: (context) => {
                            if (context.chart.config.type === 'line' && context.dataset.label === 'Indeks Pelayanan Publik') {
                                return 'Predikat: ' + getPredikat(context.parsed.y);
                            }
                            return '';
                        }
                    }
                }
            }
        };

        // Comparison Chart
        const ctxComparison = document.getElementById('comparisonChart');
        if (ctxComparison) {
            if (comparisonChart) comparisonChart.destroy();

            comparisonChart = new Chart(ctxComparison, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'F02 (Dokumentasi)',
                            data: f02Data,
                            backgroundColor: '#4F46E5',
                            borderColor: '#4F39D5',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'F03 (Survey)',
                            data: f03Data,
                            backgroundColor: '#10B981',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    indexAxis: labels.length > 5 ? 'y' : 'x',
                    scales: {
                        y: { beginAtZero: true },
                        x: { beginAtZero: true }
                    }
                }
            });
        }

        // Indeks Chart
        const ctxIndeks = document.getElementById('indeksChart');
        if (ctxIndeks) {
            if (indeksChart) indeksChart.destroy();

            indeksChart = new Chart(ctxIndeks, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Indeks Pelayanan Publik',
                        data: indeksData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // F02 Aspek Chart
        const ctxF02Aspek = document.getElementById('f02AspekChart');
        if (ctxF02Aspek) {
            if (f02AspekChart) f02AspekChart.destroy();
            
            const f02Labels = {!! json_encode($f02AspekLabels ?? []) !!};
            const f02Values = {!! json_encode($f02AspekValues ?? []) !!};
            
            f02AspekChart = new Chart(ctxF02Aspek, {
                type: 'bar',
                data: {
                    labels: f02Labels,
                    datasets: [{
                        label: 'Skor F02',
                        data: f02Values,
                        backgroundColor: ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#06B6D4'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 5
                        }
                    }
                }
            });
        }

        // F03 Aspek Chart
        const ctxF03Aspek = document.getElementById('f03AspekChart');
        if (ctxF03Aspek) {
            if (f03AspekChart) f03AspekChart.destroy();
            
            const f03Labels = {!! json_encode($f03AspekLabels ?? []) !!};
            const f03Values = {!! json_encode($f03AspekValues ?? []) !!};
            
            f03AspekChart = new Chart(ctxF03Aspek, {
                type: 'bar',
                data: {
                    labels: f03Labels,
                    datasets: [{
                        label: 'Skor F03',
                        data: f03Values,
                        backgroundColor: ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#06B6D4'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 5
                        }
                    }
                }
            });
        }
    }

    function downloadChartImage(chartId, filename) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;
        
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/jpeg', 0.95);
        link.download = filename + '_' + new Date().toISOString().split('T')[0] + '.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function downloadChartCsv(chartType) {
        const getEmailPrefix = (email) => {
            if (!email) return 'UNKNOWN';
            return email.split('@')[0].toUpperCase();
        };

        const rows = [];
        
        if (chartType === 'comparison') {
            rows.push(['Unit Pelayanan', 'F02 (0-100)', 'F02 (0-5)', 'F03 (1-5)', 'Responden']);
            chartDataCache.forEach(d => {
                rows.push([getEmailPrefix(d.user_email), d.f02_nilai, d.f02_normalized, d.f03_rata_rata, d.f03_jumlah_responden]);
            });
        } else if (chartType === 'indeks') {
            rows.push(['Unit Pelayanan', 'F02 (0-5)', 'F03 (1-5)', 'Rumus', 'Indeks Nilai', 'Predikat']);
            chartDataCache.forEach(d => {
                const rumus = `(${d.f02_normalized}*0.75)+(${d.f03_rata_rata}*0.25)`;
                rows.push([getEmailPrefix(d.user_email), d.f02_normalized, d.f03_rata_rata, rumus, d.indeks_nilai, getPredikat(d.indeks_nilai)]);
            });
        }

        const csv = rows.map(r => r.join(',')).join('\n');
        downloadFile(csv, 'dashboard_' + chartType + '.csv');
    }

    document.getElementById('exportDashboardBtn')?.addEventListener('click', function() {
        const element = document.querySelector('.dash-main');
        const opt = {
            margin: 15,
            filename: 'Dashboard_PEKPP_' + new Date().toISOString().split('T')[0] + '.pdf',
            image: { type: 'jpeg', quality: 0.95 },
            html2canvas: { scale: 2 },
            jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
        };
        html2pdf().set(opt).from(element).save();
    });

    document.getElementById('exportChartCsvBtn')?.addEventListener('click', function() {
        const getEmailPrefix = (email) => {
            if (!email) return 'UNKNOWN';
            return email.split('@')[0].toUpperCase();
        };

        const rows = [['Unit Pelayanan', 'F02 (0-100)', 'F02 (0-5)', 'F03 (1-5)', 'Responden', 'Rumus Indeks', 'Indeks Nilai', 'Predikat']];
        chartDataCache.forEach(d => {
            const rumus = `(${d.f02_normalized}*0.75)+(${d.f03_rata_rata}*0.25)`;
            rows.push([getEmailPrefix(d.user_email), d.f02_nilai, d.f02_normalized, d.f03_rata_rata, d.f03_jumlah_responden, rumus, d.indeks_nilai, getPredikat(d.indeks_nilai)]);
        });
        rows.push([]);
        rows.push(['RINGKASAN']);
        rows.push(['Total Unit', summaryData.total_upp]);
        rows.push(['Rata-rata F02 (1-5)', summaryData.avg_f02]);
        rows.push(['Rata-rata F03 (1-5)', summaryData.avg_f03]);
        rows.push(['Rata-rata Indeks', summaryData.avg_indeks]);
        rows.push(['Predikat', getPredikat(summaryData.avg_indeks)]);
        const csv = rows.map(r => r.join(',')).join('\n');
        downloadFile(csv, 'dashboard_lengkap.csv');
    });

    function downloadFile(content, filename) {
        const link = document.createElement('a');
        link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(content);
        link.download = filename.replace('.csv', '_' + new Date().toISOString().split('T')[0] + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Track selected UPP for each chart filter
    let comparisonFilterUppIds = {!! json_encode($selectedUppIds ?? []) !!};
    let indeksFilterUppIds = {!! json_encode($selectedUppIds ?? []) !!};
    let f02AspekFilterUppIds = {!! json_encode($selectedUppIds ?? []) !!};
    let f03AspekFilterUppIds = {!! json_encode($selectedUppIds ?? []) !!};

    // Functions for chart filter modals
    function openChartFilter(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeChartFilter(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function getSelectedChartUppIds(checkboxSelector) {
        return Array.from(document.querySelectorAll(checkboxSelector))
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value));
    }

    function applyComparisonFilter() {
        comparisonFilterUppIds = getSelectedChartUppIds('.comparison-checkbox');
        if (comparisonFilterUppIds.length === 0) {
            alert('Pilih minimal satu UPP!');
            return;
        }
        // Fetch fresh data from server
        fetchAndRenderComparison();
        closeChartFilter('modalFilterComparison');
    }

    function applyIndeksFilter() {
        indeksFilterUppIds = getSelectedChartUppIds('.indeks-checkbox');
        if (indeksFilterUppIds.length === 0) {
            alert('Pilih minimal satu UPP!');
            return;
        }
        // Fetch fresh data from server
        fetchAndRenderIndeks();
        closeChartFilter('modalFilterIndeks');
    }

    function applyF02AspekFilter() {
        f02AspekFilterUppIds = getSelectedChartUppIds('.f02aspek-checkbox');
        if (f02AspekFilterUppIds.length === 0) {
            alert('Pilih minimal satu UPP!');
            return;
        }
        // Fetch fresh data from server
        fetchAndRenderF02Aspek();
        closeChartFilter('modalFilterF02Aspek');
    }

    function applyF03AspekFilter() {
        f03AspekFilterUppIds = getSelectedChartUppIds('.f03aspek-checkbox');
        if (f03AspekFilterUppIds.length === 0) {
            alert('Pilih minimal satu UPP!');
            return;
        }
        // Fetch fresh data from server
        fetchAndRenderF03Aspek();
        closeChartFilter('modalFilterF03Aspek');
    }

    function getFilteredData(uppIds) {
        return chartDataCache.filter(data => uppIds.includes(data.upp_id));
    }

    // AJAX Functions to fetch fresh data from server
    function fetchAndRenderComparison() {
        fetch('{{ route("api.dashboard.filtered-data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                upp_ids: comparisonFilterUppIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update local data cache
                chartDataCache = data.upps;
                // Render chart with fresh data
                renderComparisonChartFiltered();
            } else {
                alert('Error: ' + (data.error || 'Gagal memuat data'));
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Gagal mengambil data dari server');
        });
    }

    function fetchAndRenderIndeks() {
        fetch('{{ route("api.dashboard.filtered-data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                upp_ids: indeksFilterUppIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chartDataCache = data.upps;
                renderIndeksChartFiltered();
            } else {
                alert('Error: ' + (data.error || 'Gagal memuat data'));
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Gagal mengambil data dari server');
        });
    }

    function fetchAndRenderF02Aspek() {
        fetch('{{ route("api.dashboard.filtered-data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                upp_ids: f02AspekFilterUppIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update F02 aspek data with fresh values
                const f02Values = data.f02AspekValues;
                renderF02AspekChartFilteredWithData(f02Values);
            } else {
                alert('Error: ' + (data.error || 'Gagal memuat data'));
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Gagal mengambil data dari server');
        });
    }

    function fetchAndRenderF03Aspek() {
        fetch('{{ route("api.dashboard.filtered-data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                upp_ids: f03AspekFilterUppIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update F03 aspek data with fresh values
                const f03Values = data.f03AspekValues;
                renderF03AspekChartFilteredWithData(f03Values);
            } else {
                alert('Error: ' + (data.error || 'Gagal memuat data'));
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Gagal mengambil data dari server');
        });
    }

    function renderComparisonChartFiltered() {
        const filteredData = getFilteredData(comparisonFilterUppIds);
        const labels = filteredData.map(d => d.upp_nama.substring(0, 15));
        const f02Data = filteredData.map(d => d.f02_nilai);
        const f03Data = filteredData.map(d => d.f03_rata_rata);

        const ctxComparison = document.getElementById('comparisonChart');
        if (ctxComparison) {
            if (comparisonChart) comparisonChart.destroy();
            comparisonChart = new Chart(ctxComparison, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'F02 (Dokumentasi)',
                            data: f02Data,
                            backgroundColor: '#4F46E5',
                            borderColor: '#4F39D5',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'F03 (Survey)',
                            data: f03Data,
                            backgroundColor: '#10B981',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: { cornerRadius: 6, padding: 12, titleFont: { size: 13 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }
    }

    function renderIndeksChartFiltered() {
        const filteredData = getFilteredData(indeksFilterUppIds);
        const labels = filteredData.map(d => d.upp_nama.substring(0, 15));
        const indeksData = filteredData.map(d => d.indeks_nilai);

        const ctxIndeks = document.getElementById('indeksChart');
        if (ctxIndeks) {
            if (indeksChart) indeksChart.destroy();
            indeksChart = new Chart(ctxIndeks, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Indeks Pelayanan Publik',
                        data: indeksData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: { cornerRadius: 6, padding: 12, titleFont: { size: 13 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }
    }

    function renderF02AspekChartFiltered() {
        const filteredData = getFilteredData(f02AspekFilterUppIds);
        const f02Labels = {!! json_encode($f02AspekLabels ?? []) !!};
        const f02Values = {!! json_encode($f02AspekValues ?? []) !!};

        const ctxF02Aspek = document.getElementById('f02AspekChart');
        if (ctxF02Aspek) {
            if (f02AspekChart) f02AspekChart.destroy();
            f02AspekChart = new Chart(ctxF02Aspek, {
                type: 'bar',
                data: {
                    labels: f02Labels,
                    datasets: [{
                        label: 'Skor F02',
                        data: f02Values,
                        backgroundColor: ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#06B6D4'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: { x: { beginAtZero: true, max: 5 } }
                }
            });
        }
    }

    // New function to render with custom data from server
    function renderF02AspekChartFilteredWithData(f02Values) {
        const f02Labels = {!! json_encode($f02AspekLabels ?? []) !!};

        const ctxF02Aspek = document.getElementById('f02AspekChart');
        if (ctxF02Aspek) {
            if (f02AspekChart) f02AspekChart.destroy();
            f02AspekChart = new Chart(ctxF02Aspek, {
                type: 'bar',
                data: {
                    labels: f02Labels,
                    datasets: [{
                        label: 'Skor F02',
                        data: f02Values,
                        backgroundColor: ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#06B6D4'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: { x: { beginAtZero: true, max: 5 } }
                }
            });
        }
    }

    function renderF03AspekChartFiltered() {
        const filteredData = getFilteredData(f03AspekFilterUppIds);
        const f03Labels = {!! json_encode($f03AspekLabels ?? []) !!};
        const f03Values = {!! json_encode($f03AspekValues ?? []) !!};

        const ctxF03Aspek = document.getElementById('f03AspekChart');
        if (ctxF03Aspek) {
            if (f03AspekChart) f03AspekChart.destroy();
            f03AspekChart = new Chart(ctxF03Aspek, {
                type: 'bar',
                data: {
                    labels: f03Labels,
                    datasets: [{
                        label: 'Skor F03',
                        data: f03Values,
                        backgroundColor: ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#06B6D4'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 5
                        }
                    }
                }
            });
        }
    }

    // New function to render with custom data from server
    function renderF03AspekChartFilteredWithData(f03Values) {
        const f03Labels = {!! json_encode($f03AspekLabels ?? []) !!};

        const ctxF03Aspek = document.getElementById('f03AspekChart');
        if (ctxF03Aspek) {
            if (f03AspekChart) f03AspekChart.destroy();
            f03AspekChart = new Chart(ctxF03Aspek, {
                type: 'bar',
                data: {
                    labels: f03Labels,
                    datasets: [{
                        label: 'Skor F03',
                        data: f03Values,
                        backgroundColor: ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#06B6D4'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 5
                        }
                    }
                }
            });
        }
    }

    // Handle modal close with Escape key and click outside
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeChartFilter('modalFilterComparison');
            closeChartFilter('modalFilterIndeks');
            closeChartFilter('modalFilterF02Aspek');
            closeChartFilter('modalFilterF03Aspek');
        }
    });

    // Close modal when clicking outside
    document.querySelectorAll('[id^="modalFilter"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeChartFilter(modal.id);
            }
        });
    });

    // Setup select-all checkboxes for each chart filter
    function setupChartSelectAll(selectAllCheckId, checkboxSelector) {
        const selectAllCheck = document.getElementById(selectAllCheckId);
        const checkboxes = document.querySelectorAll(checkboxSelector);
        
        if (selectAllCheck && checkboxes.length > 0) {
            selectAllCheck.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
            
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    const someChecked = Array.from(checkboxes).some(c => c.checked);
                    selectAllCheck.checked = allChecked;
                    selectAllCheck.indeterminate = someChecked && !allChecked;
                });
            });
        }
    }

    // Initialize select-all for all chart filters
    setupChartSelectAll('selectAllComparison', '.comparison-checkbox');
    setupChartSelectAll('selectAllIndeks', '.indeks-checkbox');
    setupChartSelectAll('selectAllF02Aspek', '.f02aspek-checkbox');
    setupChartSelectAll('selectAllF03Aspek', '.f03aspek-checkbox');

</script>

@endsection
