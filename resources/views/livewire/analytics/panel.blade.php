<div class="min-h-screen bg-white">
    <style>
        /* === CSS VARIABLES - SYSTEM DESIGN === */
        :root {
            /* Semantic Colors - Flat, no gradients */
            --color-text-primary: #1f2937;
            --color-text-secondary: #6b7280;
            --color-text-tertiary: #9ca3af;
            --color-text-muted: #888780;

            --color-background-primary: #ffffff;
            --color-background-secondary: #f9fafb;
            --color-background-tertiary: #f3f4f6;

            --color-border-primary: #e5e7eb;
            --color-border-secondary: #d1d5db;
            --color-border-tertiary: #e2e8f0;

            /* Semantic Accents - No gradients */
            --color-blue-accent: #378ADD;
            --color-blue-dark: #185FA5;
            --color-blue-light: #eef2ff;

            --color-teal-accent: #1D9E75;
            --color-teal-dark: #0F6E56;
            --color-teal-light: #ecfdf5;

            --color-amber-accent: #EF9F27;
            --color-amber-dark: #BA7517;
            --color-amber-light: #fff7e8;

            --color-red-accent: #E24B4A;
            --color-red-dark: #A32D2D;
            --color-red-light: #fef2f2;

            --color-purple-accent: #7F77DD;
            --color-purple-dark: #534AB7;
            --color-purple-light: #f5f3ff;

            --color-gray-accent: #888780;
            --color-gray-dark: #5F5E5A;
            --color-gray-light: #f3f2f0;

            --primary: #4F46E5;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
        }

        .dash-main {
            min-height: 100vh;
            padding: 0;
            overflow-x: hidden;
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
        }

        /* Filter Section */
        .filter-section {
            background: #f9fafb;
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .filter-section .container {
            padding: 0;
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        /* Charts Section - Each chart terpisah */
        .chart-section {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .chart-section .container {
            padding: 0;
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        .chart-card {
            background: white;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        .chart-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .chart-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .chart-card-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 9999px;
            font-weight: 600;
        }

        .chart-container {
            position: relative;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
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

        .btn-export {
            background-color: #16a34a;
            color: #ffffff;
            border: 1px solid #15803d;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }

        .btn-export:hover {
            background-color: #15803d;
            border-color: #166534;
        }

        .btn-export[disabled] {
            background-color: #9ca3af;
            border-color: #9ca3af;
            cursor: not-allowed;
        }

        .action-info {
            font-size: 0.875rem;
            color: #6B7280;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 0;
        }

        /* === SUMMARY CARDS - NEW DESIGN SYSTEM === */

        /* Section Label */
        .summary-section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--color-text-primary);
            margin-bottom: 10px;
            margin-top: 18px;
            display: block;
        }

        .summary-section-label:first-of-type {
            margin-top: 0;
        }

        /* Base Card */
        .summary-card-new {
            border-radius: 12px;
            border: 1px solid var(--color-border-secondary);
            background: var(--color-background-primary);
            padding: 14px 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            cursor: pointer;
            transition: background 140ms ease;
        }

        .summary-card-new:hover {
            background: var(--color-background-secondary);
        }

        /* Card with left accent border */
        .summary-card-accent {
            border-left: 4px solid;
            border-radius: 0 12px 12px 0;
        }

        .summary-card-accent.accent-gray { border-left-color: var(--color-gray-accent); }
        .summary-card-accent.accent-blue { border-left-color: var(--color-blue-accent); }
        .summary-card-accent.accent-amber { border-left-color: var(--color-amber-accent); }
        .summary-card-accent.accent-teal { border-left-color: var(--color-teal-accent); }

        /* Card Label (above the number) */
        .summary-card-label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--color-text-primary);
            margin-bottom: 6px;
            display: block;
        }

        /* Card Value (the big number) */
        .summary-card-value {
            font-size: 32px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
            display: block;
        }

        .summary-card-value.color-gray { color: var(--color-gray-dark); }
        .summary-card-value.color-blue { color: var(--color-blue-dark); }
        .summary-card-value.color-amber { color: var(--color-amber-dark); }
        .summary-card-value.color-teal { color: var(--color-teal-dark); }
        .summary-card-value.color-red { color: var(--color-red-dark); }
        .summary-card-value.color-purple { color: var(--color-purple-dark); }

        /* Card Subtext */
        .summary-card-subtext {
            font-size: 12px;
            color: var(--color-text-secondary);
            line-height: 1.4;
            display: block;
        }

        /* Divider */
        .summary-card-divider {
            height: 1px;
            background: var(--color-border-tertiary);
            margin: 10px 0;
        }

        /* Badge Pill */
        .summary-badge-pill {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 100px;
            margin-bottom: 6px;
        }

        .summary-badge-pill.badge-red {
            background: var(--color-red-light);
            color: var(--color-red-dark);
        }
        .summary-badge-pill.badge-amber {
            background: var(--color-amber-light);
            color: var(--color-amber-dark);
        }
        .summary-badge-pill.badge-blue {
            background: var(--color-blue-light);
            color: var(--color-blue-dark);
        }

        /* Meta text (kontribusi IPP, dll) */
        .summary-card-meta {
            font-size: 12px;
            color: var(--color-text-primary);
            line-height: 1.4;
            display: block;
            margin-bottom: 4px;
        }

        .summary-card-meta strong {
            font-weight: 500;
            color: var(--color-text-primary);
        }

        /* Progress Bar */
        .summary-progress-bar {
            background: var(--color-background-tertiary);
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            margin: 6px 0;
        }

        .summary-progress-fill {
            height: 100%;
            background: var(--color-red-accent);
            border-radius: 3px;
        }

        /* IPP Card - Vertical Layout */
        .summary-card-ipp {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .summary-card-ipp-left { }
        .summary-card-ipp-right { }

        .summary-card-ipp-value {
            font-size: 34px;
            font-weight: 700;
            line-height: 1;
            color: var(--color-red-dark);
            margin: 8px 0;
        }

        /* IPP Breakdown list */
        .summary-ipp-breakdown {
            font-size: 12px;
            color: var(--color-text-primary);
            font-weight: 500;
        }

        .summary-ipp-breakdown-item {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 6px 0;
        }

        .summary-ipp-breakdown-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .summary-ipp-breakdown-dot.dot-teal { background: var(--color-teal-accent); }
        .summary-ipp-breakdown-dot.dot-red { background: var(--color-red-accent); }

        /* Grid Layouts */
        .summary-grid-4col {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 10px;
        }

        @media (min-width: 760px) {
            .summary-grid-4col {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1080px) {
            .summary-grid-4col {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .summary-grid-status {
            gap: 8px;
        }

        @media (min-width: 1200px) {
            .summary-grid-status {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }

            .summary-grid-status .summary-card-new {
                padding: 12px 12px;
            }

            .summary-grid-status .summary-card-label {
                font-size: 11px;
                margin-bottom: 5px;
            }

            .summary-grid-status .summary-card-value {
                font-size: 26px;
                margin-bottom: 3px;
            }

            .summary-grid-status .summary-card-subtext {
                font-size: 11px;
            }
        }

        .summary-grid-penilaian {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 10px;
        }

        @media (min-width: 760px) {
            .summary-grid-penilaian {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .summary-grid-penilaian {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .summary-grid-penilaian .summary-card-ipp-container {
            grid-column: span 1;
        }

        @media (min-width: 1200px) {
            .summary-grid-penilaian .summary-card-ipp-container {
                grid-column: span 1;
            }
        }

        .summary-grid-4col-top {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        @media (min-width: 760px) {
            .summary-grid-4col-top {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .summary-grid-4col-top {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .summary-grid-3col-bottom {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        @media (min-width: 760px) {
            .summary-grid-3col-bottom {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .summary-grid-3col-bottom {
                grid-template-columns: 1fr 1fr 2fr;
            }
        }

        .summary-grid-3col-bottom > :nth-child(3) {
            grid-column: auto;
        }

        @media (min-width: 1200px) {
            .summary-grid-3col-bottom > :nth-child(3) {
                grid-column: span 1;
            }
        }

        .summary-grid-2col {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 10px;
        }

        @media (min-width: 760px) {
            .summary-grid-2col {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .summary-grid-2col {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Section Divider */
        .summary-section-divider {
            height: 1px;
            background: var(--color-border-tertiary);
            margin: 16px 0 14px;
        }

        .summary-detail-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.46);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 16px;
        }

        .summary-detail-modal-overlay.active {
            display: flex;
        }

        .summary-detail-modal {
            width: min(980px, 100%);
            max-height: 88vh;
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 50px rgba(2, 6, 23, 0.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .summary-detail-head {
            padding: 16px 18px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .summary-detail-title {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 800;
        }

        .summary-detail-subtitle {
            margin-top: 4px;
            font-size: 0.84rem;
            opacity: 0.92;
        }

        .summary-detail-close {
            border: none;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            border-radius: 8px;
            width: 34px;
            height: 34px;
            font-size: 1.2rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .summary-detail-content {
            padding: 12px 14px 16px;
            overflow: auto;
        }

        .summary-detail-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 620px;
        }

        .summary-detail-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            text-align: left;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-detail-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eef2f7;
            color: #334155;
            font-size: 0.9rem;
            vertical-align: top;
        }

        .summary-detail-empty {
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-size: 0.92rem;
        }
        .summary-orange::before { background: #f97316; }
        .summary-green::before { background: #16a34a; }

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

            .btn-export {
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

        .upp-name-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .upp-number-badge {
            min-width: 26px;
            height: 26px;
            padding: 0 6px;
            border-radius: 999px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .upp-label-text {
            font-size: 0.97rem;
            color: #374151;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        /* Detail Button Styling */
        .btn-detail {
            padding: 6px 12px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-detail:hover {
            background: #4338ca;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
        }

        .btn-detail:active {
            background: #3f2dcc;
            transform: scale(0.98);
        }

        /* Indicator Detail Modal Overlay */
        .indicator-detail-overlay {
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .indicator-detail-modal {
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

        /* Ensure no horizontal scroll */
        html, body {
            overflow-x: hidden;
            max-width: 100%;
            width: 100%;
        }
    </style>
    <!-- Header -->
    <div class="bg-white border-b border-e5e7eb sticky top-0 z-10 shadow-sm">
        <div class="container">
            <div class="dash-header">
                <h1>📊 Dashboard Analisis Penilaian</h1>
                <p>Data F02, F03, dan IPP per Unit Pelayanan Publik</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <!-- Filter Section - Terpisah -->
    <div class="filter-section">
        <div class="container">
            {{-- Filter Button Section --}}
            <div class="action-buttons">
                <div class="action-buttons-left">
                    <select wire:model.live="periode_id" class="btn-filter" style="width: auto; appearance: auto;">
                        <option value="">Semua Periode</option>
                        @foreach($periode_options as $option)
                            <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>

                    <button type="button" class="btn-filter" id="openUppModal">
                        <i class="fas fa-search"></i> Filter UPP
                    </button>
                    <button type="button"
                            class="btn-export"
                            wire:click="exportF02ValidationExcelZip"
                            wire:loading.attr="disabled"
                            wire:target="exportF02ValidationExcelZip">
                        <span wire:loading.remove wire:target="exportF02ValidationExcelZip">
                            <i class="fas fa-file-excel"></i> Export F02 Excel (ZIP)
                        </span>
                        <span wire:loading wire:target="exportF02ValidationExcelZip">
                            Menyiapkan ZIP Excel...
                        </span>
                    </button>
                    <button type="button"
                            class="btn-filter"
                            id="clearFilterBtn"
                            onclick="handleClearFilter(event)"
                            style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; display: none;">
                        <i class="fas fa-times"></i> Hapus Filter
                    </button>
                </div>

                <div class="action-info">
                    <span id="displayInfoText">
                        @if(empty($upp_id))
                            Menampilkan semua {{ count($upp_options) }} UPP
                        @elseif(count($upp_id) === 1)
                            Menampilkan 1 dari {{ count($upp_options) }} UPP
                        @else
                            Menampilkan {{ count($upp_id) }} dari {{ count($upp_options) }} UPP
                        @endif
                    </span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0;">
                <!-- SEKSI 1: STATUS PENGISIAN -->
                <span class="summary-section-label">Status Pengisian</span>
                <div class="summary-grid-4col summary-grid-status">
                    <!-- Card 1: Total Unit Pelayanan -->
                    <div class="summary-card-new summary-card-accent accent-gray" role="button" tabindex="0" onclick="openSummaryDetail('total_upp')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('total_upp');}">
                        <span class="summary-card-label">Total Unit Pelayanan</span>
                        <span class="summary-card-value color-gray">{{ number_format((int) ($summary_cards['total_upp'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">UPP dalam cakupan filter aktif</span>
                    </div>

                    <!-- Card 2: Sudah Submit -->
                    <div class="summary-card-new summary-card-accent accent-blue" role="button" tabindex="0" onclick="openSummaryDetail('sudah_submit')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('sudah_submit');}">
                        <span class="summary-card-label">Sudah Submit</span>
                        <span class="summary-card-value color-blue">{{ number_format((int) ($summary_cards['sudah_submit'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Pengisian F01 terkirim</span>
                    </div>

                    <!-- Card 3: Belum Validasi -->
                    <div class="summary-card-new summary-card-accent accent-amber" role="button" tabindex="0" onclick="openSummaryDetail('belum_validasi')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('belum_validasi');}">
                        <span class="summary-card-label">Belum Validasi</span>
                        <span class="summary-card-value color-amber">{{ number_format((int) ($summary_cards['belum_validasi'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Menunggu validasi F02</span>
                    </div>

                    <!-- Card 4: Belum Submit -->
                    <div class="summary-card-new summary-card-accent accent-red" role="button" tabindex="0" onclick="openSummaryDetail('belum_submit')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('belum_submit');}">
                        <span class="summary-card-label">Belum Submit</span>
                        <span class="summary-card-value color-red">{{ number_format((int) ($summary_cards['belum_submit'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">UPP yang belum mengirim F02/F01</span>
                    </div>

                    <!-- Card 5: Sudah Selesai -->
                    <div class="summary-card-new summary-card-accent accent-teal" role="button" tabindex="0" onclick="openSummaryDetail('sudah_selesai')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('sudah_selesai');}">
                        <span class="summary-card-label">Sudah Selesai</span>
                        <span class="summary-card-value color-teal">{{ number_format((int) ($summary_cards['sudah_selesai'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Validasi F02 selesai</span>
                    </div>
                </div>

                <!-- SEKSI 2: PENILAIAN & INDEKS -->
                <span class="summary-section-label" style="margin-top: 18px;">Penilaian &amp; Indeks</span>
                <div class="summary-grid-penilaian">
                    <!-- Card F02 -->
                    <div class="summary-card-new" role="button" tabindex="0" onclick="openSummaryDetail('avg_f02')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('avg_f02');}">
                        <span class="summary-card-label" style="color: var(--color-amber-dark);">Nilai F02</span>
                        <span class="summary-card-value color-amber">{{ number_format((float) ($summary_cards['avg_f02'] ?? 0), 2) }}</span>
                        <span class="summary-card-subtext">Dokumentasi (0–100)</span>
                        <div class="summary-card-divider"></div>
                        <span class="summary-badge-pill badge-amber">Kategori IPP: {{ $summary_cards['ipp_category'] ?? '-' }}</span>
                        <span class="summary-card-meta"><strong>Kontribusi IPP (75%)</strong>: {{ number_format((float) ($summary_cards['f02_contribution'] ?? 0), 2) }}</span>
                    </div>

                    <!-- Card F03 -->
                    <div class="summary-card-new" role="button" tabindex="0" onclick="openSummaryDetail('avg_f03')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('avg_f03');}">
                        <span class="summary-card-label" style="color: var(--color-blue-dark);">Nilai F03</span>
                        <span class="summary-card-value color-blue">{{ number_format((float) ($summary_cards['avg_f03'] ?? 0), 2) }}</span>
                        <span class="summary-card-subtext">Survey kepuasan (1–5) · {{ number_format((int) ($summary_cards['f03_response_count'] ?? 0)) }} responden</span>
                        <div class="summary-card-divider"></div>
                        <span class="summary-card-meta"><strong>Kontribusi IPP (25%)</strong>: {{ number_format((float) ($summary_cards['f03_contribution'] ?? 0), 2) }}</span>
                        @if((int) ($summary_cards['f03_under_minimum_upp_count'] ?? 0) > 0)
                            <span class="summary-card-meta" style="color: var(--color-text-tertiary); font-size: 10px;">UPP di bawah minimum {{ (int) ($summary_cards['f03_minimum_target'] ?? 0) }} responden (skor efektif 0)</span>
                        @endif
                    </div>

                    <!-- Card IPP - Span 2 kolom -->
                    <div class="summary-card-new summary-card-ipp-container" role="button" tabindex="0" onclick="openSummaryDetail('avg_ipp')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('avg_ipp');}">
                        <div class="summary-card-ipp">
                            <!-- Left Column: IPP Value & Badge -->
                            <div class="summary-card-ipp-left">
                                <span class="summary-card-label" style="color: var(--color-red-dark);">Indeks Pelayanan Publik (IPP)</span>
                                <span class="summary-card-ipp-value">{{ number_format((float) ($summary_cards['avg_ipp'] ?? 0), 2) }}</span>
                                <span class="summary-badge-pill badge-red">{{ $summary_cards['ipp_category'] ?? '-' }} — {{ substr($summary_cards['ipp_category_label'] ?? '', strpos($summary_cards['ipp_category_label'] ?? '', '–') + 2) }}</span>
                            </div>

                            <!-- Right Column: Progress & Breakdown -->
                            <div class="summary-card-ipp-right">
                                <div style="font-size: 10px; color: var(--color-text-tertiary); margin-bottom: 4px;">0 · · · · 5.00</div>
                                <div class="summary-progress-bar">
                                    <div class="summary-progress-fill" style="width: {{ min(((float) ($summary_cards['avg_ipp'] ?? 0)) / 5.00 * 100, 100) }}%;"></div>
                                </div>
                                <div style="font-size: 10px; color: var(--color-text-secondary); margin: 6px 0;">{{ number_format((float) ($summary_cards['avg_ipp'] ?? 0), 2) }} / 5.00 ({{ number_format(((float) ($summary_cards['avg_ipp'] ?? 0)) / 5.00 * 100, 1) }}%)</div>
                                <div class="summary-ipp-breakdown">
                                    <div class="summary-ipp-breakdown-item">
                                        <span class="summary-ipp-breakdown-dot dot-teal"></span>
                                        <span>UPP Baik (≥ 3.01): <strong>{{ number_format((int) ($summary_cards['upp_baik'] ?? 0)) }}</strong></span>
                                    </div>
                                    <div class="summary-ipp-breakdown-item">
                                        <span class="summary-ipp-breakdown-dot dot-red"></span>
                                        <span>Prioritas Pembinaan (≤ 2.00): <strong>{{ number_format((int) ($summary_cards['ipp_prioritas_pembinaan'] ?? 0)) }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEKSI 3: DISTRIBUSI KATEGORI IPP -->
                <div class="summary-section-divider"></div>
                <span class="summary-section-label">Distribusi Kategori IPP</span>

                <!-- Baris 1: 4 Kategori Atas (A, A-, B, B-) -->
                <div class="summary-grid-4col-top">
                    <!-- A: Pelayanan Prima -->
                    <div class="summary-card-new summary-card-accent" style="border-left-color: var(--color-purple-accent);" role="button" tabindex="0" onclick="openSummaryDetail('ipp_a_prima')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('ipp_a_prima');}">
                        <span class="summary-card-label" style="color: var(--color-purple-dark);">PELAYANAN PRIMA</span>
                        <span class="summary-card-value color-purple">{{ number_format((int) ($summary_cards['ipp_a_prima'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Kategori A · IPP &gt; 4.50</span>
                    </div>

                    <!-- A-: Sangat Baik -->
                    <div class="summary-card-new summary-card-accent" style="border-left-color: var(--color-blue-accent);" role="button" tabindex="0" onclick="openSummaryDetail('ipp_a_minus')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('ipp_a_minus');}">
                        <span class="summary-card-label" style="color: var(--color-blue-dark);">SANGAT BAIK</span>
                        <span class="summary-card-value color-blue">{{ number_format((int) ($summary_cards['ipp_a_minus'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Kategori A− · 4.01–4.50</span>
                    </div>

                    <!-- B: Baik -->
                    <div class="summary-card-new summary-card-accent" style="border-left-color: var(--color-teal-accent);" role="button" tabindex="0" onclick="openSummaryDetail('ipp_b')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('ipp_b');}">
                        <span class="summary-card-label" style="color: var(--color-teal-dark);">BAIK</span>
                        <span class="summary-card-value color-teal">{{ number_format((int) ($summary_cards['ipp_b'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Kategori B · 3.51–4.00</span>
                    </div>

                    <!-- B-: Baik Dengan Catatan -->
                    <div class="summary-card-new summary-card-accent" style="border-left-color: var(--color-teal-accent);" role="button" tabindex="0" onclick="openSummaryDetail('ipp_b_minus')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('ipp_b_minus');}">
                        <span class="summary-card-label" style="color: var(--color-teal-dark);">BAIK DENGAN CATATAN</span>
                        <span class="summary-card-value color-teal">{{ number_format((int) ($summary_cards['ipp_b_minus'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Kategori B− · 3.01–3.50</span>
                    </div>
                </div>

                <!-- Baris 2: 3 Kategori Bawah (C, C-, Prioritas Pembinaan) -->
                <div class="summary-grid-3col-bottom">
                    <!-- C: Cukup -->
                    <div class="summary-card-new summary-card-accent" style="border-left-color: var(--color-amber-accent);" role="button" tabindex="0" onclick="openSummaryDetail('ipp_c')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('ipp_c');}">
                        <span class="summary-card-label" style="color: var(--color-amber-dark);">CUKUP</span>
                        <span class="summary-card-value color-amber">{{ number_format((int) ($summary_cards['ipp_c'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Kategori C · 2.51–3.00</span>
                    </div>

                    <!-- C-: Cukup Dengan Catatan -->
                    <div class="summary-card-new summary-card-accent" style="border-left-color: var(--color-amber-accent);" role="button" tabindex="0" onclick="openSummaryDetail('ipp_c_minus')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('ipp_c_minus');}">
                        <span class="summary-card-label" style="color: var(--color-amber-dark);">CUKUP DENGAN CATATAN</span>
                        <span class="summary-card-value color-amber">{{ number_format((int) ($summary_cards['ipp_c_minus'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Kategori C− · 2.01–2.50</span>
                    </div>

                    <!-- Prioritas Pembinaan -->
                    <div class="summary-card-new summary-card-accent" style="border-left-color: var(--color-red-accent);" role="button" tabindex="0" onclick="openSummaryDetail('ipp_prioritas_pembinaan')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('ipp_prioritas_pembinaan');}">
                        <span class="summary-card-label" style="color: var(--color-red-dark);">PRIORITAS PEMBINAAN</span>
                        <span class="summary-card-value color-red">{{ number_format((int) ($summary_cards['ipp_prioritas_pembinaan'] ?? 0)) }}</span>
                        <span class="summary-card-subtext">Kategori D, E, F · IPP ≤ 2.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Filter Section -->

    <!-- Charts Section - Terpisah -->

    <!-- Debug: Show current state - Updated on every Livewire render -->
    <div style="display: none;"
         id="debugUppId"
         wire:key="debug-upp-id"
            data-upp-id='@json($upp_id ?? [])'
         data-f02-count="{{ count($f02_data) }}"
         data-f02-labels='@json($f02_labels)'
         data-f02-data='@json($f02_data)'
         data-f03-labels='@json($f03_labels)'
         data-f03-data='@json($f03_data)'
         data-ipp-labels='@json($ipp_labels)'
         data-ipp-data='@json($ipp_data)'
            data-aspek-ids='@json($aspek_ids)'
         data-aspek-labels='@json($aspek_labels)'
         data-aspek-values='@json($aspek_values)'
         data-f03-aspek-ids='@json($f03_aspek_ids ?? [])'
         data-f03-aspek-labels='@json($f03_aspek_labels ?? [])'
         data-f03-aspek-values='@json($f03_aspek_values ?? [])'>
    </div>

    {{-- Modal Filter UPP --}}
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
                        Pilih Semua ({{ count($upp_options) }} UPP)
                    </label>
                </div>
                <div class="select-all-check" style="margin-top: -8px; background: #f0fdf4; border-color: #bbf7d0;">
                    <input class="form-check-input" type="checkbox" id="selectSubmittedUpp">
                    <label class="form-check-label" for="selectSubmittedUpp" style="color: #166534;">
                        Pilih UPP Sudah Submit Saja
                    </label>
                </div>
                <div id="uppChecklistContainer">
                    @foreach($upp_options as $upp)
                        <div class="form-check">
                            <input class="form-check-input upp-checkbox" type="checkbox"
                                id="upp_{{ $upp['id'] }}"
                                value="{{ $upp['id'] }}"
                                data-label="{{ strtolower($upp['validation_label'] ?? '') }}"
                                data-status="{{ $upp['validation_status'] ?? 'belum_submit' }}"
                                {{ in_array($upp['id'], $upp_id ?? [], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="upp_{{ $upp['id'] }}" style="display: flex; justify-content: space-between; width: 100%; align-items: center; gap: 10px;">
                                <span class="upp-name-wrap">
                                    <span class="upp-number-badge">{{ $loop->iteration }}</span>
                                    <span class="upp-label-text">{{ $upp['label'] }}</span>
                                </span>
                                <span style="display: inline-flex; gap: 6px; align-items: center;">
                                    <span style="font-weight: bold; color: #166534; font-size: 0.85rem; background: #dcfce7; padding: 2px 8px; border-radius: 4px;">IPP: {{ number_format((float) ($upp['ipp_value'] ?? 0), 2) }}</span>
                                    <span class="status-badge" style="font-weight: 700; color: {{ $upp['validation_color'] ?? '#b45309' }}; font-size: 0.75rem; background: {{ $upp['validation_bg'] ?? '#fef3c7' }}; padding: 2px 8px; border-radius: 999px;">{{ $upp['validation_label'] ?? 'Belum Validasi' }}</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-cancel" id="closeModalBtn">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn-modal btn-modal-submit" id="submitUppFilter" onclick="handleSubmitUppFilter(event)">
                    <i class="fas fa-check"></i> Tampilkan Data
                </button>
            </div>
        </div>
    </div>

    <div class="summary-detail-modal-overlay" id="summaryDetailModalOverlay">
        <div class="summary-detail-modal">
            <div class="summary-detail-head">
                <div>
                    <h4 class="summary-detail-title" id="summaryDetailTitle">Detail Ringkasan</h4>
                    <div class="summary-detail-subtitle" id="summaryDetailSubtitle">Daftar UPP</div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="downloadTableJpg('summaryDetailTable', 'Detail_Ringkasan.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                    <button type="button" onclick="downloadTableCsv('summaryDetailTable', 'Detail_Ringkasan.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                    <button type="button" class="summary-detail-close" id="summaryDetailCloseBtn">&times;</button>
                </div>
            </div>
            <div class="summary-detail-content">
                <table class="summary-detail-table" id="summaryDetailTable">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>UPP</th>
                            <th style="width: 150px;" id="summaryMetricColHead">Nilai</th>
                            <th style="width: 280px;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="summaryDetailTableBody"></tbody>
                </table>
                <div class="summary-detail-empty" id="summaryDetailEmpty" style="display: none;">Tidak ada data UPP untuk kategori ini.</div>
            </div>
        </div>
    </div>

    @if($aspek_detail_visible && $aspek_detail)
        <div wire:key="aspekDetailModal-{{ $aspek_detail['aspek']['id'] }}" class="summary-detail-modal-overlay active" id="aspekDetailOverlay" style="display: flex; z-index: 1002;">
            <div class="summary-detail-modal" style="width: min(1120px, 100%);">
                <div class="summary-detail-head" style="background: linear-gradient(135deg, #0f766e, #0ea5e9);">
                    <div>
                        <h4 class="summary-detail-title">Distribusi Skor Aspek: {{ $aspek_detail['aspek']['nama'] }}</h4>
                        <div class="summary-detail-subtitle">Kelompok UPP per skor 0-5 (berdasarkan pembulatan skor aspek rata-rata)</div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="downloadTableJpg('aspekDetailTable', 'Distribusi_Skor_Aspek.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                        <button type="button" onclick="downloadTableCsv('aspekDetailTable', 'Distribusi_Skor_Aspek.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                        <button type="button" class="summary-detail-close" wire:click="closeAspekDetail()">&times;</button>
                    </div>
                </div>

                <div class="summary-detail-content">
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                        <div style="padding: 8px 10px; border-radius: 8px; background: #ecfeff; color: #0f766e; font-size: 0.82rem;">
                            Total UPP: <strong>{{ (int) ($aspek_detail['distribution']['total_upp'] ?? 0) }}</strong>
                        </div>
                        <div style="padding: 8px 10px; border-radius: 8px; background: #f1f5f9; color: #334155; font-size: 0.82rem;">
                            Bobot Aspek: <strong>{{ rtrim(rtrim(number_format((float) ($aspek_detail['aspek']['bobot'] ?? 0), 2, '.', ''), '0'), '.') }}%</strong>
                        </div>
                        <div style="padding: 8px 10px; border-radius: 8px; background: #f8fafc; color: #475569; font-size: 0.82rem;">
                            {{ $aspek_detail['distribution']['bucket_method'] ?? 'Skor aspek dibulatkan ke skor terdekat (0-5).' }}
                        </div>
                    </div>

                    @if((int) ($aspek_detail['distribution']['total_upp'] ?? 0) > 0)
                        <table class="summary-detail-table" style="min-width: 920px;" id="aspekDetailTable">
                            <thead>
                                <tr>
                                    <th style="width: 90px;">Skor</th>
                                    <th style="width: 220px;">Predikat</th>
                                    <th style="width: 110px; text-align: center;">Jml UPP</th>
                                    <th style="width: 100px; text-align: center;">%</th>
                                    <th>Daftar UPP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($aspek_detail['distribution']['scores'] ?? []) as $scoreGroup)
                                    <tr>
                                        <td>
                                            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; padding: 4px 10px; border-radius: 999px; background: {{ ($scoreGroup['color'] ?? '#334155') }}1f; color: {{ $scoreGroup['color'] ?? '#334155' }}; font-weight: 700;">
                                                {{ $scoreGroup['skor'] }}
                                            </span>
                                        </td>
                                        <td style="font-weight: 600; color: #334155;">{{ $scoreGroup['predikat'] ?? '-' }}</td>
                                        <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ (int) ($scoreGroup['upp_count'] ?? 0) }}</td>
                                        <td style="text-align: center; color: #475569;">{{ number_format((float) ($scoreGroup['percentage'] ?? 0), 1) }}%</td>
                                        <td>
                                            @if(!empty($scoreGroup['upp_rows']))
                                                <div style="display: flex; flex-wrap: wrap; gap: 6px; max-height: 120px; overflow-y: auto;">
                                                    @foreach($scoreGroup['upp_rows'] as $uppRow)
                                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-size: 0.78rem;">
                                                            <span>{{ $uppRow['upp_label'] }}</span>
                                                            <strong style="color: #0f766e;">{{ number_format((float) ($uppRow['skor_aspek_raw'] ?? 0), 2) }}</strong>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span style="color: #94a3b8; font-size: 0.82rem;">Tidak ada UPP pada kelompok skor ini.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="summary-detail-empty">Tidak ada data UPP untuk aspek ini pada filter aktif.</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Indicator Detail Modal -->
    @if($indicator_detail_visible && $indicator_detail)
        <div wire:key="indicatorDetailModal-{{ $indicator_detail['indikator']['id'] }}" class="indicator-detail-overlay" id="indicatorDetailOverlay" style="display: flex; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.46); z-index: 1001; justify-content: center; align-items: center; padding: 16px;">
            <div class="indicator-detail-modal" style="width: min(1200px, 100%); background: #ffffff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 20px 50px rgba(2, 6, 23, 0.25); display: flex; flex-direction: column; overflow: hidden;">

                <!-- Header -->
                <div style="padding: 16px 18px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div>
                        <h4 style="margin: 0; font-size: 1.02rem; font-weight: 800;">{{ $indicator_detail['indikator']['nama'] }}</h4>
                        <div style="margin-top: 4px; font-size: 0.84rem; opacity: 0.92;">{{ $indicator_detail['aspek']['nama'] }}</div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="downloadTableJpg('indicatorDetailTable', 'Detail_Indikator.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                        <button type="button" onclick="downloadTableCsv('indicatorDetailTable', 'Detail_Indikator.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                        <button type="button" wire:click="closeIndicatorDetail()" style="border: none; background: rgba(255, 255, 255, 0.18); color: #fff; border-radius: 8px; width: 34px; height: 34px; font-size: 1.2rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">&times;</button>
                    </div>
                </div>

                <!-- Body: 2-column grid + footer -->
                <div style="display: flex; flex-direction: column;">
                    @if($indicator_detail['distribution']['total_upp'] > 0)
                        <!-- Main Grid: Chart (left) | Table (right) -->
                        <div style="padding: 16px 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">

                            <!-- Pie Chart - Column 1 -->
                            <div style="display: flex; flex-direction: column; width: 100%; background: #f9fafb; border-radius: 8px; border: 1px solid #ddd; padding: 16px;">
                                <div style="display: flex; justify-content: space-between; width: 100%; padding-bottom: 8px; border-bottom: 1px solid #ddd; margin-bottom: 8px;">
                                    <span style="font-weight: 600; font-size: 0.85rem;">Distribusi Skor</span>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" onclick="downloadChartJpg('indicatorPieChart', 'Distribusi_Skor.jpg')" class="btn-detail" style="background: #10b981; padding: 2px 6px; font-size: 0.65rem;">⬇ JPG</button>
                                        <button type="button" onclick="downloadChartCsv('indicatorPieChart', 'Distribusi_Skor.csv')" class="btn-detail" style="background: #f59e0b; padding: 2px 6px; font-size: 0.65rem;">⬇ CSV</button>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: center; align-items: center; min-height: 260px;">
                                    <canvas id="indicatorPieChart" width="260" height="260" data-chart-labels='@json($indicator_detail['distribution']['chart_data']['labels'] ?? [])' data-chart-data='@json($indicator_detail['distribution']['chart_data']['data'] ?? [])' data-chart-colors='@json($indicator_detail['distribution']['chart_data']['backgroundColor'] ?? [])'></canvas>
                                </div>
                            </div>

                            <!-- Scores Table - Column 2 -->
                            <div>
                                <table id="indicatorDetailTable" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                                    <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th style="text-align: left; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #475569; font-size: 0.78rem;">Skor</th>
                                            <th style="text-align: left; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #475569; font-size: 0.78rem;">Narasi</th>
                                            <th style="text-align: center; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #475569; font-size: 0.78rem;">Jml UPP</th>
                                            <th style="text-align: center; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #475569; font-size: 0.78rem;">%</th>
                                            <th style="text-align: center; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #475569; font-size: 0.78rem;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($indicator_detail['distribution']['scores'] as $scoreRow)
                                            <tr style="background: {{ $selected_score_for_upp === $scoreRow['skor'] ? '#f0f4ff' : '#ffffff' }}; border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                                                <td style="padding: 10px 8px; color: {{ $scoreRow['color'] }}; font-weight: 700; font-size: 0.9rem;">{{ $scoreRow['skor'] }}</td>
                                                <td style="padding: 10px 8px; color: #475569; font-size: 0.75rem; max-width: 180px; word-wrap: break-word; white-space: normal; line-height: 1.2;" title="{{ $scoreRow['narasi'] }}">{{ $scoreRow['narasi'] }}</td>
                                                <td style="padding: 10px 8px; text-align: center; color: #334155; font-weight: 600;">{{ $scoreRow['upp_count'] }}</td>
                                                <td style="padding: 10px 8px; text-align: center; color: #334155; font-weight: 600;">{{ $scoreRow['percentage'] }}%</td>
                                                <td style="padding: 10px 8px; text-align: center;">
                                                    <button type="button" wire:click="selectScoreForUpp({{ $scoreRow['skor'] }})" style="padding: 4px 8px; background: #4f46e5; color: white; border: none; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                                                        Lihat
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- End Grid -->

                        <!-- UPP List Footer (conditional display) - Outside Grid -->
                        @if($selected_score_for_upp !== null)
                            @php
                                $selectedScoreData = collect($indicator_detail['distribution']['scores'])->firstWhere('skor', $selected_score_for_upp);
                            @endphp
                            @if($selectedScoreData)
                                <div style="border-top: 1px solid #e5e7eb; padding: 14px 18px; background: #f9fafb; flex-shrink: 0;">
                                    <div style="font-size: 0.9rem; font-weight: 600; color: #1f2937; margin-bottom: 10px;">
                                        Daftar UPP — Skor {{ $selectedScoreData['skor'] }} ({{ $selectedScoreData['predikat'] }})
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px; max-height: 150px; overflow-y: auto;">
                                        @if(!empty($selectedScoreData['upp_list']))
                                            @foreach($selectedScoreData['upp_list'] as $upp)
                                                <span style="display: inline-block; padding: 6px 12px; background: {{ $selectedScoreData['color'] }}20; border: 1px solid {{ $selectedScoreData['color'] }}; color: #334155; border-radius: 6px; font-size: 0.8rem; font-weight: 500;">
                                                    {{ $upp->upp_kode }} - {{ $upp->upp_nama }}
                                                </span>
                                            @endforeach
                                        @else
                                            <div style="color: #9ca3af; font-size: 0.85rem;">Tidak ada UPP untuk skor ini.</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        <div style="padding: 32px 18px; text-align: center; color: #9ca3af; flex: 1; display: flex; align-items: center; justify-content: center;">
                            <p>Tidak ada data UPP untuk indikator ini.</p>
                        </div>
                    @endif
                </div>
                <!-- End Body -->

            </div>
        </div>
    @endif
    @script
    <script>
        // ====================================================================
        // Pie Chart for Indicator Detail Modal
        // Pushed to stack('scripts') which loads after livewireScripts,
        // so Livewire is guaranteed to be available here.
        // ====================================================================

        function _createPieChart(canvas) {
            if (!canvas || typeof Chart === 'undefined') return;

            var labels, data, colors;
            try {
                labels = JSON.parse(canvas.getAttribute('data-chart-labels') || '[]');
                data   = JSON.parse(canvas.getAttribute('data-chart-data')   || '[]');
                colors = JSON.parse(canvas.getAttribute('data-chart-colors') || '[]');
            } catch (e) {
                console.error('[PieChart] Parse error:', e);
                return;
            }

            if (!labels.length || !data.length) {
                console.warn('[PieChart] No data to render');
                return;
            }

            // Destroy previous instance
            if (window._indicatorPieChart) {
                try { window._indicatorPieChart.destroy(); } catch(e) {}
                window._indicatorPieChart = null;
            }

            // Also destroy via Chart.js registry (handles canvas replacement by Livewire morph)
            try {
                var existing = Chart.getChart(canvas);
                if (existing) existing.destroy();
            } catch(e) {}

            try {
                window._indicatorPieChart = new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { size: 10 }, padding: 10, boxWidth: 8 }
                            }
                        }
                    }
                });
                console.log('[PieChart] ✅ Created');
            } catch (e) {
                console.error('[PieChart] ❌ Failed:', e);
            }
        }

        function _tryInitPieChart() {
            var canvas = document.getElementById('indicatorPieChart');
            if (canvas) {
                _createPieChart(canvas);
            }
        }

        // Livewire is available here because stack('scripts') loads after livewireScripts
        Livewire.on('initChart', function() {
            console.log('[PieChart] 🔔 initChart event received');
            setTimeout(_tryInitPieChart, 400);
        });

        // Fallback: Hook into Livewire morph cycle
        Livewire.hook('morph.updated', ({ el }) => {
            if (el.id === 'indicatorPieChart' || (el.querySelector && el.querySelector('#indicatorPieChart'))) {
                setTimeout(_tryInitPieChart, 200);
            }
        });

        Livewire.hook('morph.added', ({ el }) => {
            if (el.id === 'indicatorPieChart' || (el.querySelector && el.querySelector('#indicatorPieChart'))) {
                setTimeout(_tryInitPieChart, 200);
            }
        });

        console.log('[PieChart] Script ready');
    </script>
    @endscript

    <!-- Chart 1: F02 -->
    <div class="chart-section">
        <div class="container">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span>📈</span>Skor F02
                    </h3>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="downloadChartJpg('f02Chart', 'Skor_F02.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                        <button type="button" onclick="downloadChartCsv('f02Chart', 'Skor_F02.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                        <span class="chart-card-badge" style="background: #dbeafe; color: #0369a1;">Validasi</span>
                    </div>
                </div>
                <div wire:ignore class="chart-container" style="height: 300px;">
                    <canvas id="f02Chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: F03 -->
    <div class="chart-section">
        <div class="container">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span>📊</span>Skor F03
                    </h3>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="downloadChartJpg('f03Chart', 'Skor_F03.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                        <button type="button" onclick="downloadChartCsv('f03Chart', 'Skor_F03.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                        <span class="chart-card-badge" style="background: #dcfce7; color: #166534;">Publik</span>
                    </div>
                </div>
                <div wire:ignore class="chart-container" style="height: 300px;">
                    <canvas id="f03Chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 3: IPP -->
    <div class="chart-section">
        <div class="container">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span>🎯</span>Nilai IPP
                    </h3>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="downloadChartJpg('ippChart', 'Nilai_IPP.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                        <button type="button" onclick="downloadChartCsv('ippChart', 'Nilai_IPP.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                        <span class="chart-card-badge" style="background: #e9d5ff; color: #6b21a8;">Akhir</span>
                    </div>
                </div>
                <div wire:ignore class="chart-container" style="height: 300px;">
                    <canvas id="ippChart"></canvas>
                </div>
                <!-- Legend for IPP Predikats -->
                <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; font-size: 0.8rem; color: #4b5563; font-weight: 500;">
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #9333EA; display: inline-block;"></span> Pelayanan Prima</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #2563EB; display: inline-block;"></span> Sangat Baik</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #16A34A; display: inline-block;"></span> Baik</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #0891B2; display: inline-block;"></span> Baik Dengan Catatan</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #EAB308; display: inline-block;"></span> Cukup</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #EA580C; display: inline-block;"></span> Kurang</div>
                    <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #DC2626; display: inline-block;"></span> Prioritas Pembinaan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 4: Aspek -->
    <div class="chart-section">
        <div class="container" style="display: grid; gap: 18px; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span>📋</span>Total Nilai Indikator F02 per Aspek
                    </h3>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="downloadChartJpg('aspekChart', 'Total_Nilai_Indikator_F02_per_Aspek.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                        <button type="button" onclick="downloadChartCsv('aspekChart', 'Total_Nilai_Indikator_F02_per_Aspek.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                        <span class="chart-card-badge" style="background: #fed7aa; color: #92400e;">
                            @if(empty($upp_id))
                                Agregasi All UPP
                            @elseif(count($upp_id) === 1)
                                Filter 1 UPP
                            @else
                                Filter {{ count($upp_id) }} UPP
                            @endif
                        </span>
                    </div>
                </div>
                <div wire:ignore class="chart-container" style="height: 300px;">
                    <canvas id="aspekChart"></canvas>
                </div>
                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #f0f0f0; font-size: 0.78rem; color: #64748b; line-height: 1.5;">
                    💡 Klik titik radar untuk melihat pembagian kelompok UPP berdasarkan skor 0–5 pada aspek yang dipilih.
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span>📋</span>Total Nilai Aspek F03
                    </h3>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" onclick="downloadChartJpg('f03AspekChart', 'Total_Nilai_Aspek_F03.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                        <button type="button" onclick="downloadChartCsv('f03AspekChart', 'Total_Nilai_Aspek_F03.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                        <span class="chart-card-badge" style="background: #dcfce7; color: #166534;">
                            @if(empty($upp_id))
                                Agregasi All UPP
                            @elseif(count($upp_id) === 1)
                                Filter 1 UPP
                            @else
                                Filter {{ count($upp_id) }} UPP
                            @endif
                        </span>
                    </div>
                </div>
                <div wire:ignore class="chart-container" style="height: 340px;">
                    <canvas id="f03AspekChart"></canvas>
                </div>
                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #f0f0f0; font-size: 0.78rem; color: #64748b; line-height: 1.5;">
                    💡 Radar chart ini menampilkan rata-rata skor tiap aspek F03 dari seluruh UPP (agregasi semua indikator per aspek).
                </div>
            </div>
        </div>

        <!-- Skor Indikator per Aspek sub-section -->
        <div style="margin-top: 24px; background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">

            <!-- Sub-section header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1rem;">📊</span>
                    <span style="font-size: 0.95rem; font-weight: 700; color: #1f2937;">Skor Indikator per Aspek</span>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="downloadTableJpg('tabelSkorIndikator', 'Skor_Indikator.jpg')" class="btn-detail" style="background: #10b981;"><i class="fas fa-image"></i> JPG</button>
                    <button type="button" onclick="downloadTableCsv('tabelSkorIndikator', 'Skor_Indikator.csv')" class="btn-detail" style="background: #f59e0b;"><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>

            @if(!empty($aspek_tabs))
                @php
                    $selectedAspekTab = collect($aspek_tabs)->firstWhere('id', (int) $selected_aspek_id);
                @endphp

                <!-- Aspek tab buttons -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
                    @foreach($aspek_tabs as $aspekTab)
                        <button
                            type="button"
                            wire:click="selectAspek({{ $aspekTab['id'] }})"
                            style="padding: 7px 14px; border-radius: 8px; border: 1px solid {{ (int) $selected_aspek_id === (int) $aspekTab['id'] ? '#4f46e5' : '#d1d5db' }}; background: {{ (int) $selected_aspek_id === (int) $aspekTab['id'] ? '#eef2ff' : '#ffffff' }}; color: {{ (int) $selected_aspek_id === (int) $aspekTab['id'] ? '#3730a3' : '#374151' }}; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                            {{ $aspekTab['nama'] }}
                        </button>
                    @endforeach
                </div>

                @if(!empty($selectedAspekTab))
                    <!-- Aspek meta info badges -->
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
                        <div style="padding: 8px 12px; background: #f1f5f9; color: #334155; border-radius: 8px; font-size: 0.82rem; border: 1px solid #e2e8f0;">
                            Bobot Aspek: <strong>{{ rtrim(rtrim(number_format((float) ($selectedAspekTab['bobot_aspek'] ?? 0), 2, '.', ''), '0'), '.') }}%</strong>
                        </div>
                        <div style="padding: 8px 12px; background: #ecfeff; color: #0f766e; border-radius: 8px; font-size: 0.82rem; border: 1px solid #99f6e4;">
                            Total Skor Setelah Bobot: <strong>{{ number_format((float) ($selectedAspekTab['skor_setelah_bobot'] ?? 0), 4) }}</strong>
                        </div>
                    </div>
                @endif

                <!-- Table -->
                <div style="border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
                    <div style="max-height: 360px; overflow: auto;">
                        <table wire:key="indikator-table-{{ $selected_aspek_id }}" id="tabelSkorIndikator"
                                style="width: 100%; border-collapse: collapse;">
                            <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th style="text-align: left; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; width: 56px;">No</th>
                                    <th style="text-align: left; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb;">Nama Indikator</th>
                                    <th style="text-align: right; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; width: 100px;">Skor</th>
                                    <th style="text-align: center; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; width: 140px;">Predikat</th>
                                    <th style="text-align: center; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selected_aspek_rows as $row)
                                    <tr wire:key="aspek-row-{{ $row['indikator_id'] }}">
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; color: #334155;">{{ $row['no'] }}</td>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; color: #1f2937;">{{ $row['indikator_nama'] }}</td>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: right; font-size: 0.85rem; color: #0f766e; font-weight: 700;">{{ number_format((float) $row['indikator_skor'], 2) }}</td>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: center; font-size: 0.85rem; font-weight: 600;">
                                            @php
                                                $skor = (float) $row['indikator_skor'];
                                                if ($skor > 4.50) {
                                                    $predikat = 'Pelayanan Prima';
                                                    $predikatColor = '#7F77DD';
                                                    $predikatBg = '#f5f3ff';
                                                } elseif ($skor >= 4.01 && $skor <= 4.50) {
                                                    $predikat = 'Sangat Baik';
                                                    $predikatColor = '#185FA5';
                                                    $predikatBg = '#eef2ff';
                                                } elseif ($skor >= 3.51 && $skor <= 4.00) {
                                                    $predikat = 'Baik';
                                                    $predikatColor = '#0F6E56';
                                                    $predikatBg = '#ecfdf5';
                                                } elseif ($skor >= 3.01 && $skor <= 3.50) {
                                                    $predikat = 'Baik Dengan Catatan';
                                                    $predikatColor = '#0F6E56';
                                                    $predikatBg = '#ecfdf5';
                                                } elseif ($skor >= 2.51 && $skor <= 3.00) {
                                                    $predikat = 'Cukup';
                                                    $predikatColor = '#BA7517';
                                                    $predikatBg = '#fff7e8';
                                                } elseif ($skor >= 2.01 && $skor <= 2.50) {
                                                    $predikat = 'Kurang';
                                                    $predikatColor = '#BA7517';
                                                    $predikatBg = '#fff7e8';
                                                } else {
                                                    $predikat = 'Prioritas Pembinaan';
                                                    $predikatColor = '#A32D2D';
                                                    $predikatBg = '#fef2f2';
                                                }
                                            @endphp
                                            <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; background: {{ $predikatBg }}; color: {{ $predikatColor }}; font-size: 0.75rem;">{{ $predikat }}</span>
                                        </td>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                            <button type="button" class="btn-detail" wire:click="showIndikatorDetail({{ $row['indikator_id'] }})">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 16px 12px; text-align: center; color: #94a3b8; font-size: 0.85rem;">Belum ada data indikator untuk aspek ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div style="padding: 12px; border-radius: 8px; background: #f8fafc; color: #64748b; font-size: 0.85rem;">
                    Data aspek belum tersedia untuk filter aktif.
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
    // --- EXPORT FUNCTIONS ---
    function downloadChartJpg(chartId, filename) {
        const chart = chartInstances[chartId] || window['_' + chartId];
        if (!chart) return alert('Chart belum siap');

        // Capture the full .chart-card container so title + chart + legend are included.
        // Fall back to the canvas parent if not inside a .chart-card (e.g. pie chart in modal).
        const container = chart.canvas.closest('.chart-card')
                       || chart.canvas.closest('[style*="border-radius"]')
                       || chart.canvas.parentElement
                       || chart.canvas;

        html2canvas(container, {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true,
            logging: false
        }).then(function(canvas) {
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/jpeg', 1.0);
            link.download = filename;
            link.click();
        });
    }

    function downloadChartCsv(chartId, filename) {
        const chart = chartInstances[chartId] || window['_' + chartId];
        if (!chart) return alert('Chart belum siap');
        let csv = '';
        const labels = chart.data.labels;
        const datasets = chart.data.datasets;
        
        // Header
        csv += 'Label';
        datasets.forEach(ds => {
            csv += ',' + (ds.label || 'Value');
        });
        csv += '\n';

        // Data
        labels.forEach((label, i) => {
            // Clean label from commas if any
            let cleanLabel = String(label).replace(/,/g, '');
            csv += cleanLabel;
            datasets.forEach(ds => {
                csv += ',' + (ds.data[i] || 0);
            });
            csv += '\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
    }

    function downloadTableJpg(tableId, filename) {
        const tableElement = document.getElementById(tableId);
        if (!tableElement) return alert('Tabel tidak ditemukan');

        // Capture the full modal container (header + content) so title and subtitle are included.
        // If the table is not inside a modal, fall back to the table element itself.
        const modal = tableElement.closest('.summary-detail-modal')
                   || tableElement.closest('.indicator-detail-modal')
                   || tableElement;

        // Collect all elements with overflow/max-height constraints inside the modal
        // so we can temporarily remove them for a full (non-clipped) capture.
        const scrollable = Array.from(modal.querySelectorAll('*')).filter(el => {
            const s = window.getComputedStyle(el);
            return s.overflow === 'auto' || s.overflow === 'scroll'
                || s.overflowY === 'auto' || s.overflowY === 'scroll';
        });
        const savedModal = { overflow: modal.style.overflow, maxHeight: modal.style.maxHeight };
        const savedScrollable = scrollable.map(el => ({
            el,
            overflow: el.style.overflow,
            overflowY: el.style.overflowY,
            maxHeight: el.style.maxHeight
        }));

        // Remove constraints
        modal.style.overflow = 'visible';
        modal.style.maxHeight = 'none';
        scrollable.forEach(el => {
            el.style.overflow = 'visible';
            el.style.overflowY = 'visible';
            el.style.maxHeight = 'none';
        });

        html2canvas(modal, {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true,
            logging: false
        }).then(function(canvas) {
            // Restore original styles
            modal.style.overflow = savedModal.overflow;
            modal.style.maxHeight = savedModal.maxHeight;
            savedScrollable.forEach(({ el, overflow, overflowY, maxHeight }) => {
                el.style.overflow = overflow;
                el.style.overflowY = overflowY;
                el.style.maxHeight = maxHeight;
            });

            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/jpeg', 1.0);
            link.download = filename;
            link.click();
        }).catch(function() {
            // Always restore on error
            modal.style.overflow = savedModal.overflow;
            modal.style.maxHeight = savedModal.maxHeight;
            savedScrollable.forEach(({ el, overflow, overflowY, maxHeight }) => {
                el.style.overflow = overflow;
                el.style.overflowY = overflowY;
                el.style.maxHeight = maxHeight;
            });
        });
    }

    function downloadTableCsv(tableId, filename) {
        const tableElement = document.getElementById(tableId);
        if (!tableElement) return alert('Tabel tidak ditemukan');
        
        let csv = [];
        const rows = tableElement.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) {
                // Clean innerText
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(','));
        }

        const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
    }

    // 🧹 CLEAR FILTER HANDLER
    function handleClearFilter(event) {
        event.preventDefault();
        console.log('🧹 handleClearFilter() FIRED');

        if (!confirm('Hapus filter yang tersimpan?')) {
            return;
        }

        try {
            // Remove from localStorage
            localStorage.removeItem('analytics_filter_upp');
            console.log('✓ Filter removed from localStorage');

            // Uncheck all checkboxes
            const uppCheckboxes = document.querySelectorAll('.upp-checkbox');
            uppCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Hide clear filter button
            const clearBtn = document.getElementById('clearFilterBtn');
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }

            // Dispatch reset to Livewire (empty array = show all)
            if (window.Livewire) {
                Livewire.dispatch('setUppFilter', { upp_id: [] });
                console.log('✓ Livewire.dispatch setUppFilter called with empty array');
            }
        } catch (error) {
            console.error('❌ Error clearing filter:', error);
        }
    }

    // SIMPLE GLOBAL HANDLER for filter submission
    function handleSubmitUppFilter(event) {
        event.preventDefault();
        console.log('🎯 handleSubmitUppFilter() FIRED - onclick attribute triggered');

        const uppCheckboxes = document.querySelectorAll('.upp-checkbox');
        const selectedValues = Array.from(uppCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        console.log('✓ Selected checkboxes:', selectedValues.length);

        if (selectedValues.length === 0) {
            console.error('❌ No checkboxes selected!');
            alert('Pilih minimal satu UPP!');
            return;
        }

        const uppIds = selectedValues.map(value => parseInt(value, 10)).filter(value => !Number.isNaN(value));
        console.log('✓ Selected UPP IDs:', uppIds);
        console.log('🔄 >>> CALLING setUppFilter with upp_ids:', uppIds);

        // 💾 SAVE TO LOCALSTORAGE
        try {
            localStorage.setItem('analytics_filter_upp', JSON.stringify(uppIds));
            console.log('💾 Filter saved to localStorage:', uppIds);
        } catch (error) {
            console.warn('⚠️ Failed to save to localStorage:', error);
        }

        // Close modal
        const moduleOverlay = document.getElementById('uppFilterModal');
        if (moduleOverlay) {
            moduleOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Call Livewire method
        if (window.Livewire) {
            Livewire.dispatch('setUppFilter', { upp_id: uppIds });
            console.log('✓ Livewire.dispatch setUppFilter called with array payload');
        } else {
            console.error('❌ Livewire not available');
        }
    }

    // Initialize chart data from Blade (runs on every component render)
    window.chartDataFromServer = {
        upp_id: @json($upp_id ?? []),
        f02_labels: @json($f02_labels),
        f02_data: @json($f02_data),
        f03_labels: @json($f03_labels),
        f03_data: @json($f03_data),
        ipp_labels: @json($ipp_labels),
        ipp_data: @json($ipp_data),
        aspek_ids: @json($aspek_ids),
        aspek_labels: @json($aspek_labels),
        aspek_values: @json($aspek_values),
        f03_aspek_ids: @json($f03_aspek_ids ?? []),
        f03_aspek_labels: @json($f03_aspek_labels ?? []),
        f03_aspek_values: @json($f03_aspek_values ?? []),
        summary_cards: @json($summary_cards ?? []),
        summary_card_details: @json($summary_card_details ?? [])
    };

    window.summaryDetailsFromServer = @json($summary_card_details ?? []);

    function callAnalyticsComponentMethod(methodName, ...args) {
        if (!window.Livewire) {
            return false;
        }

        const componentElement = document.querySelector('[wire\\:id]');
        const wireId = componentElement?.getAttribute('wire:id');
        if (!wireId) {
            return false;
        }

        Livewire.find(wireId).call(methodName, ...args);
        return true;
    }

    function openAspekDetailModal(aspekId) {
        const normalizedAspekId = parseInt(aspekId, 10);
        if (Number.isNaN(normalizedAspekId) || normalizedAspekId <= 0) {
            return;
        }

        if (!callAnalyticsComponentMethod('showAspekDetail', normalizedAspekId)) {
            console.error('❌ Unable to open aspek detail modal: Livewire component not found');
        }
    }

    function closeAspekDetailModal() {
        callAnalyticsComponentMethod('closeAspekDetail');
    }

    function openSummaryDetail(summaryKey) {
        const modal = document.getElementById('summaryDetailModalOverlay');
        const titleEl = document.getElementById('summaryDetailTitle');
        const subtitleEl = document.getElementById('summaryDetailSubtitle');
        const metricHead = document.getElementById('summaryMetricColHead');
        const tbody = document.getElementById('summaryDetailTableBody');
        const emptyState = document.getElementById('summaryDetailEmpty');

        if (!modal || !titleEl || !subtitleEl || !metricHead || !tbody || !emptyState) {
            return;
        }

        const payload = window.summaryDetailsFromServer?.[summaryKey] || null;
        if (!payload) {
            alert('Detail untuk card ini belum tersedia.');
            return;
        }

        titleEl.textContent = payload.title || 'Detail Ringkasan';
        subtitleEl.textContent = payload.subtitle || 'Daftar UPP';

        const firstRow = payload.rows?.[0] || null;
        metricHead.textContent = firstRow?.metric_label || 'Nilai';

        tbody.innerHTML = '';
        if (!payload.rows || payload.rows.length === 0) {
            emptyState.style.display = 'block';
            document.getElementById('summaryDetailTable').style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            document.getElementById('summaryDetailTable').style.display = 'table';

            payload.rows.forEach((row) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.no ?? ''}</td>
                    <td>${row.upp ?? '-'}</td>
                    <td><strong>${row.metric_value ?? '-'}</strong></td>
                    <td>${row.extra ?? '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSummaryDetailModal() {
        const modal = document.getElementById('summaryDetailModalOverlay');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    console.log('📊 [BLADE RENDER] window.chartDataFromServer initialized:', {
        upp_id: window.chartDataFromServer.upp_id,
        f02_count: window.chartDataFromServer.f02_data?.length || 0,
        f03_count: window.chartDataFromServer.f03_data?.length || 0,
        ipp_count: window.chartDataFromServer.ipp_data?.length || 0,
        aspek_count: window.chartDataFromServer.aspek_values?.length || 0,
        aspek_id_count: window.chartDataFromServer.aspek_ids?.length || 0
    });

    // Debug: Verify Chart library is loaded
    if (typeof Chart === 'undefined') {
        console.error('⚠️ Chart.js library not loaded!');
    } else {
        console.log('✓ Chart.js loaded');
    }

    // ========== UPP Modal Handler (Dashboard-style) ==========
    document.addEventListener('DOMContentLoaded', () => {
        console.log('🚀 DOMContentLoaded - initializing modal & charts...');

        const moduleOverlay = document.getElementById('uppFilterModal');
        const openModalBtn = document.getElementById('openUppModal');
        const closeModalBtn = document.getElementById('closeModal');
        const closeModalBtnFooter = document.getElementById('closeModalBtn');
        const summaryDetailModal = document.getElementById('summaryDetailModalOverlay');
        const summaryDetailCloseBtn = document.getElementById('summaryDetailCloseBtn');
        const selectAllCheckbox = document.getElementById('selectAllUpp');
        const uppCheckboxes = document.querySelectorAll('.upp-checkbox');

        console.log('Modal elements found:', {
            moduleOverlay: !!moduleOverlay,
            openModalBtn: !!openModalBtn,
            closeModalBtn: !!closeModalBtn,
            checkboxes: uppCheckboxes.length
        });

        // Open Modal
        if (openModalBtn && moduleOverlay) {
            openModalBtn.addEventListener('click', function() {
                console.log('Opening modal...');
                moduleOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Close Modal
        const closeModal = () => {
            console.log('Closing modal...');
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

        if (summaryDetailCloseBtn) {
            summaryDetailCloseBtn.addEventListener('click', closeSummaryDetailModal);
        }

        if (summaryDetailModal) {
            summaryDetailModal.addEventListener('click', function(e) {
                if (e.target === summaryDetailModal) {
                    closeSummaryDetailModal();
                }
            });
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
            if (e.key === 'Escape' && moduleOverlay && moduleOverlay.classList.contains('active')) {
                closeModal();
            }
            if (e.key === 'Escape' && summaryDetailModal && summaryDetailModal.classList.contains('active')) {
                closeSummaryDetailModal();
            }
            if (e.key === 'Escape') {
                const aspekOverlay = document.getElementById('aspekDetailOverlay');
                if (aspekOverlay && getComputedStyle(aspekOverlay).display !== 'none') {
                    closeAspekDetailModal();
                }
            }
            // Close indicator detail modal
            if (e.key === 'Escape') {
                const indicatorOverlay = document.getElementById('indicatorDetailOverlay');
                if (indicatorOverlay && getComputedStyle(indicatorOverlay).display !== 'none') {
                    callAnalyticsComponentMethod('closeIndicatorDetail');
                }
            }
        });

        // Close indicator detail modal when clicking outside (on overlay)
        document.addEventListener('click', function(e) {
            const aspekOverlay = document.getElementById('aspekDetailOverlay');
            if (aspekOverlay && getComputedStyle(aspekOverlay).display !== 'none' && e.target === aspekOverlay) {
                closeAspekDetailModal();
            }

            const indicatorOverlay = document.getElementById('indicatorDetailOverlay');
            if (indicatorOverlay && getComputedStyle(indicatorOverlay).display !== 'none' && e.target === indicatorOverlay) {
                callAnalyticsComponentMethod('closeIndicatorDetail');
            }
        });

        // 💾 RESTORE FROM LOCALSTORAGE
        try {
            const savedFilter = localStorage.getItem('analytics_filter_upp');
            if (savedFilter) {
                const savedUppIds = JSON.parse(savedFilter);
                console.log('📂 Restoring filter from localStorage:', savedUppIds);

                uppCheckboxes.forEach(checkbox => {
                    const checkboxValue = parseInt(checkbox.value, 10);
                    checkbox.checked = savedUppIds.includes(checkboxValue);
                });

                // Show clear filter button
                const clearBtn = document.getElementById('clearFilterBtn');
                if (clearBtn) {
                    clearBtn.style.display = 'inline-flex';
                    console.log('✓ Clear filter button shown');
                }

                console.log('✓ Checkboxes restored from saved filter');

                // 🔄 AUTO-APPLY SAVED FILTER ON PAGE LOAD
                // Wait a brief moment to ensure Livewire is fully initialized
                setTimeout(() => {
                    console.log('🚀 Auto-applying saved filter from localStorage...');
                    const selectedCheckboxes = Array.from(uppCheckboxes).filter(cb => cb.checked);
                    const selectedUppIds = selectedCheckboxes
                        .map(cb => parseInt(cb.value, 10))
                        .filter(id => !Number.isNaN(id));

                    if (selectedUppIds.length > 0 && window.Livewire) {
                        console.log('📤 Dispatching auto-apply filter:', selectedUppIds);
                        Livewire.dispatch('setUppFilter', { upp_id: selectedUppIds });
                    }
                }, 500);
            } else {
                console.log('ℹ️ No saved filter in localStorage');
                // Hide clear filter button if no saved filter
                const clearBtn = document.getElementById('clearFilterBtn');
                if (clearBtn) {
                    clearBtn.style.display = 'none';
                }
            }
        } catch (error) {
            console.warn('⚠️ Error restoring from localStorage:', error);
        }

        // Checkbox synchronization logic using Event Delegation
        // This ensures the logic still works even after Livewire re-renders the DOM.
        function updateMasterCheckboxesDynamically() {
            const checkboxes = document.querySelectorAll('.upp-checkbox');
            const selectAll = document.getElementById('selectAllUpp');
            const selectSubmitted = document.getElementById('selectSubmittedUpp');
            
            if (checkboxes.length === 0) return;

            let allChecked = true;
            let anyChecked = false;
            let onlySubmittedChecked = true;
            let anySubmittedChecked = false;

            checkboxes.forEach(cb => {
                let status = cb.getAttribute('data-status') || 'belum_submit';
                let isSubmitted = (status === 'sudah_validasi' || status === 'belum_validasi');

                if (cb.checked) {
                    anyChecked = true;
                    if (!isSubmitted) {
                        onlySubmittedChecked = false; // An unsubmitted UPP is checked
                    } else {
                        anySubmittedChecked = true; // At least one submitted UPP is checked
                    }
                } else {
                    allChecked = false;
                    if (isSubmitted) {
                        onlySubmittedChecked = false; // A submitted UPP is NOT checked
                    }
                }
            });

            if (selectAll) {
                selectAll.checked = allChecked;
                selectAll.indeterminate = anyChecked && !allChecked;
            }

            if (selectSubmitted) {
                selectSubmitted.checked = anySubmittedChecked && onlySubmittedChecked;
            }
        }

        // Attach a single listener to the document to handle all checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'selectAllUpp') {
                const checkboxes = document.querySelectorAll('.upp-checkbox');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                updateMasterCheckboxesDynamically();
            }
            else if (e.target && e.target.id === 'selectSubmittedUpp') {
                const checkboxes = document.querySelectorAll('.upp-checkbox');
                checkboxes.forEach(cb => {
                    let status = cb.getAttribute('data-status') || 'belum_submit';
                    let isSubmitted = (status === 'sudah_validasi' || status === 'belum_validasi');
                    
                    if (e.target.checked) {
                        cb.checked = isSubmitted; // Only check if submitted
                    } else {
                        cb.checked = false; // Uncheck all
                    }
                });
                updateMasterCheckboxesDynamically();
            }
            else if (e.target && e.target.classList.contains('upp-checkbox')) {
                updateMasterCheckboxesDynamically();
            }
        });

        // Run once on load
        updateMasterCheckboxesDynamically();

        // ========== CHARTS INITIALIZATION ==========
        try {
            console.log('📊 Initializing charts...');
            initF02Chart();
            console.log('✓ F02 chart initialized');
            initF03Chart();
            console.log('✓ F03 chart initialized');
            initIPPChart();
            console.log('✓ IPP chart initialized');
            initAspekChart();
            console.log('✓ Aspek chart initialized');
            initF03AspekChart();
            console.log('✓ F03 Aspek chart initialized');
        } catch (error) {
            console.error('❌ Error initializing charts:', error);
        }
    });

    const colors = {
        primary: '#4F46E5',
        secondary: '#0EA5E9',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#06B6D4',
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: { font: { size: 11 } }
            },
            filler: { propagate: true }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                ticks: { font: { size: 10 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 } }
            }
        }
    };

    // Global chart instances tracker
    const chartInstances = {
        f02Chart: null,
        f03Chart: null,
        ippChart: null,
        aspekChart: null,
        f03AspekChart: null
    };

    // Helper function to get chart data from DOM data attributes
    // These are updated by Livewire on every re-render
    function getChartDataFromAttributes() {
        const debugEl = document.getElementById('debugUppId');
        if (!debugEl) {
            console.error('❌ [getChartDataFromAttributes] debugUppId element not found!');
            return null;
        }

        console.log('📊 [getChartDataFromAttributes] Reading from DOM data attributes');
        const data = {
            f02_labels: JSON.parse(debugEl.getAttribute('data-f02-labels') || '[]'),
            f02_data: JSON.parse(debugEl.getAttribute('data-f02-data') || '[]'),
            f03_labels: JSON.parse(debugEl.getAttribute('data-f03-labels') || '[]'),
            f03_data: JSON.parse(debugEl.getAttribute('data-f03-data') || '[]'),
            ipp_labels: JSON.parse(debugEl.getAttribute('data-ipp-labels') || '[]'),
            ipp_data: JSON.parse(debugEl.getAttribute('data-ipp-data') || '[]'),
            aspek_ids: JSON.parse(debugEl.getAttribute('data-aspek-ids') || '[]'),
            aspek_labels: JSON.parse(debugEl.getAttribute('data-aspek-labels') || '[]'),
            aspek_values: JSON.parse(debugEl.getAttribute('data-aspek-values') || '[]'),
            f03_aspek_ids: JSON.parse(debugEl.getAttribute('data-f03-aspek-ids') || '[]'),
            f03_aspek_labels: JSON.parse(debugEl.getAttribute('data-f03-aspek-labels') || '[]'),
            f03_aspek_values: JSON.parse(debugEl.getAttribute('data-f03-aspek-values') || '[]')
        };

        console.log('    - f02_data:', data.f02_data?.length || 0, 'items');
        console.log('    - f03_data:', data.f03_data?.length || 0, 'items');
        console.log('    - ipp_data:', data.ipp_data?.length || 0, 'items');
        console.log('    - aspek_values:', data.aspek_values?.length || 0, 'items');
        console.log('    - f03_aspek_values:', data.f03_aspek_values?.length || 0, 'items');

        return data;
    }

    function initF02Chart() {
        const ctx = document.getElementById('f02Chart');
        if (!ctx) return;

        const chartData = getChartDataFromAttributes();
        if (!chartData) {
            console.error('❌ Could not get chart data from attributes');
            return;
        }

        console.log('📊 F02Chart data:', chartData.f02_data);

        // Destroy existing chart if any
        if (chartInstances.f02Chart) {
            chartInstances.f02Chart.destroy();
        }

        chartInstances.f02Chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.f02_labels,
                datasets: [{
                    label: 'Skor F02',
                    data: chartData.f02_data,
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(79, 70, 229, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointHoverRadius: 6
                }]
            },
            options: chartOptions
        });
    }

    function initF03Chart() {
        const ctx = document.getElementById('f03Chart');
        if (!ctx) return;

        const chartData = getChartDataFromAttributes();
        if (!chartData) {
            console.error('❌ Could not get chart data from attributes');
            return;
        }

        console.log('📊 F03Chart data:', chartData.f03_data);

        // Destroy existing chart if any
        if (chartInstances.f03Chart) {
            chartInstances.f03Chart.destroy();
        }

        chartInstances.f03Chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.f03_labels,
                datasets: [{
                    label: 'Skor F03',
                    data: chartData.f03_data,
                    borderColor: colors.success,
                    backgroundColor: 'rgba(34, 197, 94, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: colors.success,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointHoverRadius: 6
                }]
            },
            options: chartOptions
        });
    }

    function initIPPChart() {
        const ctx = document.getElementById('ippChart');
        if (!ctx) return;

        const chartData = getChartDataFromAttributes();
        if (!chartData) {
            console.error('❌ Could not get chart data from attributes');
            return;
        }

        console.log('📊 IPPChart data:', chartData.ipp_data);

        // Destroy existing chart if any
        if (chartInstances.ippChart) {
            chartInstances.ippChart.destroy();
        }

        // Predikat color mapper
        function getPredikatColor(skor) {
            const val = parseFloat(skor);
            if (val > 4.50) return '#9333EA'; // Istimewa (Purple 600)
            if (val >= 4.01) return '#2563EB'; // Sangat Baik (Blue 600)
            if (val >= 3.51) return '#16A34A'; // Baik (Green 600)
            if (val >= 3.01) return '#0891B2'; // BDC (Cyan 600)
            if (val >= 2.51) return '#EAB308'; // Cukup (Yellow 500)
            if (val >= 2.01) return '#EA580C'; // Kurang (Orange 600)
            return '#DC2626'; // Prioritas Pembinaan (Red 600)
        }
        
        const pointColors = chartData.ipp_data.map(skor => getPredikatColor(skor));

        // Plugin to draw dashed lines from data points to x-axis
        const dropLinePlugin = {
            id: 'dropLine',
            beforeDatasetsDraw: (chart) => {
                const ctx = chart.ctx;
                chart.getDatasetMeta(0).data.forEach((point, index) => {
                    ctx.save();
                    ctx.beginPath();
                    ctx.setLineDash([4, 4]);
                    ctx.moveTo(point.x, point.y);
                    ctx.lineTo(point.x, chart.scales.y.bottom);
                    ctx.lineWidth = 1.5;
                    ctx.strokeStyle = pointColors[index] || 'rgba(0,0,0,0.2)';
                    ctx.stroke();
                    ctx.restore();
                });
            }
        };

        const ippOptions = JSON.parse(JSON.stringify(chartOptions));
        if(ippOptions.plugins && ippOptions.plugins.legend) {
            ippOptions.plugins.legend.display = false;
        }

        chartInstances.ippChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.ipp_labels,
                datasets: [{
                    label: 'Nilai IPP',
                    data: chartData.ipp_data,
                    borderColor: '#cbd5e1', // Neutral line so dots pop out
                    backgroundColor: 'rgba(203, 213, 225, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8
                }]
            },
            options: ippOptions,
            plugins: [dropLinePlugin]
        });
    }

    function initAspekChart() {
        const ctx = document.getElementById('aspekChart');
        if (!ctx) return;

        const chartData = getChartDataFromAttributes();
        if (!chartData) {
            console.error('❌ Could not get chart data from attributes');
            return;
        }

        console.log('📊 AspekChart data:', chartData.aspek_values);

        // Destroy existing chart if any
        if (chartInstances.aspekChart) {
            chartInstances.aspekChart.destroy();
        }

        chartInstances.aspekChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: chartData.aspek_labels.map((label, index) => {
                    const score = parseFloat(chartData.aspek_values[index] || 0).toFixed(2);
                    return [label, `Skor: ${score}`];
                }),
                datasets: [{
                    label: 'Rata-rata Indikator',
                    data: chartData.aspek_values,
                    backgroundColor: 'rgba(79, 70, 229, 0.25)',
                    borderColor: 'rgba(79, 70, 229, 0.9)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(79, 70, 229, 0.9)',
                    pointBorderColor: '#ffffff',
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: 'rgba(79, 70, 229, 0.9)',
                    fill: true,
                    tension: 0
                }]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { font: { size: 11 } }
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 5,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.07)'
                        },
                        angleLines: {
                            color: 'rgba(0, 0, 0, 0.08)'
                        },
                        pointLabels: {
                            font: { size: 11 },
                            color: '#475569'
                        },
                        ticks: {
                            display: true,
                            stepSize: 1,
                            color: '#334155',
                            backdropColor: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                },
                onHover: function(event, elements) {
                    const canvas = event?.native?.target;
                    if (canvas) {
                        canvas.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                    }
                },
                onClick: function(_event, elements) {
                    if (!elements || elements.length === 0) {
                        return;
                    }

                    const selectedIndex = elements[0]?.index;
                    const selectedAspekId = parseInt(chartData.aspek_ids?.[selectedIndex], 10);

                    if (!Number.isNaN(selectedAspekId) && selectedAspekId > 0) {
                        openAspekDetailModal(selectedAspekId);
                    }
                }
            }
        });
    }

    function initF03AspekChart() {
        const ctx = document.getElementById('f03AspekChart');
        if (!ctx) return;

        const chartData = getChartDataFromAttributes();
        if (!chartData) {
            console.error('❌ Could not get chart data from attributes');
            return;
        }

        console.log('📊 F03AspekChart data:', chartData.f03_aspek_values);

        if (chartInstances.f03AspekChart) {
            chartInstances.f03AspekChart.destroy();
        }

        chartInstances.f03AspekChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: chartData.f03_aspek_labels,
                datasets: [{
                    label: 'Rata-rata Skor F03',
                    data: chartData.f03_aspek_values,
                    backgroundColor: 'rgba(16, 185, 129, 0.24)',
                    borderColor: 'rgba(16, 185, 129, 0.95)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(16, 185, 129, 0.95)',
                    pointBorderColor: '#ffffff',
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: 'rgba(16, 185, 129, 0.95)',
                    fill: true
                }]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { font: { size: 11 } }
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 5,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.07)'
                        },
                        angleLines: {
                            color: 'rgba(0, 0, 0, 0.08)'
                        },
                        pointLabels: {
                            font: { size: 11 },
                            color: '#475569'
                        },
                        ticks: {
                            display: true,
                            stepSize: 1,
                            color: '#334155',
                            backdropColor: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    }

    window.addEventListener('analytics-charts-updated', (event) => {
        console.log('');
        console.log('═══════════════════════════════════════════════════════');
        console.log('🔄 analytics-charts-updated EVENT FIRED');
        console.log('═══════════════════════════════════════════════════════');

        if (event.detail?.chartData) {
            window.chartDataFromServer = event.detail.chartData;
            window.summaryDetailsFromServer = event.detail.chartData.summary_card_details || {};
            console.log('📊 Chart data updated from browser event:', {
                upp_id: window.chartDataFromServer.upp_id,
                f02_count: window.chartDataFromServer.f02_data?.length || 0,
                f03_count: window.chartDataFromServer.f03_data?.length || 0,
                f03_aspek_count: window.chartDataFromServer.f03_aspek_values?.length || 0,
                ipp_count: window.chartDataFromServer.ipp_data?.length || 0,
                aspek_count: window.chartDataFromServer.aspek_values?.length || 0,
                aspek_id_count: window.chartDataFromServer.aspek_ids?.length || 0
            });
        }

        setTimeout(() => {
            try {
                console.log('🔄 Re-initializing all charts...');
                initF02Chart();
                console.log('   ✓ F02 chart re-initialized');

                initF03Chart();
                console.log('   ✓ F03 chart re-initialized');

                initIPPChart();
                console.log('   ✓ IPP chart re-initialized');

                initAspekChart();
                console.log('   ✓ Aspek chart re-initialized');

                initF03AspekChart();
                console.log('   ✓ F03 Aspek chart re-initialized');

                console.log('✅ All charts updated successfully!');
            } catch (error) {
                console.error('❌ Error re-initializing charts:', error);
            }
            console.log('═══════════════════════════════════════════════════════');
            console.log('');
        }, 100);
    });

    window.addEventListener('analytics-export-failed', (event) => {
        const message = event.detail?.message || 'Export gagal diproses.';
        alert(message);
    });

    window.addEventListener('analytics-export-success', (event) => {
        const message = event.detail?.message || 'Export berhasil.';
        alert(message);
    });
</script>
</div>
