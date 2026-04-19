<div class="min-h-screen bg-white">
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

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 14px;
            margin-top: 14px;
        }

        @media (min-width: 760px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1080px) {
            .summary-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .summary-card {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
            padding: 14px 14px 12px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: transform 140ms ease, box-shadow 140ms ease;
            font-family: "Plus Jakarta Sans", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }

        .summary-card:focus-visible {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #6366f1;
        }

        .summary-card-head {
            font-size: 0.76rem;
            letter-spacing: 0.06em;
            font-weight: 700;
            color: #55657d;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .summary-card-value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 7px;
        }

        .summary-card-caption {
            font-size: 0.84rem;
            color: #64748b;
            line-height: 1.3;
        }

        .summary-card-subline {
            margin-top: 9px;
            padding-top: 9px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #475569;
            line-height: 1.3;
        }

        .summary-card-subline strong {
            color: #334155;
        }

        @media (max-width: 640px) {
            .summary-card {
                padding: 12px 12px 10px;
            }

            .summary-card-value {
                font-size: 1.65rem;
            }

            .summary-card-caption,
            .summary-card-subline {
                font-size: 0.78rem;
            }
        }

        .summary-purple::before { background: #4f46e5; }
        .summary-amber::before { background: #f59e0b; }
        .summary-slate::before { background: #0f172a; }
        .summary-emerald::before { background: #10b981; }
        .summary-rose::before { background: #ef4444; }
        .summary-blue::before { background: #2563eb; }
        .summary-orange::before { background: #f97316; }
        .summary-green::before { background: #22c55e; }

        .summary-purple { background: #ffffff; }
        .summary-purple .summary-card-value { color: #1e293b; }

        .summary-amber { background: #fff7e8; }
        .summary-amber .summary-card-value { color: #1e293b; }

        .summary-slate { background: #ffffff; }
        .summary-slate .summary-card-value { color: #1e293b; }

        .summary-emerald { background: #ecfdf5; }
        .summary-emerald .summary-card-value { color: #1e293b; }

        .summary-rose { background: #fef2f2; }
        .summary-rose .summary-card-value { color: #dc2626; }

        .summary-blue { background: #eef2ff; }
        .summary-blue .summary-card-value { color: #2563eb; }

        .summary-orange { background: #fff4ed; }
        .summary-orange .summary-card-value { color: #ea580c; }

        .summary-green { background: #ecfdf3; }
        .summary-green .summary-card-value { color: #16a34a; }

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

            <div class="summary-grid">
                <div class="summary-card summary-purple" role="button" tabindex="0" onclick="openSummaryDetail('total_upp')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('total_upp');}">
                    <div class="summary-card-head">Total Unit Pelayanan</div>
                    <div class="summary-card-value">{{ number_format((int) ($summary_cards['total_upp'] ?? 0)) }}</div>
                    <div class="summary-card-caption">UPP dalam cakupan filter aktif</div>
                </div>

                <div class="summary-card summary-amber" role="button" tabindex="0" onclick="openSummaryDetail('avg_f02')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('avg_f02');}">
                    <div class="summary-card-head">Nilai F02</div>
                    <div class="summary-card-value">{{ number_format((float) ($summary_cards['avg_f02'] ?? 0), 2) }}</div>
                    <div class="summary-card-caption">Dokumentasi (0-100) • Kategori IPP: {{ $summary_cards['ipp_category'] ?? '-' }}</div>
                    <div class="summary-card-subline"><strong>Kontribusi IPP (75%)</strong>: {{ number_format((float) ($summary_cards['f02_contribution'] ?? 0), 2) }}</div>
                </div>

                <div class="summary-card summary-slate" role="button" tabindex="0" onclick="openSummaryDetail('avg_f03')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('avg_f03');}">
                    <div class="summary-card-head">Nilai F03</div>
                    <div class="summary-card-value">{{ number_format((float) ($summary_cards['avg_f03'] ?? 0), 2) }}</div>
                    <div class="summary-card-caption">Survey Kepuasan (1-5) • Responden: {{ number_format((int) ($summary_cards['f03_response_count'] ?? 0)) }}</div>
                    <div class="summary-card-subline">
                        <strong>Kontribusi IPP (25%)</strong>: {{ number_format((float) ($summary_cards['f03_contribution'] ?? 0), 2) }}
                        @if((int) ($summary_cards['f03_under_minimum_upp_count'] ?? 0) > 0)
                            • UPP di bawah minimum {{ (int) ($summary_cards['f03_minimum_target'] ?? 0) }} responden (skor efektif 0)
                        @endif
                    </div>
                </div>

                <div class="summary-card summary-emerald" role="button" tabindex="0" onclick="openSummaryDetail('avg_ipp')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('avg_ipp');}">
                    <div class="summary-card-head">Indeks Pelayanan Publik</div>
                    <div class="summary-card-value">{{ number_format((float) ($summary_cards['avg_ipp'] ?? 0), 2) }}</div>
                    <div class="summary-card-caption">{{ $summary_cards['ipp_category_label'] ?? (($summary_cards['ipp_status'] ?? 'Buruk')) }}</div>
                </div>

                <div class="summary-card summary-emerald" role="button" tabindex="0" onclick="openSummaryDetail('upp_baik')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('upp_baik');}">
                    <div class="summary-card-head">UPP Baik</div>
                    <div class="summary-card-value">{{ number_format((int) ($summary_cards['upp_baik'] ?? 0)) }}</div>
                    <div class="summary-card-caption">Status evaluasi baik (>= 3.01)</div>
                </div>

                <div class="summary-card summary-rose" role="button" tabindex="0" onclick="openSummaryDetail('upp_perlu_pembinaan')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('upp_perlu_pembinaan');}">
                    <div class="summary-card-head">UPP Perlu Pembinaan</div>
                    <div class="summary-card-value">{{ number_format((int) ($summary_cards['upp_perlu_pembinaan'] ?? 0)) }}</div>
                    <div class="summary-card-caption">Perlu perhatian khusus</div>
                </div>

                <div class="summary-card summary-blue" role="button" tabindex="0" onclick="openSummaryDetail('sudah_submit')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('sudah_submit');}">
                    <div class="summary-card-head">Sudah Submit</div>
                    <div class="summary-card-value">{{ number_format((int) ($summary_cards['sudah_submit'] ?? 0)) }}</div>
                    <div class="summary-card-caption">Pengisian F01 terkirim</div>
                </div>

                <div class="summary-card summary-orange" role="button" tabindex="0" onclick="openSummaryDetail('belum_validasi')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('belum_validasi');}">
                    <div class="summary-card-head">Belum Validasi</div>
                    <div class="summary-card-value">{{ number_format((int) ($summary_cards['belum_validasi'] ?? 0)) }}</div>
                    <div class="summary-card-caption">Menunggu validasi F02</div>
                </div>

                <div class="summary-card summary-green" role="button" tabindex="0" onclick="openSummaryDetail('sudah_selesai')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openSummaryDetail('sudah_selesai');}">
                    <div class="summary-card-head">Sudah Selesai</div>
                    <div class="summary-card-value">{{ number_format((int) ($summary_cards['sudah_selesai'] ?? 0)) }}</div>
                    <div class="summary-card-caption">Validasi F02 selesai</div>
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
         data-aspek-labels='@json($aspek_labels)'
         data-aspek-values='@json($aspek_values)'>
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
                <div id="uppChecklistContainer">
                    @foreach($upp_options as $upp)
                        <div class="form-check">
                            <input class="form-check-input upp-checkbox" type="checkbox"
                                id="upp_{{ $upp['id'] }}"
                                value="{{ $upp['id'] }}"
                                {{ in_array($upp['id'], $upp_id ?? [], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="upp_{{ $upp['id'] }}" style="display: flex; justify-content: space-between; width: 100%; align-items: center; gap: 10px;">
                                <span class="upp-name-wrap">
                                    <span class="upp-number-badge">{{ $loop->iteration }}</span>
                                    <span class="upp-label-text">{{ $upp['label'] }}</span>
                                </span>
                                <span style="display: inline-flex; gap: 6px; align-items: center;">
                                    <span style="font-weight: bold; color: #166534; font-size: 0.85rem; background: #dcfce7; padding: 2px 8px; border-radius: 4px;">IPP: {{ number_format((float) ($upp['ipp_value'] ?? 0), 2) }}</span>
                                    @if(!empty($upp['is_export_ready']))
                                        <span style="font-weight: 700; color: #166534; font-size: 0.75rem; background: #bbf7d0; padding: 2px 8px; border-radius: 999px;">Siap Export</span>
                                    @else
                                        <span style="font-weight: 700; color: #b45309; font-size: 0.75rem; background: #fef3c7; padding: 2px 8px; border-radius: 999px;">Belum Selesai Validasi</span>
                                    @endif
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
                <button type="button" class="summary-detail-close" id="summaryDetailCloseBtn">&times;</button>
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

    <!-- Chart 1: F02 -->
    <div class="chart-section">
        <div class="container">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span>📈</span>Skor F02
                    </h3>
                    <span class="chart-card-badge" style="background: #dbeafe; color: #0369a1;">Validasi</span>
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
                    <span class="chart-card-badge" style="background: #dcfce7; color: #166534;">Publik</span>
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
                    <span class="chart-card-badge" style="background: #e9d5ff; color: #6b21a8;">Akhir</span>
                </div>
                <div wire:ignore class="chart-container" style="height: 300px;">
                    <canvas id="ippChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 4: Aspek -->
    <div class="chart-section">
        <div class="container">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span>📋</span>Total Nilai Indikator F02 per Aspek
                    </h3>
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
                <div wire:ignore class="chart-container" style="height: 300px;">
                    <canvas id="aspekChart"></canvas>
                </div>

                <div style="margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 16px;">
                    <div style="font-size: 0.95rem; font-weight: 700; color: #1f2937; margin-bottom: 10px;">Skor Indikator per Aspek</div>

                    @if(!empty($aspek_tabs))
                        @php
                            $selectedAspekTab = collect($aspek_tabs)->firstWhere('id', (int) $selected_aspek_id);
                        @endphp

                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                            @foreach($aspek_tabs as $aspekTab)
                                <button
                                    type="button"
                                    wire:click="selectAspek({{ $aspekTab['id'] }})"
                                    style="padding: 8px 12px; border-radius: 8px; border: 1px solid {{ (int) $selected_aspek_id === (int) $aspekTab['id'] ? '#4f46e5' : '#d1d5db' }}; background: {{ (int) $selected_aspek_id === (int) $aspekTab['id'] ? '#eef2ff' : '#ffffff' }}; color: {{ (int) $selected_aspek_id === (int) $aspekTab['id'] ? '#3730a3' : '#374151' }}; font-size: 0.82rem; font-weight: 600; cursor: pointer;">
                                    {{ $aspekTab['nama'] }}
                                </button>
                            @endforeach
                        </div>

                        @if(!empty($selectedAspekTab))
                            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                                <div style="padding: 8px 10px; background: #f1f5f9; color: #334155; border-radius: 8px; font-size: 0.82rem;">
                                    Bobot Aspek: <strong>{{ rtrim(rtrim(number_format((float) ($selectedAspekTab['bobot_aspek'] ?? 0), 2, '.', ''), '0'), '.') }}%</strong>
                                </div>
                                <div style="padding: 8px 10px; background: #ecfeff; color: #0f766e; border-radius: 8px; font-size: 0.82rem;">
                                    Total Skor Setelah Bobot: <strong>{{ number_format((float) ($selectedAspekTab['skor_setelah_bobot'] ?? 0), 4) }}</strong>
                                </div>
                            </div>
                        @endif

                        <div style="border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
                            <div style="max-height: 360px; overflow: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th style="text-align: left; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; width: 56px;">No</th>
                                            <th style="text-align: left; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; width: 120px;">Kode</th>
                                            <th style="text-align: left; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb;">Nama Indikator</th>
                                            <th style="text-align: right; font-size: 0.78rem; color: #64748b; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; width: 120px;">Skor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($selected_aspek_rows as $row)
                                            <tr>
                                                <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; color: #334155;">{{ $row['no'] }}</td>
                                                <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; color: #334155; font-weight: 600;">{{ $row['indikator_kode'] }}</td>
                                                <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; color: #1f2937;">{{ $row['indikator_nama'] }}</td>
                                                <td style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; text-align: right; font-size: 0.85rem; color: #0f766e; font-weight: 700;">{{ number_format((float) $row['indikator_skor'], 4) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" style="padding: 16px 12px; text-align: center; color: #94a3b8; font-size: 0.85rem;">Belum ada data indikator untuk aspek ini.</td>
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
        </div>
    </div>

    <script>
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
        aspek_labels: @json($aspek_labels),
        aspek_values: @json($aspek_values),
        summary_cards: @json($summary_cards ?? []),
        summary_card_details: @json($summary_card_details ?? [])
    };

    window.summaryDetailsFromServer = @json($summary_card_details ?? []);

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
        aspek_count: window.chartDataFromServer.aspek_values?.length || 0
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
        });

        // Select All functionality (dashboard-style)
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

            function updateSelectAllCheckbox() {
                const allChecked = Array.from(uppCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(uppCheckboxes).some(cb => cb.checked);

                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        }

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
        aspekChart: null
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
            aspek_labels: JSON.parse(debugEl.getAttribute('data-aspek-labels') || '[]'),
            aspek_values: JSON.parse(debugEl.getAttribute('data-aspek-values') || '[]')
        };

        console.log('    - f02_data:', data.f02_data?.length || 0, 'items');
        console.log('    - f03_data:', data.f03_data?.length || 0, 'items');
        console.log('    - ipp_data:', data.ipp_data?.length || 0, 'items');
        console.log('    - aspek_values:', data.aspek_values?.length || 0, 'items');

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

        chartInstances.ippChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.ipp_labels,
                datasets: [{
                    label: 'Nilai IPP',
                    data: chartData.ipp_data,
                    borderColor: colors.warning,
                    backgroundColor: 'rgba(168, 85, 247, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: colors.warning,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointHoverRadius: 6
                }]
            },
            options: chartOptions
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
            type: 'bar',
            data: {
                labels: chartData.aspek_labels,
                datasets: [{
                    label: 'Skor Aspek',
                    data: chartData.aspek_values,
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.8)',   // indigo
                        'rgba(168, 85, 247, 0.8)',  // purple
                        'rgba(236, 72, 153, 0.8)',  // pink
                        'rgba(34, 197, 94, 0.8)',   // green
                        'rgba(59, 130, 246, 0.8)',  // blue
                        'rgba(249, 115, 22, 0.8)',  // orange
                        'rgba(14, 165, 233, 0.8)'   // sky
                    ],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                ...chartOptions,
                indexAxis: 'y',
                plugins: {
                    ...chartOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y: {
                        grid: {
                            display: false,
                            drawBorder: false
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
                ipp_count: window.chartDataFromServer.ipp_data?.length || 0,
                aspek_count: window.chartDataFromServer.aspek_values?.length || 0
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
