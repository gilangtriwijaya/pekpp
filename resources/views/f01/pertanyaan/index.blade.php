@extends('layouts.app')
@section('title','Manajemen Pertanyaan')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('components.crud-table.css', ['prefix' => 'pertanyaan'])

<div class="pertanyaan-container">
    @include('components.crud-table.header', [
        'prefix' => 'pertanyaan',
        'title' => 'Manajemen Pertanyaan F01',
        'subtitle' => 'Kelola pertanyaan survei untuk setiap indikator penilaian kematangan',
        'buttonText' => 'Tambah Pertanyaan',
        'buttonAction' => 'openCreateModal()'
    ])

    @include('components.crud-table.stats', [
        'prefix' => 'pertanyaan',
        'stats' => [
            ['label' => 'Total Pertanyaan', 'value' => $pertanyaan->total()],
            ['label' => 'Pertanyaan Aktif', 'value' => $pertanyaan->where('aktif', 1)->count()],
            ['label' => 'Wajib Diisi', 'value' => $pertanyaan->where('wajib', 1)->count()]
        ]
    ])

    @include('components.crud-table.table-card', [
        'prefix' => 'pertanyaan',
        'tableTitle' => 'Daftar Pertanyaan',
        'tableId' => 'pertanyaanTable'
    ])

    @include('components.crud-table.search', [
        'prefix' => 'pertanyaan',
        'searchInputId' => 'pertanyaanSearch',
        'tableId' => 'pertanyaanTable'
    ])
    
    <div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; justify-content: flex-end; background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form method="GET" action="{{ route('admin.f01.pertanyaan.index') }}" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <label for="filter_periode" style="font-size: 14px; font-weight: 500; color: #475569;">Filter Periode:</label>
            <select name="periode_id" id="filter_periode" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; min-width: 150px; outline: none; transition: border-color 0.2s;" onchange="document.getElementById('filter_indikator').value=''; this.form.submit()" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
                <option value="">Semua Periode</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓' : '' }}
                    </option>
                @endforeach
            </select>
            
            <label for="filter_indikator" style="font-size: 14px; font-weight: 500; color: #475569; margin-left: 1rem;">Indikator:</label>
            <select name="indikator_id" id="filter_indikator" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; min-width: 200px; outline: none; transition: border-color 0.2s;" onchange="this.form.submit()" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
                <option value="">Semua Indikator</option>
                @foreach($indikators as $i)
                    <option value="{{ $i->id }}" {{ request('indikator_id') == $i->id ? 'selected' : '' }}>
                        [{{ $i->kode }}] {{ Str::limit($i->nama, 40) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <table class="pertanyaan-table" id="pertanyaanTable">
        <thead>
            <tr>
                <th style="width: 80px;">Kode</th>
                <th>Pertanyaan</th>
                <th style="width: 100px;">Indikator</th>
                <th style="width: 120px;">Tipe</th>
                <th style="width: 70px;">Wajib</th>
                <th style="width: 80px;">Status</th>
                <th style="width: 140px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pertanyaan as $p)
            <tr data-id="{{ $p->id }}" class="pertanyaan-row">
                <td>
                    <strong style="font-family: 'Courier New', monospace;">{{ $p->kode }}</strong>
                </td>
                <td>
                    <span title="{{ $p->label }}" style="cursor: help;">{{ Str::limit($p->label, 55) }}</span>
                    @if($p->indikator)
                        <br><small class="pertanyaan-text-muted">[{{ $p->indikator->kode }}]</small>
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="pertanyaan-badge pertanyaan-badge-light">{{ $p->indikator->kode ?? '-' }}</span>
                </td>
                <td style="text-align: center;">
                    <span class="pertanyaan-badge pertanyaan-badge-info">{{ getQuestionTypeLabel($p->tipe_input) }}</span>
                </td>
                <td style="text-align: center;">
                    <span class="pertanyaan-badge {{ $p->wajib ? 'pertanyaan-badge-warning' : 'pertanyaan-badge-secondary' }}">
                        {{ $p->wajib ? 'Ya' : 'Tidak' }}
                    </span>
                </td>
                <td style="text-align: center;">
                    <span class="pertanyaan-badge {{ $p->aktif ? 'pertanyaan-badge-success' : 'pertanyaan-badge-secondary' }}">
                        {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td style="text-align: center;">
                    <div class="pertanyaan-action-buttons">
                        <button class="pertanyaan-btn-icon" onclick="viewDetail({{ $p->id }})" title="Lihat Detail">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                        <button class="pertanyaan-btn-icon" onclick="openEditModal({{ $p->id }})" title="Edit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <button class="pertanyaan-btn-icon btn-danger delete-btn" data-id="{{ $p->id }}" title="Hapus">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 24px; color: #64748b;">Tidak ada data pertanyaan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

@include('components.crud-table.pagination', [
    'prefix' => 'pertanyaan',
    'data' => $pertanyaan
])
</div>

<!-- Modals -->
@include('f01.pertanyaan.modals.create', ['indikators' => $indikators])
@include('f01.pertanyaan.modals.detail')
@include('f01.pertanyaan.modals.delete')

<!-- Toast Notification -->
<div class="pertanyaan-toast" id="pertanyaan-toast"></div>

@push('styles')
    <style>
        /* Modal size variations */
        .pertanyaan-modal-lg {
            max-width: 800px;
        }

        .pertanyaan-modal-sm {
            max-width: 400px;
        }

        /* Modal header danger style */
        .pertanyaan-modal-header-danger {
            background: #dc2626;
            color: white;
            border-bottom-color: #b91c1c;
        }

        .pertanyaan-modal-header-danger .pertanyaan-modal-title {
            color: white;
        }

        /* Text muted */
        .pertanyaan-text-muted {
            font-size: 13px;
            color: #64748b;
            display: block;
            margin-top: 4px;
        }

        /* Badge styles - additional for table */
        .pertanyaan-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .pertanyaan-badge-info {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .pertanyaan-badge-success {
            background: #dcfce7;
            color: #166534;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pertanyaan-badge-success::before {
            content: "●";
            font-size: 8px;
        }

        .pertanyaan-badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .pertanyaan-badge-light {
            background: #f1f5f9;
            color: #475569;
        }

        .pertanyaan-badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        /* Drag handle styling */
        .pertanyaan-drag-handle {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            cursor: grab;
            color: #94a3b8;
            font-weight: bold;
        }

        .pertanyaan-row:active .pertanyaan-drag-handle {
            cursor: grabbing;
        }

        /* Form hidden group */
        .pertanyaan-form-hidden {
            display: none;
        }

        /* Form container for opsi */
        .pertanyaan-opsi-container {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Opsi item styling */
        .pertanyaan-opsi-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .pertanyaan-opsi-item:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .pertanyaan-opsi-item:last-child {
            margin-bottom: 0;
        }

        .pertanyaan-opsi-item .pertanyaan-form-input {
            flex: 1;
            margin: 0;
            padding: 0.625rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .pertanyaan-opsi-item .pertanyaan-form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .pertanyaan-opsi-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border: none;
            border-radius: 4px;
            background: #fef2f2;
            color: #dc2626;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
            font-size: 14px;
        }

        .pertanyaan-opsi-delete:hover {
            background: #fee2e2;
            transform: scale(1.1);
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }

        .pertanyaan-opsi-delete:active {
            transform: scale(0.95);
        }

        .pertanyaan-opsi-delete i {
            font-size: 14px;
        }

        /* Form divider for checkboxes */
        .pertanyaan-form-divider {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        /* Checkbox styling */
        .pertanyaan-form-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
        }

        .pertanyaan-form-checkbox:last-child {
            margin-bottom: 0;
        }

        .pertanyaan-form-checkbox-input {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #2563eb;
            transition: all 0.2s ease;
        }

        .pertanyaan-form-checkbox-input:hover {
            transform: scale(1.1);
        }

        .pertanyaan-form-checkbox-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin: 0;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease;
        }

        .pertanyaan-form-checkbox-label:hover {
            color: #2563eb;
        }

        /* Validation error styles */
        .pertanyaan-form-input.is-invalid {
            border-color: #dc2626;
            background-color: #fef2f2;
        }

        .pertanyaan-text-error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        /* Action buttons styling */
        .pertanyaan-action-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .pertanyaan-btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            padding: 0;
            border: none;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0;
        }

        .pertanyaan-btn-icon:hover {
            background: #e2e8f0;
            color: #334155;
            transform: translateY(-2px);
        }

        .pertanyaan-btn-icon:active {
            transform: translateY(0);
        }

        .pertanyaan-btn-icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .pertanyaan-btn-icon.btn-danger {
            color: #dc2626;
            background: #fef2f2;
        }

        .pertanyaan-btn-icon.btn-danger:hover {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Button Tambah Opsi styling */
        .pertanyaan-btn-tambah-opsi {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #2563eb;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid #93c5fd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }

        .pertanyaan-btn-tambah-opsi:hover {
            background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
            border-color: #60a5fa;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .pertanyaan-btn-tambah-opsi:active {
            transform: translateY(0);
        }

        /* Custom table styling for pertanyaan */
        .pertanyaan-table tbody td {
            font-size: 13px;
        }

        /* Toast animations */
        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateX(400px);
            }
            to { 
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from { 
                opacity: 1;
                transform: translateX(0);
            }
            to { 
                opacity: 0;
                transform: translateX(400px);
            }
        }

        /* Modal Overlay & Container Styling */
        .pertanyaan-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }

        .pertanyaan-modal-overlay.active {
            display: flex;
        }

        .pertanyaan-modal {
            background: white;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        /* Modal Header */
        .pertanyaan-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .pertanyaan-modal-title {
            font-size: 1.375rem;
            font-weight: 600;
            margin: 0;
            color: #1f2937;
        }

        .pertanyaan-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border-radius: 4px;
        }

        .pertanyaan-modal-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        /* Modal Body */
        .pertanyaan-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }

        /* Modal Footer */
        .pertanyaan-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        /* Form Group Styling */
        .pertanyaan-form-group {
            margin-bottom: 1.25rem;
        }

        .pertanyaan-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .pertanyaan-form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .pertanyaan-form-label .required {
            color: #dc2626;
        }

        .pertanyaan-form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            color: #1f2937;
            background: white;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .pertanyaan-form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        /* Button Group Styling */
        .pertanyaan-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pertanyaan-btn-primary {
            background: #2563eb;
            color: white;
        }

        .pertanyaan-btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .pertanyaan-btn-primary:active {
            transform: translateY(0);
        }

        .pertanyaan-btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .pertanyaan-btn-secondary:hover {
            background: #d1d5db;
            transform: translateY(-1px);
        }

        .pertanyaan-btn-secondary:active {
            transform: translateY(0);
        }

        .pertanyaan-btn-danger {
            background: #dc2626;
            color: white;
        }

        .pertanyaan-btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .pertanyaan-btn-danger:active {
            transform: translateY(0);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Define functions first before any DOM manipulation
        let deleteData = null;
        let sortable = null;

        function showToast(message, type = 'info') {
            // Create container jika belum ada
            let container = document.getElementById('pertanyaan-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'pertanyaan-toast-container';
                container.style.cssText = 'position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 10000; display: flex; flex-direction: column; gap: 0.75rem; max-width: calc(100vw - 3rem);';
                document.body.appendChild(container);
            }
            
            const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
            
            const toast = document.createElement('div');
            toast.className = 'alert ' + alertClass + ' d-flex align-items-center gap-2 mb-0';
            toast.style.cssText = 'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 0.375rem; max-width: 400px; word-wrap: break-word; animation: slideIn 0.3s ease-out;';
            toast.innerHTML = '<i class="fas fa-' + icon + '" aria-hidden="true" style="flex-shrink: 0;"></i><span style="flex: 1; white-space: pre-wrap;">' + message + '</span><button type="button" class="btn-close btn-sm" aria-label="Tutup pemberitahuan" style="flex-shrink: 0;"></button>';
            
            container.appendChild(toast);
            
            const closeBtn = toast.querySelector('.btn-close');
            const closeToast = () => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            };
            
            closeBtn?.addEventListener('click', closeToast);
            
            // Auto close setelah 4 detik
            const timer = setTimeout(closeToast, 4000);
            
            // Clear timer jika user manual close
            toast.addEventListener('mouseenter', () => clearTimeout(timer));
        }

        function validateForm(form) {
            const errors = [];
            
            // Required fields
            const indikator = document.getElementById('pertanyaan-indikator').value.trim();
            const label = document.getElementById('pertanyaan-label').value.trim();
            const tipeInput = document.getElementById('pertanyaan-tipeInput').value;
            
            if (!indikator) {
                errors.push('Pilih Indikator terlebih dahulu');
            }
            
            if (!label || label.length === 0) {
                errors.push('Masukkan teks pertanyaan');
            } else if (label.length < 5) {
                errors.push('Teks pertanyaan minimal 5 karakter');
            } else if (label.length > 500) {
                errors.push('Teks pertanyaan maksimal 500 karakter');
            }
            
            if (!tipeInput) {
                errors.push('Pilih tipe pertanyaan');
            }
            
            // Type-specific validation
            if (['radio', 'checkbox', 'select'].includes(tipeInput)) {
                const opsiInputs = document.querySelectorAll('.opsi-input');
                const validOpsi = Array.from(opsiInputs)
                    .filter(inp => inp.value.trim().length > 0);
                
                if (validOpsi.length === 0) {
                    errors.push('Tambahkan minimal 1 opsi jawaban');
                } else if (validOpsi.length < 2) {
                    errors.push('Minimal 2 opsi untuk tipe ini');
                }
            }
            
            if (['number', 'skala'].includes(tipeInput)) {
                const min = document.getElementById('pertanyaan-min').value;
                const max = document.getElementById('pertanyaan-max').value;
                
                if (!min || !max) {
                    errors.push('Min dan Max harus diisi untuk tipe ini');
                } else {
                    const minVal = parseInt(min);
                    const maxVal = parseInt(max);
                    if (isNaN(minVal) || isNaN(maxVal)) {
                        errors.push('Min dan Max harus berupa angka');
                    }
                    if (minVal >= maxVal) {
                        errors.push('Min harus lebih kecil dari Max');
                    }
                }
            }
            
            return errors;
        }

        function addOpsiInput() {
            const container = document.getElementById('pertanyaan-opsiContainer');
            const uniqueId = 'opsi-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            const count = container.querySelectorAll('[data-opsi-id]').length + 1;
            const html = `
                <div class="pertanyaan-opsi-item" data-opsi-id="${uniqueId}">
                    <input type="text" class="pertanyaan-form-input opsi-input" name="opsi[]" placeholder="Opsi ${count}" required>
                    <button class="pertanyaan-opsi-delete" type="button" onclick="this.closest('[data-opsi-id]').remove(); updateSkipDropdown('pertanyaan-skipIfAnswer');" title="Hapus opsi">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            const newInput = container.querySelector('[data-opsi-id="' + uniqueId + '"] .opsi-input');
            if (newInput) {
                newInput.focus();
                // Update skip dropdown when new option is added
                newInput.addEventListener('change', () => populateSkipOptions(document.getElementById('pertanyaan-tipeInput').value));
                newInput.addEventListener('blur', () => populateSkipOptions(document.getElementById('pertanyaan-tipeInput').value));
            }
        }

        async function submitData(url, method, formData) {
            try {
                // Collect opsi_jawaban from opsi-input elements as FormData fields
                const opsiInputs = document.querySelectorAll('.opsi-input');
                const opsiValues = Array.from(opsiInputs)
                    .map(input => input.value.trim())
                    .filter(val => val !== '');
                
                // Remove old opsi_jawaban fields
                formData.delete('opsi_jawaban');
                
                // Add opsi values as separate FormData fields
                opsiValues.forEach((val, idx) => {
                    formData.append('opsi_jawaban[' + idx + '][label]', val);
                    formData.append('opsi_jawaban[' + idx + '][value]', val);
                });

                // Collect conditional questions data for yesno type
                const tipeInput = document.getElementById('pertanyaan-tipeInput');
                if (tipeInput && tipeInput.value === 'yesno') {
                    const condLabels = document.querySelectorAll('.conditional-label');
                    const condTipes = document.querySelectorAll('.conditional-tipe');
                    const condShowWhens = document.querySelectorAll('.conditional-show-when');
                    
                    // Remove old conditional fields
                    const keysToRemove = Array.from(formData.keys()).filter(key => key.startsWith('conditional_'));
                    keysToRemove.forEach(key => formData.delete(key));
                    
                    // Add new conditional fields
                    condLabels.forEach((label, idx) => {
                        if (label.value.trim()) {
                            formData.append(`conditional_label[${idx}]`, label.value.trim());
                            formData.append(`conditional_tipe[${idx}]`, condTipes[idx]?.value || '');
                            formData.append(`conditional_show_when[${idx}]`, condShowWhens[idx]?.value || 'keduanya');
                        }
                    });
                }

                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                const submitBtn = document.getElementById('pertanyaanForm')?.querySelector('button[type="submit"]');
                if (!submitBtn) throw new Error('Submit button not found');
                
                const originalHTML = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData
                });

                // Check response status BEFORE parsing JSON
                if (!response.ok) {
                    const errorText = await response.text();
                    let errorMsg = 'Gagal menyimpan data';
                    try {
                        const errorData = JSON.parse(errorText);
                        if (errorData.error) {
                            // Handle validation errors (array of field errors)
                            if (typeof errorData.error === 'object') {
                                const errorLines = Object.values(errorData.error)
                                    .flat()
                                    .map(err => '• ' + err);
                                errorMsg = errorLines.join('\n');
                            } else {
                                // Handle single error message
                                errorMsg = errorData.error;
                            }
                        } else if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                    } catch (e) {
                        errorMsg = 'Server error: ' + response.status;
                    }
                    throw new Error(errorMsg);
                }

                const data = await response.json();
                hideModal("pertanyaanCreateModal");
                showToast(data.message || 'Data berhasil disimpan', 'success');
                setTimeout(() => location.reload(), 1500);
                
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Terjadi kesalahan', 'error');
                const submitBtn = document.getElementById('pertanyaanForm')?.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan';
                }
            }
        }

        function showModal(modalId) {
            const el = document.getElementById(modalId);
            if (!el) return;
            el.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideModal(modalId) {
            const el = document.getElementById(modalId);
            if (!el) return;
            el.classList.remove('active');
            document.body.style.overflow = '';
            
            // Clear form if closing create/edit modal
            if (modalId === 'pertanyaanCreateModal') {
                clearForm('pertanyaanForm');
            }
        }

        function clearForm(formId) {
            const form = document.getElementById(formId);
            if (form) form.reset();
            
            const minMaxGroup = document.getElementById('pertanyaan-minMaxGroup');
            const opsiGroup = document.getElementById('pertanyaan-opsiGroup');
            const conditionalGroup = document.getElementById('pertanyaan-conditionalGroup');
            const allowLainnyaGroup = document.getElementById('pertanyaan-allowLainnyaGroup');
            const opsiContainer = document.getElementById('pertanyaan-opsiContainer');
            
            if (minMaxGroup) minMaxGroup.style.display = 'none';
            if (opsiGroup) opsiGroup.style.display = 'none';
            if (conditionalGroup) conditionalGroup.style.display = 'none';
            if (allowLainnyaGroup) allowLainnyaGroup.style.display = 'none';
            if (opsiContainer) opsiContainer.innerHTML = '';
            
            clearConditionalQuestions();
            
            // Clear validation error styles if any
            if (form) {
                form.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            }
        }

        function updateTipeInputUI(selectId) {
            const tipe = document.getElementById(selectId).value;
            const minMaxGroup = document.getElementById('pertanyaan-minMaxGroup');
            const opsiGroup = document.getElementById('pertanyaan-opsiGroup');
            const conditionalGroup = document.getElementById('pertanyaan-conditionalGroup');
            const skipGroup = document.getElementById('pertanyaan-skipGroup');
            const allowLainnyaGroup = document.getElementById('pertanyaan-allowLainnyaGroup');
            
            if (minMaxGroup) {
                minMaxGroup.style.display = (tipe === 'number' || tipe === 'skala') ? 'block' : 'none';
            }
            if (opsiGroup) {
                opsiGroup.style.display = (['radio', 'checkbox', 'select'].includes(tipe)) ? 'block' : 'none';
            }
            if (conditionalGroup) {
                conditionalGroup.style.display = (tipe === 'yesno') ? 'block' : 'none';
            }
            if (skipGroup) {
                skipGroup.style.display = (['yesno', 'radio', 'checkbox', 'select'].includes(tipe)) ? 'block' : 'none';
            }
            if (allowLainnyaGroup) {
                allowLainnyaGroup.style.display = (tipe === 'checkbox') ? 'block' : 'none';
            }
            
            // Populate skip dropdown based on question type
            populateSkipOptions(tipe);
        }

        function populateSkipOptions(tipe) {
            const skipSelect = document.getElementById('pertanyaan-skipIfAnswer');
            if (!skipSelect) return;
            
            const currentValue = skipSelect.value;
            let options = ['<option value="">-- Tidak ada skip --</option>'];
            
            if (tipe === 'yesno') {
                options.push('<option value="ya">Ya</option>');
                options.push('<option value="tidak">Tidak</option>');
            } else if (['radio', 'checkbox', 'select'].includes(tipe)) {
                // For multiple choice types, get options from opsi_jawaban inputs
                const opsiInputs = document.querySelectorAll('.opsi-input');
                opsiInputs.forEach((input, idx) => {
                    const val = input.value.trim();
                    if (val) {
                        options.push(`<option value="${val}">${val}</option>`);
                    }
                });
            }
            
            skipSelect.innerHTML = options.join('');
            skipSelect.value = currentValue; // Try to restore previous value
        }

        function updateSkipDropdown(selectId) {
            // This function is called when skip dropdown changes
            // Can be used for validation or other logic in future
        }

        function loadOpsiOptions(options) {
            const container = document.getElementById('pertanyaan-opsiContainer');
            container.innerHTML = '';
            (Array.isArray(options) ? options : []).forEach((opt, idx) => {
                const label = opt.label || opt.value || opt;
                container.innerHTML += `
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control opsi-input" name="opsi[]" value="${label}" placeholder="Opsi ${idx + 1}">
                        <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });
        }

        function getQuestionTypeLabel(tipe) {
            const labels = {
                'text': 'Teks Pendek',
                'textarea': 'Teks Panjang',
                'number': 'Angka',
                'radio': 'Pilihan Ganda',
                'checkbox': 'Pilihan Banyak',
                'select': 'Dropdown',
                'yesno': 'Ya/Tidak',
                'skala': 'Skala'
            };
            return labels[tipe] || tipe;
        }

        // Conditional Questions Functions
        let conditionalQuestionCounter = 0;

        function addConditionalQuestion() {
            const container = document.getElementById('pertanyaan-conditionalContainer');
            const idx = conditionalQuestionCounter++;
            
            const html = `
                <div class="conditional-question-item" id="condQ_${idx}" style="background-color: white; padding: 12px; border-radius: 4px; margin-bottom: 12px; border: 1px solid #dee2e6;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <small style="color: #6c757d; font-weight: 500;">Pertanyaan Lanjutan ${idx + 1}</small>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeConditionalQuestion(${idx})">
                            <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 0.85rem; color: #495057; font-weight: 500; margin-bottom: 5px; display: block;">
                            Pertanyaan <span style="color: red;">*</span>
                        </label>
                        <textarea class="conditional-label" name="conditional_label[]" 
                                  style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9rem; font-family: inherit;" 
                                  rows="2" placeholder="Ketik pertanyaan lanjutan..." required></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 0.85rem; color: #495057; font-weight: 500; margin-bottom: 5px; display: block;">
                                Tipe Input <span style="color: red;">*</span>
                            </label>
                            <select class="conditional-tipe" name="conditional_tipe[]" 
                                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9rem;" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="text">📝 Teks Pendek</option>
                                <option value="textarea">📄 Teks Panjang</option>
                                <option value="number">🔢 Angka</option>
                                <option value="radio">⭕ Pilihan Ganda</option>
                                <option value="checkbox">☑️ Pilihan Banyak</option>
                                <option value="select">📋 Dropdown</option>
                            </select>
                        </div>
                        
                        <div>
                            <label style="font-size: 0.85rem; color: #495057; font-weight: 500; margin-bottom: 5px; display: block;">
                                Tampilkan Jika Jawaban <span style="color: red;">*</span>
                            </label>
                            <select class="conditional-show-when" name="conditional_show_when[]" 
                                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9rem;" required>
                                <option value="ya">Ya</option>
                                <option value="tidak">Tidak</option>
                                <option value="keduanya">Keduanya</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeConditionalQuestion(idx) {
            const el = document.getElementById(`condQ_${idx}`);
            if (el) el.remove();
        }

        function clearConditionalQuestions() {
            const container = document.getElementById('pertanyaan-conditionalContainer');
            if (container) container.innerHTML = '';
            conditionalQuestionCounter = 0;
        }

        function openCreateModal() {
            clearForm('pertanyaanForm');
            
            // Reset aspek dan indikator dropdowns
            document.getElementById('pertanyaan-aspek').value = '';
            document.getElementById('pertanyaan-indikator').value = '';
            document.getElementById('pertanyaan-indikator').disabled = true;
            document.getElementById('pertanyaan-indikator').innerHTML = '<option value="">-- Pilih Indikator --</option>';
            
            const label = document.getElementById('pertanyaan-createModalLabel');
            if (label) label.textContent = 'Tambah Pertanyaan Baru';
            
            const form = document.getElementById('pertanyaanForm');
            if (form) {
                form.removeAttribute('data-edit-id');
                form.dataset.mode = 'create';
            }
            
            showModal('pertanyaanCreateModal');
        }

        async function openEditModal(id) {
            try {
                // CLEAR FORM DULU SEBELUM POPULATE
                clearForm('pertanyaanForm');
                
                const response = await fetch('{{ route("admin.f01.pertanyaan.show", ":id") }}'.replace(':id', id), {
                    headers: { 'Accept': 'application/json' }
                });
                
                // Check response status BEFORE parsing JSON
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                }
                
                const data = await response.json();
                if (!data || !data.data) {
                    throw new Error('Invalid response format');
                }
                
                const p = data.data;
                
                populateEditForm(p);
                
                const label = document.getElementById('pertanyaan-createModalLabel');
                if (label) label.textContent = 'Edit Pertanyaan: ' + p.kode;
                
                const form = document.getElementById('pertanyaanForm');
                if (form) {
                    form.dataset.editId = id;
                    form.dataset.mode = 'edit';
                }
                
                showModal('pertanyaanCreateModal');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Gagal memuat data pertanyaan', 'error');
            }
        }

        function populateEditForm(data) {
            // Set aspek dari indikator relationship
            if (data.indikator && data.indikator.aspek_id) {
                document.getElementById('pertanyaan-aspek').value = data.indikator.aspek_id;
                // Load indikators untuk aspek ini, kemudian set selected indikator
                loadIndicatorsByAspek(data.indikator_id);
            } else {
                document.getElementById('pertanyaan-aspek').value = '';
                document.getElementById('pertanyaan-indikator').disabled = true;
            }
            
            document.getElementById('pertanyaan-kode').value = data.kode;
            document.getElementById('pertanyaan-label').value = data.label;
            document.getElementById('pertanyaan-tipeInput').value = data.tipe_input;
            document.getElementById('pertanyaan-wajib').checked = data.wajib;
            document.getElementById('pertanyaan-aktif').checked = data.aktif;
            document.getElementById('pertanyaan-allowLainnya').checked = data.allow_lainnya;
            document.getElementById('pertanyaan-urutan').value = data.urutan;
            
            if (data.tipe_input === 'number' || data.tipe_input === 'skala') {
                document.getElementById('pertanyaan-minMaxGroup').style.display = 'block';
                document.getElementById('pertanyaan-min').value = data.min || '';
                document.getElementById('pertanyaan-max').value = data.max || '';
            }

            if (['radio', 'checkbox', 'select'].includes(data.tipe_input)) {
                document.getElementById('pertanyaan-opsiGroup').style.display = 'block';
                loadOpsiOptions(data.opsi_jawaban || []);
            }

            // Load conditional questions if yesno type
            if (data.tipe_input === 'yesno' && data.conditional_questions && data.conditional_questions.length > 0) {
                loadConditionalQuestions(data.conditional_questions);
            }

            // Set skip_if_answer if present
            if (data.skip_if_answer) {
                const skipSelect = document.getElementById('pertanyaan-skipIfAnswer');
                if (skipSelect) {
                    populateSkipOptions(data.tipe_input);
                    skipSelect.value = data.skip_if_answer;
                }
            }

            updateTipeInputUI('pertanyaan-tipeInput');
        }

        function loadConditionalQuestions(conditionalQuestions) {
            clearConditionalQuestions();
            
            conditionalQuestions.forEach((cq, idx) => {
                // Create the HTML structure for conditional question
                const rowDiv = document.createElement('div');
                rowDiv.id = `condQ_${idx}`;
                rowDiv.className = 'conditional-question-row';
                
                const labelDiv = document.createElement('div');
                labelDiv.className = 'pertanyaan-form-group';
                const labelLabel = document.createElement('label');
                labelLabel.className = 'pertanyaan-form-label';
                labelLabel.textContent = 'Pertanyaan Lanjutan';
                const labelTextarea = document.createElement('textarea');
                labelTextarea.name = 'conditional_label[]';
                labelTextarea.className = 'pertanyaan-form-input';
                labelTextarea.rows = '2';
                labelTextarea.placeholder = 'Pertanyaan lanjutan...';
                labelTextarea.value = cq.label;
                labelDiv.appendChild(labelLabel);
                labelDiv.appendChild(labelTextarea);
                
                const tipeDiv = document.createElement('div');
                tipeDiv.className = 'pertanyaan-form-group';
                const tipeLabel = document.createElement('label');
                tipeLabel.className = 'pertanyaan-form-label';
                tipeLabel.textContent = 'Jenis Input';
                const tipeSelect = document.createElement('select');
                tipeSelect.name = 'conditional_tipe[]';
                tipeSelect.className = 'pertanyaan-form-input';
                tipeSelect.innerHTML = `
                    <option value="text" ${cq.tipe_input === 'text' ? 'selected' : ''}>Text</option>
                    <option value="number" ${cq.tipe_input === 'number' ? 'selected' : ''}>Number</option>
                    <option value="textarea" ${cq.tipe_input === 'textarea' ? 'selected' : ''}>Textarea</option>
                    <option value="radio" ${cq.tipe_input === 'radio' ? 'selected' : ''}>Radio</option>
                    <option value="checkbox" ${cq.tipe_input === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                    <option value="select" ${cq.tipe_input === 'select' ? 'selected' : ''}>Dropdown</option>
                `;
                tipeDiv.appendChild(tipeLabel);
                tipeDiv.appendChild(tipeSelect);
                
                const showDiv = document.createElement('div');
                showDiv.className = 'pertanyaan-form-group';
                const showLabel = document.createElement('label');
                showLabel.className = 'pertanyaan-form-label';
                showLabel.textContent = 'Tampilkan ketika jawaban:';
                showDiv.appendChild(showLabel);
                
                const radioContainer = document.createElement('div');
                radioContainer.style.display = 'flex';
                radioContainer.style.gap = '15px';
                
                ['ya', 'tidak', 'keduanya'].forEach(val => {
                    const label = document.createElement('label');
                    label.style.display = 'flex';
                    label.style.alignItems = 'center';
                    label.style.gap = '5px';
                    label.style.cursor = 'pointer';
                    
                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = `conditional_show_when[${idx}]`;
                    input.value = val;
                    input.checked = cq.show_when === val;
                    
                    const span = document.createElement('span');
                    span.textContent = val.charAt(0).toUpperCase() + val.slice(1);
                    
                    label.appendChild(input);
                    label.appendChild(span);
                    radioContainer.appendChild(label);
                });
                
                showDiv.appendChild(radioContainer);
                
                const btnDiv = document.createElement('div');
                btnDiv.style.marginTop = '10px';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-danger btn-sm';
                removeBtn.textContent = 'Hapus';
                removeBtn.onclick = () => removeConditionalQuestion(idx);
                btnDiv.appendChild(removeBtn);
                
                const container = document.getElementById('pertanyaan-conditionalContainer');
                if (container) {
                    const itemDiv = document.createElement('div');
                    itemDiv.style.padding = '10px';
                    itemDiv.style.marginBottom = '10px';
                    itemDiv.style.backgroundColor = '#f0f8ff';
                    itemDiv.style.borderRadius = '4px';
                    itemDiv.appendChild(labelDiv);
                    itemDiv.appendChild(tipeDiv);
                    itemDiv.appendChild(showDiv);
                    itemDiv.appendChild(btnDiv);
                    container.appendChild(itemDiv);
                }
            });
            
            // Update counter
            const counter = document.getElementById('conditionalQuestionCounter') || {value: 0};
            counter.value = Math.max(counter.value, conditionalQuestions.length);
        }

        async function viewDetail(id) {
            try {
                const response = await fetch('{{ route("admin.f01.pertanyaan.show", ":id") }}'.replace(':id', id), {
                    headers: { 'Accept': 'application/json' }
                });
                
                // Check response status BEFORE parsing JSON
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                }
                
                const data = await response.json();
                if (!data || !data.data) {
                    throw new Error('Invalid response format');
                }
                
                const p = data.data;
                const tipeLabel = getQuestionTypeLabel(p.tipe_input);
                
                let html = '<div class="detail-group"><label class="detail-label">Kode Pertanyaan</label><p class="detail-value">' + p.kode + '</p></div>';
                html += '<div class="detail-group"><label class="detail-label">Indikator</label><p class="detail-value">' + (p.indikator ? '[' + p.indikator.kode + '] ' + p.indikator.nama : '-') + '</p></div>';
                html += '<div class="detail-group"><label class="detail-label">Pertanyaan (Label)</label><p class="detail-value">' + p.label + '</p></div>';
                html += '<div class="detail-group"><label class="detail-label">Tipe Input</label><p class="detail-value"><span class="badge bg-info">' + tipeLabel + '</span></p></div>';

                if (p.tipe_input === 'number' || p.tipe_input === 'skala') {
                    html += '<div class="detail-group"><label class="detail-label">Rentang Nilai</label><p class="detail-value">' + (p.min || '-') + ' sampai ' + (p.max || '-') + '</p></div>';
                }

                if (['radio', 'checkbox', 'select'].includes(p.tipe_input) && p.opsi_jawaban) {
                    html += '<div class="detail-group"><label class="detail-label">Opsi Jawaban</label><ul class="detail-value" style="list-style: none; padding: 0;">';
                    (Array.isArray(p.opsi_jawaban) ? p.opsi_jawaban : []).forEach(opt => {
                        const label = opt.label || opt.value || opt;
                        html += '<li><i class="fas fa-check-circle text-success me-2" aria-hidden="true"></i>' + label + '</li>';
                    });
                    html += '</ul></div>';
                }

                html += '<div class="detail-group"><label class="detail-label">Wajib Diisi</label><p class="detail-value"><span class="badge ' + (p.wajib ? 'bg-warning text-dark' : 'bg-secondary') + '">' + (p.wajib ? 'Ya' : 'Tidak') + '</span></p></div>';
                html += '<div class="detail-group"><label class="detail-label">Status</label><p class="detail-value"><span class="badge ' + (p.aktif ? 'bg-success' : 'bg-danger') + '">' + (p.aktif ? 'Aktif' : 'Nonaktif') + '</span></p></div>';
                html += '<div class="detail-group"><label class="detail-label">Urutan</label><p class="detail-value">#' + p.urutan + '</p></div>';

                const detailContent = document.getElementById('pertanyaan-detailContent');
                if (detailContent) {
                    detailContent.innerHTML = html;
                    showModal('pertanyaanDetailModal');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Gagal memuat detail pertanyaan', 'error');
            }
        }

        async function openDeleteModal(id) {
            try {
                const response = await fetch('{{ route("admin.f01.pertanyaan.show", ":id") }}'.replace(':id', id), {
                    headers: { 'Accept': 'application/json' }
                });
                
                // Check response status BEFORE parsing JSON
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                }
                
                const data = await response.json();
                if (!data || !data.data) {
                    throw new Error('Invalid response format');
                }
                
                deleteData = { 
                    id: id, 
                    label: data.data.label,
                    route: '{{ route("admin.f01.pertanyaan.destroy", ":id") }}'.replace(':id', id)
                };
                
                // Update delete modal content with question label
                const deleteText = document.getElementById('pertanyaan-deleteText');
                if (deleteText) {
                    deleteText.innerHTML = '<div class="mb-2">Anda yakin ingin menghapus:</div><div class="bg-light p-3 rounded border-start border-4 border-danger"><strong>"' + (data.data.label.substring(0, 150)) + (data.data.label.length > 150 ? '..."' : '"') + '</strong></div>';
                }
                
                showModal('pertanyaanDeleteModal');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Gagal memuat data pertanyaan', 'error');
            }
        }

        function executeDelete() {
            if (!deleteData) return;
            
            const btn = document.getElementById('pertanyaan-delete-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menghapus...';
            
            fetch(deleteData.route, {
                method: 'DELETE',
                headers: { 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                }
            })
            .then(r => r.json())
            .then(data => {
                hideModal('pertanyaanDeleteModal');
                showToast(data.message || 'Pertanyaan berhasil dihapus', 'success');
                setTimeout(() => location.reload(), 500);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash me-2"></i> Hapus Selamanya';
                showToast(err?.message || 'Gagal menghapus pertanyaan', 'error');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Form submit handler - unified logic for create and edit
            const form = document.getElementById('pertanyaanForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // VALIDATE FORM DULU!
                    const errors = validateForm(this);
                    if (errors.length > 0) {
                        showToast(errors.join('\n'), 'error');
                        return;
                    }
                    
                    const isEdit = this.dataset.editId;
                    const url = isEdit 
                        ? '{{ route("admin.f01.pertanyaan.update", ":id") }}'.replace(':id', this.dataset.editId)
                        : '{{ route("admin.f01.pertanyaan.store") }}';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    submitData(url, method, new FormData(this));
                });
            }

            // Initialize Sortable for reordering with visual feedback and error handling
            const sortableElement = document.getElementById('pertanyaanSortable');
            if (sortableElement && sortableElement.children.length > 0) {
                sortable = new Sortable(sortableElement, {
                    handle: '.fa-grip-vertical',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: async function(evt) {
                        const originalOpacity = evt.item.style.opacity;
                        evt.item.style.opacity = '0.5';
                        sortable.option('disabled', true);
                        
                        try {
                            const order = Array.from(sortableElement.querySelectorAll('[data-id]'))
                                .map(el => parseInt(el.dataset.id));
                            
                            const response = await fetch('{{ route("admin.f01.pertanyaan.reorder") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ order: order })
                            });
                            
                            if (!response.ok) throw new Error('Gagal mengubah urutan');
                            
                            const data = await response.json();
                            evt.item.style.opacity = originalOpacity;
                            showToast('Urutan pertanyaan berhasil diperbarui', 'success');
                        } catch (error) {
                            // Rollback position
                            sortable.sort(sortable.toArray());
                            evt.item.style.opacity = originalOpacity;
                            showToast(error.message || 'Gagal mengubah urutan', 'error');
                        } finally {
                            sortable.option('disabled', false);
                        }
                    }
                });
            }

            // Cascading dropdown: Load indicators by aspek
            window.loadIndicatorsByAspek = async function(selectedIndicatorId = null) {
                const aspekSelect = document.getElementById('pertanyaan-aspek');
                const indikatorSelect = document.getElementById('pertanyaan-indikator');
                const aspekId = aspekSelect.value;
                
                // Reset indikator if no aspek selected
                if (!aspekId) {
                    indikatorSelect.innerHTML = '<option value="">-- Pilih Indikator --</option>';
                    indikatorSelect.disabled = true;
                    indikatorSelect.value = '';
                    return;
                }
                
                try {
                    // Show loading state
                    indikatorSelect.innerHTML = '<option value="">Loading...</option>';
                    indikatorSelect.disabled = true;
                    
                    // Fetch indicators for this aspek
                    const response = await fetch('{{ route("admin.f01.get-indicators-by-aspek", ":id") }}'.replace(':id', aspekId), {
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Gagal memuat indikator');
                    }
                    
                    const result = await response.json();
                    
                    if (result.success && result.data.length > 0) {
                        // Build options
                        let html = '<option value="">-- Pilih Indikator --</option>';
                        result.data.forEach(ind => {
                            html += `<option value="${ind.id}">${ind.display}</option>`;
                        });
                        
                        indikatorSelect.innerHTML = html;
                        indikatorSelect.disabled = false;
                        
                        // Set selected indicator if provided (for edit mode)
                        if (selectedIndicatorId) {
                            indikatorSelect.value = selectedIndicatorId;
                        }
                    } else {
                        indikatorSelect.innerHTML = '<option value="">Tidak ada Indikator</option>';
                        indikatorSelect.disabled = true;
                    }
                } catch (error) {
                    console.error('Error loading indicators:', error);
                    indikatorSelect.innerHTML = '<option value="">Gagal memuat indikator</option>';
                    indikatorSelect.disabled = true;
                    showToast(error.message || 'Gagal memuat indikator', 'error');
                }
            };

            // Standardized modal close: overlay click, ESC key, and close button
            document.querySelectorAll('.pertanyaan-modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideModal(this.id);
                    }
                });
            });

            // ESC key handler to close active modal
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const activeModal = document.querySelector('.pertanyaan-modal-overlay.active');
                    if (activeModal) {
                        hideModal(activeModal.id);
                    }
                }
            });

            // Delete button event delegation (cleaner than multiple listeners)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-btn')) {
                    const id = e.target.closest('.delete-btn').dataset.id;
                    openDeleteModal(id);
                }
            });
        });
    </script>
@endpush

<script>
{!! file_get_contents(public_path('js/multi-sort.js')) !!}
</script>

@endsection
