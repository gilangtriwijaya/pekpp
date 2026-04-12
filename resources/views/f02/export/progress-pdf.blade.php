<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Progress F02</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #2563EB;
            padding-bottom: 8px;
        }
        
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 4px;
        }
        
        .header-subtitle {
            font-size: 10px;
            color: #6B7280;
            margin-bottom: 2px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        
        .table thead {
            background-color: #2563EB;
            color: white;
        }
        
        .table th {
            padding: 8px 6px;
            text-align: center;
            font-weight: 600;
            border: 1px solid #1e40af;
            font-size: 10px;
        }
        
        .table td {
            padding: 6px;
            border: 1px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
        }
        
        .table tbody tr:nth-child(odd) {
            background-color: #F9FAFB;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #FFFFFF;
        }
        
        .table td:nth-child(2) {
            text-align: left;
        }
        
        .table tfoot tr {
            background-color: #2563EB;
            color: white;
            font-weight: bold;
        }
        
        .table tfoot td {
            border: 1px solid #1e40af;
            padding: 7px 6px;
        }
        
        .table tfoot td:nth-child(2) {
            text-align: left;
        }
        
        .status-checked {
            background-color: #DCFCE7;
            font-weight: bold;
            color: #166534;
        }
        
        .status-belum {
            background-color: #FEE2E2;
            font-weight: bold;
            color: #991B1B;
        }
        
        .status-pengisian {
            background-color: #FEF08A;
            font-weight: bold;
            color: #92400E;
        }
        
        .status-submit {
            background-color: #86EFAC;
            font-weight: bold;
            color: #166534;
        }
        
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #F0F9FF;
            border-left: 4px solid #2563EB;
        }
        
        .summary-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .color-legend {
            display: flex;
            gap: 24px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        
        .color-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
        }
        
        .color-box {
            width: 20px;
            height: 16px;
            border: 1px solid #999;
            flex-shrink: 0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 10px;
        }
        
        .summary-label {
            flex: 1;
        }
        
        .summary-value {
            font-weight: 600;
            color: #2563EB;
            text-align: right;
            min-width: 60px;
        }
        
        .footer {
            text-align: right;
            margin-top: 16px;
            font-size: 8px;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 8px;
        }
        
        .footer p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">Laporan Progress Pengisian F02</div>
        <div class="header-subtitle">Periode Tahun: {{ $periode->tahun }}</div>
        <div class="header-subtitle">Tanggal Export: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th style="width: 6%;">No.</th>
                <th style="width: 44%;">Unit Pelayanan Publik</th>
                <th style="width: 12.5%;">Belum Memulai</th>
                <th style="width: 12.5%;">Dalam Pengisian</th>
                <th style="width: 12.5%;">Submit</th>
                <th style="width: 12.5%;">Selesai Validasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($progressData as $idx => $row)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td style="text-align: left;">{{ $row['upp_nama'] }}</td>
                <td class="{{ $row['belum_memulai'] ? 'status-belum' : '' }}"></td>
                <td class="{{ $row['dalam_pengisian'] ? 'status-pengisian' : '' }}"></td>
                <td class="{{ $row['submit'] ? 'status-submit' : '' }}"></td>
                <td class="{{ $row['selesai_validasi'] ? 'status-submit' : '' }}"></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td>{{ $totals['belum_memulai'] }}</td>
                <td>{{ $totals['dalam_pengisian'] }}</td>
                <td>{{ $totals['submit'] }}</td>
                <td>{{ $totals['selesai_validasi'] }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="summary">
        <div class="summary-title">📊 Ringkasan Status & Warna Indikator</div>
        <div class="color-legend">
            <div class="color-item">
                <div class="color-box" style="background-color: #FEE2E2;"></div>
                <span>Belum Memulai</span>
            </div>
            <div class="color-item">
                <div class="color-box" style="background-color: #FEF08A;"></div>
                <span>Dalam Pengisian</span>
            </div>
            <div class="color-item">
                <div class="color-box" style="background-color: #86EFAC;"></div>
                <span>Submit / Selesai</span>
            </div>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total UPP:</span>
            <span class="summary-value">{{ $totals['total_upp'] }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Belum Memulai:</span>
            <span class="summary-value">{{ $totals['belum_memulai'] }} ({{ $totals['total_upp'] > 0 ? round(($totals['belum_memulai'] / $totals['total_upp']) * 100) : 0 }}%)</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Dalam Pengisian:</span>
            <span class="summary-value">{{ $totals['dalam_pengisian'] }} ({{ $totals['total_upp'] > 0 ? round(($totals['dalam_pengisian'] / $totals['total_upp']) * 100) : 0 }}%)</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Submit:</span>
            <span class="summary-value">{{ $totals['submit'] }} ({{ $totals['total_upp'] > 0 ? round(($totals['submit'] / $totals['total_upp']) * 100) : 0 }}%)</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Selesai Validasi:</span>
            <span class="summary-value">{{ $totals['selesai_validasi'] }} ({{ $totals['total_upp'] > 0 ? round(($totals['selesai_validasi'] / $totals['total_upp']) * 100) : 0 }}%)</span>
        </div>
    </div>
    
    <div class="footer">
        <p><strong>Sistem LAYANI Mandiri</strong> • Kab. Kepulauan Anambas</p>
        <p>Dokumen ini digenerate secara otomatis pada {{ now()->format('d/m/Y H:i:s') }} WIB</p>
    </div>
</body>
</html>
