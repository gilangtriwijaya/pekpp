@extends('layouts.app')
@section('title','F03 Token Management')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1e40af;
        --primary-light: #dbeafe;
        --danger: #dc2626;
        --success: #16a34a;
        --success-light: #dcfce7;
        --warning: #ea580c;
        --neutral-50: #f8fafc;
        --neutral-100: #f1f5f9;
        --neutral-200: #e2e8f0;
        --neutral-400: #94a3b8;
        --neutral-600: #475569;
        --neutral-700: #334155;
        --neutral-900: #0f172a;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    /* Toggle Switch Styles */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
        cursor: pointer;
        user-select: none;
    }

    .toggle-switch input {
        opacity: 0;
        width: 100%;
        height: 100%;
        position: absolute;
        cursor: pointer;
        margin: 0;
        padding: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: all 0.3s ease;
        border-radius: 26px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: all 0.3s ease;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .toggle-switch input:checked + .toggle-slider {
        background-color: #16a34a;
    }

    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }

    .toggle-switch input:disabled + .toggle-slider {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .f03token-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .f03token-header {
        margin-bottom: 32px;
    }

    .f03token-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--neutral-900);
        margin-bottom: 8px;
    }

    .f03token-header p {
        font-size: 14px;
        color: var(--neutral-600);
    }

    /* Filter Section */
    .f03token-filter {
        background: white;
        padding: 20px 24px;
        border-radius: 12px;
        border: 1px solid var(--neutral-200);
        margin-bottom: 24px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: flex-end;
    }

    .f03token-filter label {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .f03token-filter label span {
        font-size: 13px;
        font-weight: 600;
        color: var(--neutral-700);
    }

    .f03token-input, .f03token-select {
        padding: 10px 14px;
        border: 1px solid var(--neutral-200);
        border-radius: 8px;
        font-size: 14px;
        background: var(--neutral-50);
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .f03token-input:focus, .f03token-select:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* Table */
    .f03token-table-wrapper {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--neutral-200);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .f03token-table {
        width: 100%;
        border-collapse: collapse;
    }

    .f03token-table thead tr {
        background: var(--neutral-50);
        border-bottom: 1px solid var(--neutral-200);
    }

    .f03token-table th {
        padding: 14px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        color: var(--neutral-700);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .f03token-table td {
        padding: 14px;
        border-bottom: 1px solid var(--neutral-100);
        font-size: 14px;
        color: var(--neutral-900);
    }

    .f03token-table tbody tr {
        transition: all 0.2s ease;
    }

    .f03token-table tbody tr:hover {
        background-color: var(--neutral-50);
    }

    .f03token-table tbody tr:last-child td {
        border-bottom: none;
    }

    .f03token-table code {
        background-color: var(--neutral-100);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-family: 'Courier New', monospace;
        color: var(--primary);
    }

    /* Badges */
    .f03token-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .f03token-badge-active {
        background-color: var(--success-light);
        color: var(--success);
    }

    .f03token-badge-inactive {
        background-color: #fee2e2;
        color: var(--danger);
    }

    .f03token-badge-pending {
        background-color: #fef3c7;
        color: var(--warning);
    }

    .f03token-badge-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: currentColor;
    }

    /* Actions */
    .f03token-actions {
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .f03token-btn-action {
        background: white;
        border: 1px solid var(--neutral-200);
        cursor: pointer;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.2s ease;
        color: var(--neutral-700);
    }

    .f03token-btn-action:hover {
        border-color: var(--primary);
        color: var(--primary);
        background-color: var(--primary-light);
    }

    .f03token-btn-action.danger {
        border-color: var(--danger);
        color: var(--danger);
    }

    .f03token-btn-action.danger:hover {
        background-color: #fee2e2;
    }

    .f03token-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--neutral-400);
        font-size: 14px;
    }

    .f03token-empty small {
        display: block;
        margin-top: 8px;
        color: var(--neutral-400);
        font-size: 12px;
    }

    /* Buttons */
    .f03token-btn {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }

    .f03token-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    }

    .f03token-btn:active {
        transform: translateY(0);
    }

    /* Modal */
    .f03token-modal {
        display: none;
        position: fixed !important;
        z-index: 1000 !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .f03token-modal.show {
        display: flex !important;
    }

    .f03token-modal-content {
        background-color: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        animation: slideUp 0.3s ease-out;
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

    .f03token-modal-header {
        padding: 24px;
        border-bottom: 1px solid var(--neutral-200);
    }

    .f03token-modal-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--neutral-900);
        margin: 0;
    }

    .f03token-modal-body {
        padding: 24px;
    }

    .f03token-modal-footer {
        padding: 20px 24px;
        border-top: 1px solid var(--neutral-200);
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .f03token-form-group {
        margin-bottom: 20px;
    }

    .f03token-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: var(--neutral-700);
    }

    .f03token-form-group input,
    .f03token-form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--neutral-200);
        border-radius: 8px;
        font-size: 14px;
        background: var(--neutral-50);
        font-family: inherit;
    }

    .f03token-form-group input:focus,
    .f03token-form-group select:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .f03token-qr-display {
        text-align: center;
        padding: 20px;
        background: var(--neutral-50);
        border-radius: 8px;
        margin: 20px 0;
    }

    .f03token-qr-display img {
        max-width: 200px;
        height: auto;
    }

    /* Toast */
    .f03token-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 16px 20px;
        border-radius: 8px;
        font-size: 14px;
        z-index: 2000;
        display: none;
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .f03token-toast.success {
        background-color: var(--success-light);
        color: var(--success);
        border: 1px solid #bbf7d0;
    }

    .f03token-toast.error {
        background-color: #fee2e2;
        color: var(--danger);
        border: 1px solid #fca5a5;
    }

    /* Pagination */
    .pagination {
        display: flex;
        gap: 6px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination li {
        list-style: none;
        margin: 0;
    }

    .pagination a,
    .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 10px;
        border: 1px solid var(--neutral-200);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        color: var(--neutral-700);
        text-decoration: none;
        background: white;
    }

    .pagination a:hover:not(.disabled) {
        border-color: var(--primary);
        background-color: var(--primary-light);
        color: var(--primary);
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
    }

    .pagination span.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        color: var(--neutral-400);
        background: var(--neutral-50);
    }

    .pagination span.disabled:hover {
        border-color: var(--neutral-200);
        background-color: var(--neutral-50);
        box-shadow: none;
    }

    .pagination span.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border-color: var(--primary);
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
    }

    .pagination .page-item.active {
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .f03token-filter {
            grid-template-columns: 1fr;
        }

        .f03token-table th,
        .f03token-table td {
            padding: 12px;
            font-size: 13px;
        }

        .f03token-title {
            font-size: 24px;
        }

        .f03token-actions {
            gap: 6px;
        }

        .f03token-btn-action {
            padding: 6px 10px;
            font-size: 11px;
        }

        .f03token-modal-content {
            width: 95%;
        }

        .pagination a,
        .pagination span {
            min-width: 32px;
            height: 32px;
            font-size: 12px;
        }
    }
</style>

<div class="f03token-container">
    <div class="f03token-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="f03token-title">Generate Token F03</h1>
            <p style="color: var(--neutral-600); margin-top: 8px;">Kelola token kuesioner publik F03 untuk setiap UPP per periode</p>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <!-- Button Generate Semua -->
            <button class="f03token-btn" onclick="generateAllTokens()" style="white-space: nowrap; margin-top: 0; height: fit-content;">
                ⚡ Generate Semua
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="f03token-filter">
        <label>
            <span>Periode</span>
            <select id="filterPeriode" class="f03token-select" onchange="changePeriode()">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ ($selectedPeriode == $p->id) ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->tahun }})
                    </option>
                @endforeach
            </select>
        </label>
        <label>
            <span>Cari UPP</span>
            <input type="search" id="searchUPP" class="f03token-input" placeholder="Nama atau Kode UPP...">
        </label>
    </div>

    <!-- Tokens Table -->
    <div class="f03token-table-wrapper">
        <table class="f03token-table">
            <thead>
                <tr>
                    <th style="width: 200px;">Nama UPP</th>
                    <th style="width: 150px;">Kode</th>
                    <th style="width: 120px;">Status Token</th>
                    <th style="width: 280px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($uppsWithStatus as $upp)
                <tr data-id="{{ $upp->token_id ?? '' }}" data-upp-id="{{ $upp->id }}" class="f03token-row">
                    <td>
                        <strong>{{ $upp->nama ?? '-' }}</strong>
                    </td>
                    <td>
                        <code style="background-color: #F3F4F6; padding: 4px 8px; border-radius: 4px; font-size: 12px;">{{ $upp->kode ?? '-' }}</code>
                    </td>
                    <td style="text-align: center;">
                        @if(is_null($upp->token_id))
                            <span class="f03token-badge" style="background-color: #FEF3C7; color: #92400E;">Belum dibuat</span>
                        @elseif($upp->token_aktif)
                            <span class="f03token-badge f03token-badge-active">Aktif</span>
                        @else
                            <span class="f03token-badge f03token-badge-inactive">Nonaktif</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div class="f03token-actions">
                            @if(is_null($upp->token_id))
                                <!-- No token yet -->
                                <button class="f03token-btn-action" onclick="generateTokenForUpp({{ $upp->id }}, {{ $selectedPeriode }}, '{{ $upp->nama }}')" title="Generate Token">
                                    ✚ Generate
                                </button>
                            @elseif($upp->token_aktif)
                                <!-- Token exists and is active -->
                                <button class="f03token-btn-action" onclick="showDetailModal({{ $upp->token_id }}, '{{ $upp->nama }}', '{{ $upp->token }}')" title="Lihat Detail & QR">
                                    📋 Detail
                                </button>
                                <button class="f03token-btn-action danger" onclick="showRevokeModal({{ $upp->token_id }})" title="Nonaktifkan Token">
                                    🔒 Revoke
                                </button>
                            @else
                                <!-- Token exists but is revoked -->
                                <button class="f03token-btn-action" onclick="showActivateModal({{ $upp->token_id }})" title="Aktifkan Kembali">
                                    ✓ Aktifkan
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="f03token-empty">
                        Belum ada UPP untuk periode ini. <br>
                        <small style="color: #9CA3AF;">Pastikan UPP sudah dibuat dan periode sudah aktif</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($uppsWithStatus->hasPages())
    <div style="margin-top: 40px; padding: 24px 0; text-align: center;">
        {{ $uppsWithStatus->links() }}
    </div>
    @endif
</div>

<!-- Modal: Generate Token -->
<div id="generateModal" class="f03token-modal">
    <div class="f03token-modal-content">
        <div class="f03token-modal-header">
            <h2 class="f03token-modal-title">Generate Token F03</h2>
        </div>
        <div class="f03token-modal-body">
            <p style="margin-bottom: 16px; color: #6B7280; font-size: 14px;">
                Periode: <strong id="genPeriodeName"></strong>
            </p>
            <div class="f03token-form-group">
                <label>UPP</label>
                <p style="font-size: 16px; font-weight: 600; margin: 0; color: #1F2937;" id="genUppName">-</p>
            </div>
            <div class="f03token-form-group" style="margin-top: 16px;">
                <label style="display: flex; align-items: center; cursor: pointer; gap: 8px;">
                    <input type="checkbox" id="genAllowMultiple" style="cursor: pointer; width: 18px; height: 18px;">
                    <span style="color: #374151; font-weight: 500;">Izinkan Pengisian Berulang</span>
                </label>
                <p style="font-size: 12px; color: #6B7280; margin-top: 6px;">Jika diaktifkan, responden dapat mengisi kuesioner lebih dari satu kali dari perangkat/IP yang berbeda.</p>
            </div>
        </div>
        <div class="f03token-modal-footer">
            <button class="f03token-btn-action" onclick="closeModal('generateModal')">Batal</button>
            <button class="f03token-btn" onclick="submitGenerateToken()">Generate</button>
        </div>
    </div>
</div>

<!-- Modal: Generate Batch -->
<div id="generateBatchModal" class="f03token-modal">
    <div class="f03token-modal-content">
        <div class="f03token-modal-header">
            <h2 class="f03token-modal-title">⚡ Generate Semua Token</h2>
        </div>
        <div class="f03token-modal-body">
            <p style="margin-bottom: 16px; color: #6B7280; font-size: 14px;">
                Periode: <strong id="genBatchPeriodeName"></strong>
            </p>
            <div style="background-color: #FEF3C7; border: 1px solid #FCD34D; padding: 12px; border-radius: 6px; font-size: 13px; color: #78350F; margin-bottom: 16px;">
                <strong>ℹ️ Info:</strong> Akan membuat token untuk <strong id="genBatchUppCount">...</strong> UPP.
            </div>
            <div style="background-color: #F3F4F6; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                <p style="font-size: 13px; color: #374151; margin-bottom: 12px; font-weight: 500;">⚙️ Pengaturan Pengisian Berulang</p>
                <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                    <div>
                        <p style="font-size: 13px; color: #374151; margin: 0; font-weight: 500;">Izinkan pengisian berulang?</p>
                        <p style="font-size: 12px; color: #6B7280; margin: 4px 0 0 0;">Responden dapat mengisi dari perangkat/IP berbeda</p>
                    </div>
                    <label class="toggle-switch" style="margin: 0; margin-left: 12px;">
                        <input type="checkbox" id="genBatchAllowMultipleToggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="f03token-modal-footer">
            <button class="f03token-btn-action" onclick="closeModal('generateBatchModal')">Batal</button>
            <button class="f03token-btn" onclick="submitGenerateAllTokens()">Generate Semua</button>
        </div>
    </div>
</div>

<!-- Modal: Detail Token & QR -->
<div id="detailModal" class="f03token-modal">
    <div class="f03token-modal-content">
        <div class="f03token-modal-header">
            <h2 class="f03token-modal-title">Detail Token - <span id="detailUppName"></span></h2>
        </div>
        <div class="f03token-modal-body">
            <div style="background-color: #F3F4F6; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                <p style="font-size: 12px; color: #6B7280; margin-bottom: 6px;">Token:</p>
                <code style="font-size: 12px; word-break: break-all;" id="detailTokenCode"></code>
            </div>
            <div id="detailQR" class="f03token-qr-display"></div>
            <div style="background-color: #F3F4F6; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                <p style="font-size: 12px; color: #6B7280; margin-bottom: 6px;">URL Kuesioner:</p>
                <a id="detailURLLink" href="#" target="_blank" style="
                    display: inline-block;
                    width: 100%;
                    padding: 10px 12px;
                    background: white;
                    color: #2563eb;
                    text-decoration: none;
                    border: 1px solid #D1D5DB;
                    border-radius: 6px;
                    font-size: 13px;
                    word-break: break-all;
                    transition: all 0.2s;
                    overflow: hidden;
                    text-overflow: ellipsis;
                " onmouseover="this.style.backgroundColor='#EFF6FF'" onmouseout="this.style.backgroundColor='white'">
                </a>
                <input type="text" id="detailURL" readonly style="display: none;">
            </div>

            <!-- Pengaturan Pengisian Berulang -->
            <div style="background-color: #F3F4F6; padding: 12px; border-radius: 6px; margin-top: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                    <div>
                        <p style="font-size: 12px; color: #6B7280; margin-bottom: 2px; font-weight: 600;">Pengisian Berulang</p>
                        <p style="font-size: 11px; color: #9CA3AF; margin: 0;"><span id="detailAllowMultipleStatus" style="font-weight: 600; color: #374151;"></span></p>
                    </div>
                    <label class="toggle-switch" onclick="event.stopPropagation();">
                        <input type="checkbox" id="allowMultipleToggle" onchange="toggleAllowMultiple()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="f03token-modal-footer">
            <button class="f03token-btn-action" onclick="copyToClipboard('detailURL')">📋 Copy URL</button>
            <button class="f03token-btn-action" onclick="openInNewTab('detailURL')" style="background: linear-gradient(135deg, #16a34a, #15803d); color: white; border: none;">🔗 Buka Link</button>
            <button class="f03token-btn-action" onclick="closeModal('detailModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal: Revoke Confirm -->
<div id="revokeModal" class="f03token-modal">
    <div class="f03token-modal-content">
        <div class="f03token-modal-header">
            <h2 class="f03token-modal-title">⚠️ Nonaktifkan Token</h2>
        </div>
        <div class="f03token-modal-body">
            <p style="color: #374151;">Apakah Anda yakin ingin menonaktifkan token ini?</p>
            <p style="color: #6B7280; font-size: 13px; margin-top: 12px;">Responden tidak akan bisa mengakses kuesioner dengan token ini. Anda bisa mengaktifkannya kembali kapan saja.</p>
        </div>
        <div class="f03token-modal-footer">
            <button class="f03token-btn-action" onclick="closeModal('revokeModal')">Batal</button>
            <button class="f03token-btn f03token-btn-action danger" onclick="submitRevoke()" style="background-color: #EF4444; color: white; border-color: #EF4444;">Ya, Nonaktifkan</button>
        </div>
    </div>
</div>

<!-- Modal: Activate Confirm -->
<div id="activateModal" class="f03token-modal">
    <div class="f03token-modal-content">
        <div class="f03token-modal-header">
            <h2 class="f03token-modal-title">✓ Aktifkan Token</h2>
        </div>
        <div class="f03token-modal-body">
            <p style="color: #374151;">Apakah Anda yakin ingin mengaktifkan kembali token ini?</p>
            <p style="color: #6B7280; font-size: 13px; margin-top: 12px;">Responden akan bisa mengakses kuesioner kembali dengan token yang sama.</p>
        </div>
        <div class="f03token-modal-footer">
            <button class="f03token-btn-action" onclick="closeModal('activateModal')">Batal</button>
            <button class="f03token-btn" onclick="submitActivate()">Ya, Aktifkan</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="f03token-toast" id="f03token-toast"></div>

<script>
    let currentTokenId = null;
    let currentTokenCode = null;
    let currentUppId = null;
    let currentPeriodeId = null;

    function showToast(message, type = 'success') {
        const toast = document.getElementById('f03token-toast');
        toast.textContent = message;
        toast.className = `f03token-toast ${type}`;
        toast.style.display = 'block';
        setTimeout(() => toast.style.display = 'none', 3000);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show');
        }
    }

    function changePeriode() {
        const periodeId = document.getElementById('filterPeriode').value;
        window.location.href = `?periode_id=${periodeId}`;
    }

    // Generate token from table row button click
    function generateTokenForUpp(uppId, periodeId, uppName) {
        currentUppId = uppId;
        currentPeriodeId = periodeId;

        // Get periode name from dropdown
        const periodeSelect = document.getElementById('filterPeriode');
        const selectedOption = periodeSelect.options[periodeSelect.selectedIndex];
        const periodeName = selectedOption.text;

        // Set modal content
        document.getElementById('genPeriodeName').textContent = periodeName;
        document.getElementById('genUppName').textContent = uppName;

        openModal('generateModal');
    }

    function submitGenerateToken() {
        if (!currentUppId || !currentPeriodeId) {
            showToast('Data UPP atau Periode tidak valid', 'error');
            return;
        }

        const allowMultiple = document.getElementById('genAllowMultiple').checked;

        fetch('{{ route("admin.f03.token.generate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                upp_id: currentUppId,
                periode_id: currentPeriodeId,
                allow_multiple_responses: allowMultiple
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeModal('generateModal');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.error || 'Gagal generate token', 'error');
            }
        })
        .catch(() => showToast('Gagal generate token', 'error'));
    }

    // Generate tokens for all UPPs without token
    function generateAllTokens() {
        const periodeSelect = document.getElementById('filterPeriode');
        const periodeId = periodeSelect.value;
        
        if (!periodeId) {
            showToast('Pilih periode terlebih dahulu', 'error');
            return;
        }

        // Get active periode name
        const selectedOption = periodeSelect.options[periodeSelect.selectedIndex];
        const periodeName = selectedOption.text;
        document.getElementById('genBatchPeriodeName').textContent = periodeName;
        
        // Count UPPs without token
        const uppWithoutToken = Array.from(document.querySelectorAll('.f03token-row')).filter(row => {
            return !row.getAttribute('data-id') || row.getAttribute('data-id') === '';
        }).length;
        
        document.getElementById('genBatchUppCount').textContent = uppWithoutToken;
        
        // Restore toggle state dari localStorage
        const toggleElement = document.getElementById('genBatchAllowMultipleToggle');
        const saved = localStorage.getItem('f03_batch_allow_multiple');
        if (saved === 'true') {
            toggleElement.checked = true;
        } else {
            toggleElement.checked = false;
        }
        
        // Store periode ID untuk later use
        document.getElementById('generateBatchModal').dataset.periodeId = periodeId;
        
        openModal('generateBatchModal');
    }

    function submitGenerateAllTokens() {
        const periodeId = document.getElementById('generateBatchModal').dataset.periodeId;
        
        if (!periodeId) {
            showToast('Periode tidak ditemukan', 'error');
            return;
        }
        
        // Baca dari toggle di dalam modal
        const toggleElement = document.getElementById('genBatchAllowMultipleToggle');
        const allowMultiple = toggleElement.checked;
        
        // Simpan ke localStorage
        localStorage.setItem('f03_batch_allow_multiple', allowMultiple ? 'true' : 'false');
        
        console.log('Toggle element:', toggleElement);
        console.log('Toggle checked property:', allowMultiple);
        console.log('Submitting with allow_multiple_responses:', allowMultiple, 'Type:', typeof allowMultiple);
        
        const btn = event.target;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '⏳ Sedang generate...';
        btn.disabled = true;
        
        const payload = {
            periode_id: parseInt(periodeId),
            allow_multiple_responses: allowMultiple ? 1 : 0
        };
        
        console.log('Payload dikirim:', JSON.stringify(payload));

        fetch('{{ route("admin.f03.token.generateAll") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            console.log('Response dari server:', data);
            if (data.success) {
                showToast(data.message, 'success');
                closeModal('generateBatchModal');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.error || 'Gagal generate token', 'error');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            showToast('Gagal generate token', 'error');
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
    }

    function showDetailModal(tokenId, uppName, tokenCode) {
        currentTokenId = tokenId;
        currentTokenCode = tokenCode;
        
        document.getElementById('detailUppName').textContent = uppName;
        document.getElementById('detailTokenCode').textContent = tokenCode;
        const formUrl = `{{ route('f03.public.form', ['token' => 'TOKEN']) }}`.replace('TOKEN', tokenCode);
        document.getElementById('detailURL').value = formUrl;
        
        // Set link href and text
        const linkElement = document.getElementById('detailURLLink');
        linkElement.href = formUrl;
        linkElement.textContent = formUrl;
        linkElement.title = formUrl;
        
        // Generate QR code
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(formUrl)}`;
        document.getElementById('detailQR').innerHTML = `<img src="${qrUrl}" alt="QR Code">`;
        
        // Fetch token data untuk get allow_multiple_responses setting
        fetch(`{{ route('admin.f03.token.show', ['id' => 'TOKEN_ID']) }}`.replace('TOKEN_ID', tokenId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.token) {
                const allowMultiple = data.token.allow_multiple_responses || false;
                // Update toggle switch
                document.getElementById('allowMultipleToggle').checked = allowMultiple;
                // Update status display
                document.getElementById('detailAllowMultipleStatus').textContent = allowMultiple ? '✓ Diizinkan' : '✗ Tidak Diizinkan';
                document.getElementById('detailAllowMultipleStatus').style.color = allowMultiple ? '#16a34a' : '#dc2626';
            }
        })
        .catch(() => {
            document.getElementById('detailAllowMultipleStatus').textContent = 'Tidak Diketahui';
        });
        
        openModal('detailModal');
    }

    function toggleAllowMultiple() {
        if (!currentTokenId) return;
        
        const toggle = document.getElementById('allowMultipleToggle');
        const newStatus = toggle.checked;
        
        // Disable toggle saat loading
        toggle.disabled = true;
        
        fetch(`{{ route('admin.f03.token.updateSettings', ['id' => 'TOKEN_ID']) }}`.replace('TOKEN_ID', currentTokenId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                allow_multiple_responses: newStatus
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                document.getElementById('detailAllowMultipleStatus').textContent = newStatus ? '✓ Diizinkan' : '✗ Tidak Diizinkan';
                document.getElementById('detailAllowMultipleStatus').style.color = newStatus ? '#16a34a' : '#dc2626';
            } else {
                showToast('Gagal mengubah pengaturan', 'error');
                toggle.checked = !newStatus; // Revert toggle
            }
        })
        .catch(() => {
            showToast('Gagal mengubah pengaturan', 'error');
            toggle.checked = !newStatus; // Revert toggle
        })
        .finally(() => {
            toggle.disabled = false; // Enable toggle lagi
        });
    }

    function openInNewTab(elementId) {
        const url = document.getElementById(elementId).value;
        if (url) {
            window.open(url, '_blank');
        }
    }

    function showRevokeModal(tokenId) {
        currentTokenId = tokenId;
        openModal('revokeModal');
    }

    function submitRevoke() {
        fetch(`{{ route('admin.f03.token.revoke', ['id' => 'TOKEN_ID']) }}`.replace('TOKEN_ID', currentTokenId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeModal('revokeModal');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Gagal menonaktifkan token', 'error');
            }
        })
        .catch(() => showToast('Gagal menonaktifkan token', 'error'));
    }

    function showActivateModal(tokenId) {
        currentTokenId = tokenId;
        openModal('activateModal');
    }

    function submitActivate() {
        fetch(`{{ route('admin.f03.token.activate', ['id' => 'TOKEN_ID']) }}`.replace('TOKEN_ID', currentTokenId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeModal('activateModal');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Gagal mengaktifkan token', 'error');
            }
        })
        .catch(() => showToast('Gagal mengaktifkan token', 'error'));
    }

    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        element.select();
        document.execCommand('copy');
        showToast('URL disalin ke clipboard', 'success');
    }

    // Search filter
    document.getElementById('searchUPP').addEventListener('keyup', function(e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.f03token-row').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        ['generateModal', 'generateBatchModal', 'detailModal', 'revokeModal', 'activateModal'].forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal && modal.classList.contains('show')) {
                closeModal(modalId);
            }
        });
    }

    // Initialize batch toggle state dari localStorage
    function initBatchToggleState() {
        const toggle = document.getElementById('batchAllowMultipleToggle');
        if (!toggle) {
            console.warn('Toggle element not found');
            return;
        }
        
        // Listen untuk perubahan (tambahan untuk memastikan save)
        toggle.addEventListener('change', function() {
            const newState = this.checked;
            localStorage.setItem('f03_batch_allow_multiple', newState ? 'true' : 'false');
            console.log('Toggle changed to:', newState);
        });
    }

    // Initialize immediately
    initBatchToggleState();
</script>

@endsection
