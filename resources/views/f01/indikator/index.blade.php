@extends('layouts.app')
@section('title','Indikator')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('components.crud-table.css', ['prefix' => 'indikator'])

<div class="indikator-container">
    @include('components.crud-table.header', [
        'prefix' => 'indikator',
        'title' => 'Manajemen Indikator',
        'subtitle' => 'Kelola indikator penilaian untuk setiap aspek',
        'buttonText' => 'Tambah Indikator',
        'buttonAction' => 'openCreateModal()'
    ])

    @include('components.crud-table.stats', [
        'prefix' => 'indikator',
        'stats' => [
            ['label' => 'Total Indikator', 'value' => $indikator->total()],
            ['label' => 'Indikator Aktif', 'value' => $indikator->where('aktif', 1)->count()]
        ]
    ])

    @include('components.crud-table.table-card', [
        'prefix' => 'indikator',
        'tableTitle' => 'Daftar Indikator',
        'tableId' => 'indikatorTable'
    ])

    @include('components.crud-table.search', [
        'prefix' => 'indikator',
        'searchInputId' => 'indikatorSearch',
        'tableId' => 'indikatorTable'
    ])
    
    <div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; justify-content: flex-end; background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form method="GET" action="{{ route('admin.f01.indikator.index') }}" style="display: flex; gap: 0.75rem; align-items: center;">
            <label for="filter_periode" style="font-size: 14px; font-weight: 500; color: #475569;">Filter Periode:</label>
            <select name="periode_id" id="filter_periode" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; min-width: 200px; outline: none; transition: border-color 0.2s;" onchange="this.form.submit()" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
                <option value="">Semua Periode</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓' : '' }}
                    </option>
                @endforeach
            </select>
            
            <label for="filter_aspek" style="font-size: 14px; font-weight: 500; color: #475569; margin-left: 1rem;">Aspek:</label>
            <select name="aspek_id" id="filter_aspek" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; min-width: 200px; outline: none; transition: border-color 0.2s;" onchange="this.form.submit()" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
                <option value="">Semua Aspek</option>
                @foreach($aspeks as $a)
                    <option value="{{ $a->id }}" {{ request('aspek_id') == $a->id ? 'selected' : '' }}>
                        [{{ $a->kode }}] {{ Str::limit($a->nama, 40) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <table class="indikator-table" id="indikatorTable">
        <thead>
            <tr>
                <th style="width: 80px;">Kode</th>
                <th>Nama Indikator</th>
                <th style="width: 120px;">Aspek</th>
                <th style="width: 80px;">Bobot</th>
                <th style="width: 80px;">Status</th>
                <th style="width: 100px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($indikator as $ind)
            <tr data-id="{{ $ind->id }}" class="indikator-row">
                <td><strong>{{ $ind->kode }}</strong></td>
                <td>{{ Str::limit($ind->nama, 60) }}</td>
                <td>
                    @if($ind->aspek)
                        <span class="indikator-badge indikator-badge-info">[{{ $ind->aspek->kode }}]</span>
                    @else
                        <span class="indikator-badge indikator-badge-secondary">-</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $ind->bobot ?? '-' }}</td>
                <td style="text-align: center;">
                    @if($ind->aktif)
                        <span class="indikator-badge indikator-badge-success">Aktif</span>
                    @else
                        <span class="indikator-badge indikator-badge-secondary">Nonaktif</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <div class="indikator-action-buttons">
                        <button class="indikator-btn-icon btn-info" onclick="viewDetail({{ $ind->id }})" title="Lihat detail">
                            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="12" r="10"/><path d="M12 6v6m0 0l4-4m-4 4l-4-4"/></svg>
                        </button>
                        <button class="indikator-btn-icon btn-warning" onclick="openEditModal({{ $ind->id }})" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="indikator-btn-icon btn-danger delete-btn" data-id="{{ $ind->id }}" title="Hapus">
                            <svg viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 24px;">
                    <div style="color: #64748b; font-size: 14px;">
                        <i class="fas fa-inbox" style="margin-right: 8px;"></i>
                        Tidak ada indikator
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('components.crud-table.pagination', [
        'prefix' => 'indikator',
        'data' => $indikator
    ])
</div>

<!-- Modals -->
@include('f01.indikator.modals.create', ['aspeks' => $aspeks])
@include('f01.indikator.modals.detail')
@include('f01.indikator.modals.delete')

<!-- Toast Notification -->
<div class="indikator-toast" id="indikator-toast"></div>

@push('styles')
    <style>
        /* Modal size variations */
        .indikator-modal-lg {
            max-width: 700px;
        }

        .indikator-modal-sm {
            max-width: 450px;
        }

        /* Modal header danger style */
        .indikator-modal-header-danger {
            background: #dc2626;
            color: white;
            border-bottom-color: #b91c1c;
        }

        .indikator-modal-header-danger .indikator-modal-title {
            color: white;
        }

        /* Text muted */
        .indikator-text-muted {
            font-size: 13px;
            color: #64748b;
            display: block;
            margin-top: 4px;
        }

        /* Badge styles */
        .indikator-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .indikator-badge-info {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .indikator-badge-success {
            background: #dcfce7;
            color: #166534;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .indikator-badge-success::before {
            content: "●";
            font-size: 8px;
        }

        .indikator-badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .indikator-badge-light {
            background: #f1f5f9;
            color: #475569;
        }

        .indikator-badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        /* Drag handle styling */
        .indikator-drag-handle {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            cursor: grab;
            color: #94a3b8;
            font-weight: bold;
        }

        .indikator-row:active .indikator-drag-handle {
            cursor: grabbing;
        }

        /* Form styling */
        .indikator-form-hidden {
            display: none;
        }

        .indikator-form-group {
            margin-bottom: 1.25rem;
        }

        .indikator-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .indikator-form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .indikator-form-label .required {
            color: #dc2626;
        }

        .indikator-form-input {
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

        .indikator-form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .indikator-form-input.is-invalid {
            border-color: #dc2626;
            background-color: #fef2f2;
        }

        .indikator-text-error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        /* Action buttons styling */
        .indikator-action-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .indikator-btn-icon {
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

        .indikator-btn-icon:hover {
            background: #e2e8f0;
            color: #334155;
            transform: translateY(-2px);
        }

        .indikator-btn-icon:active {
            transform: translateY(0);
        }

        .indikator-btn-icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .indikator-btn-icon.btn-danger {
            color: #dc2626;
            background: #fef2f2;
        }

        .indikator-btn-icon.btn-danger:hover {
            background: #fee2e2;
            color: #991b1b;
        }

        .indikator-btn-icon.btn-warning {
            color: #ea580c;
            background: #fef3c7;
        }

        .indikator-btn-icon.btn-warning:hover {
            background: #fde68a;
            color: #d97706;
        }

        .indikator-btn-icon.btn-info {
            color: #0284c7;
            background: #dbeafe;
        }

        .indikator-btn-icon.btn-info:hover {
            background: #bfdbfe;
            color: #0c4a6e;
        }

        /* Custom table styling */
        .indikator-table tbody td {
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
        .indikator-modal-overlay {
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

        .indikator-modal-overlay.active {
            display: flex;
        }

        .indikator-modal {
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
        .indikator-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .indikator-modal-title {
            font-size: 1.375rem;
            font-weight: 600;
            margin: 0;
            color: #1f2937;
        }

        .indikator-modal-close {
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

        .indikator-modal-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        /* Modal Body */
        .indikator-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }

        /* Modal Footer */
        .indikator-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        /* Button Group Styling */
        .indikator-btn {
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

        .indikator-btn-primary {
            background: #2563eb;
            color: white;
        }

        .indikator-btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .indikator-btn-primary:active {
            transform: translateY(0);
        }

        .indikator-btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .indikator-btn-secondary:hover {
            background: #d1d5db;
            transform: translateY(-1px);
        }

        .indikator-btn-secondary:active {
            transform: translateY(0);
        }

        .indikator-btn-danger {
            background: #dc2626;
            color: white;
        }

        .indikator-btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .indikator-btn-danger:active {
            transform: translateY(0);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let deleteData = null;
        let sortable = null;

        // ===== HELPER FUNCTIONS =====
        
        // Escape HTML untuk security
        function escapeHtml(str) {
            if (!str) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, m => map[m]);
        }

        // Update character counter for textarea
        function updateCharCount(textareaId, counterId) {
            const textarea = document.getElementById(textareaId);
            const counter = document.getElementById(counterId);
            if (textarea && counter) {
                const maxLength = parseInt(textarea.getAttribute('maxlength')) || 0;
                const currentLength = textarea.value.length;
                counter.textContent = currentLength + ' / ' + maxLength;
                
                // Change color if near limit
                if (maxLength > 0 && currentLength >= maxLength * 0.9) {
                    counter.style.color = '#ff5733';
                } else {
                    counter.style.color = '#888';
                }
            }
        }

        // Format deskripsi: /n untuk narasi, tanpa /n untuk poin bernomor
        function formatDeskripsi(rawText) {
            if (!rawText) return '';
            
            console.log('=== formatDeskripsi START ===');
            console.log('Input text:', rawText.substring(0, 200));
            
            // Normalize line breaks
            let text = rawText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            let lines = text.split('\n');
            
            console.log('Total lines:', lines.length);
            lines.forEach((l, i) => {
                console.log(`  Line[${i}]: ${JSON.stringify(l.substring(0, 60))}`);
            });
            
            let result = '';
            let pointCount = 0;
            
            for (let i = 0; i < lines.length; i++) {
                let line = lines[i].trim();
                
                // Skip empty lines
                if (!line) {
                    console.log(`Line[${i}]: EMPTY - skip`);
                    continue;
                }
                
                // Check if narasi (starts with /n)
                if (line.startsWith('/n')) {
                    // Narasi - remove /n marker
                    let content = line.substring(2).trim();
                    console.log(`Line[${i}]: NARASI - content: ${JSON.stringify(content.substring(0, 60))}`);
                    if (content) {
                        result += '<div style="margin-bottom: 0.8rem; text-align: justify; color: #555; line-height: 1.6;">' 
                                + escapeHtml(content) 
                                + '</div>';
                    }
                    pointCount = 0; // Reset numbering
                } else {
                    // Poin - auto-number
                    pointCount++;
                    console.log(`Line[${i}]: POIN #${pointCount} - ${JSON.stringify(line.substring(0, 60))}`);
                    result += '<div style="margin-bottom: 0.8rem; margin-left: 1.5rem; color: #555; line-height: 1.6;">'
                            + pointCount + '. ' + escapeHtml(line)
                            + '</div>';
                }
            }
            
            console.log('=== formatDeskripsi END ===');
            console.log('Output HTML:', result.substring(0, 300));
            return result;
        }

        function showToast(message, type = 'info') {
            let container = document.getElementById('indikator-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'indikator-toast-container';
                container.style.cssText = 'position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 10000; display: flex; flex-direction: column; gap: 0.75rem; max-width: calc(100vw - 3rem);';
                document.body.appendChild(container);
            }
            
            const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
            
            const toast = document.createElement('div');
            toast.className = 'alert ' + alertClass + ' d-flex align-items-center gap-2 mb-0';
            toast.style.cssText = 'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 0.375rem; max-width: 400px; word-wrap: break-word; animation: slideIn 0.3s ease-out;';
            toast.innerHTML = '<i class="fas fa-' + icon + '" aria-hidden="true" style="flex-shrink: 0;"></i><span style="flex: 1; white-space: pre-wrap;">' + message + '</span><button type="button" class="btn-close btn-sm" aria-label="Tutup" style="flex-shrink: 0;"></button>';
            
            container.appendChild(toast);
            
            const closeBtn = toast.querySelector('.btn-close');
            const closeToast = () => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            };
            
            closeBtn?.addEventListener('click', closeToast);
            setTimeout(closeToast, 4000);
        }

        function validateForm(form) {
            const errors = [];
            
            const aspek = document.getElementById('indikator-aspek').value.trim();
            const nama = document.getElementById('indikator-nama').value.trim();
            
            if (!aspek) errors.push('Pilih Aspek terlebih dahulu');
            if (!nama || nama.length === 0) errors.push('Masukkan nama indikator');
            else if (nama.length < 3) errors.push('Nama indikator minimal 3 karakter');
            else if (nama.length > 500) errors.push('Nama indikator maksimal 500 karakter');
            
            return errors;
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
            
            if (modalId === 'indikatorCreateModal') {
                clearForm('indikatorForm');
            }
        }

        function clearForm(formId) {
            const form = document.getElementById(formId);
            if (form) form.reset();
            
            // Reset character counter
            updateCharCount('indikator-deskripsi', 'deskripsi-char-count');
            
            if (form) {
                form.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            }
        }

        async function submitData(url, method, formData) {
            try {
                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                const submitBtn = document.getElementById('indikatorForm')?.querySelector('button[type="submit"]');
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
                hideModal("indikatorCreateModal");
                showToast(data.message || 'Data berhasil disimpan', 'success');
                setTimeout(() => location.reload(), 1500);
                
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Terjadi kesalahan', 'error');
                const submitBtn = document.getElementById('indikatorForm')?.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Simpan';
                }
            }
        }

        async function openEditModal(id) {
            try {
                clearForm('indikatorForm');
                
                const response = await fetch('{{ route("admin.f01.indikator.show", ":id") }}'.replace(':id', id), {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                }
                
                const data = await response.json();
                if (!data || !data.data) {
                    throw new Error('Invalid response format');
                }
                
                const indikator = data.data;
                
                document.getElementById('indikator-aspek').value = indikator.aspek_id;
                document.getElementById('indikator-kode').value = indikator.kode;
                document.getElementById('indikator-nama').value = indikator.nama;
                document.getElementById('indikator-deskripsi').value = indikator.deskripsi || '';
                document.getElementById('indikator-bukti-dukung').value = indikator.bukti_dukung || '';
                document.getElementById('indikator-bobot').value = indikator.bobot || '';
                document.getElementById('indikator-urutan').value = indikator.urutan || '';
                document.getElementById('indikator-aktif').checked = indikator.aktif;
                
                // Update character counter
                updateCharCount('indikator-deskripsi', 'deskripsi-char-count');
                
                const label = document.getElementById('indikator-createModalLabel');
                if (label) label.textContent = 'Edit Indikator: ' + indikator.kode;
                
                const form = document.getElementById('indikatorForm');
                if (form) {
                    form.dataset.editId = id;
                    form.dataset.mode = 'edit';
                }
                
                showModal('indikatorCreateModal');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Gagal memuat data indikator', 'error');
            }
        }

        async function viewDetail(id) {
            try {
                const response = await fetch('{{ route("admin.f01.indikator.show", ":id") }}'.replace(':id', id), {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                }
                
                const data = await response.json();
                if (!data || !data.data) {
                    throw new Error('Invalid response format');
                }
                
                const ind = data.data;
                
                let html = '<div class="detail-group"><label class="detail-label">Kode</label><p class="detail-value">' + ind.kode + '</p></div>';
                html += '<div class="detail-group"><label class="detail-label">Nama Indikator</label><p class="detail-value">' + ind.nama + '</p></div>';
                if (ind.deskripsi) {
                    // DEBUG: Langsung tampil raw deskripsi tanpa formatting
                    html += '<div class="detail-group"><label class="detail-label">Deskripsi [RAW]</label><div class="detail-value" style="border: 5px solid RED; background: #ffeeee; padding: 15px; white-space: pre-wrap; font-family: monospace;">' + escapeHtml(ind.deskripsi) + '</div></div>';
                }
                if (ind.bukti_dukung) {
                    html += '<div class="detail-group"><label class="detail-label">Bukti Dukung</label><p class="detail-value">' + ind.bukti_dukung + '</p></div>';
                }
                html += '<div class="detail-group"><label class="detail-label">Aspek</label><p class="detail-value">' + (ind.aspek ? '[' + ind.aspek.kode + '] ' + ind.aspek.nama : '-') + '</p></div>';
                html += '<div class="detail-group"><label class="detail-label">Bobot</label><p class="detail-value">' + (ind.bobot || '-') + '</p></div>';
                html += '<div class="detail-group"><label class="detail-label">Urutan</label><p class="detail-value">#' + ind.urutan + '</p></div>';
                html += '<div class="detail-group"><label class="detail-label">Status</label><p class="detail-value"><span class="badge ' + (ind.aktif ? 'bg-success' : 'bg-danger') + '">' + (ind.aktif ? 'Aktif' : 'Nonaktif') + '</span></p></div>';

                const detailContent = document.getElementById('indikator-detailContent');
                if (detailContent) {
                    detailContent.innerHTML = html;
                    showModal('indikatorDetailModal');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Gagal memuat detail indikator', 'error');
            }
        }

        async function openDeleteModal(id) {
            try {
                const response = await fetch('{{ route("admin.f01.indikator.show", ":id") }}'.replace(':id', id), {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                }
                
                const data = await response.json();
                if (!data || !data.data) {
                    throw new Error('Invalid response format');
                }
                
                deleteData = { 
                    id: id, 
                    nama: data.data.nama,
                    route: '{{ route("admin.f01.indikator.destroy", ":id") }}'.replace(':id', id)
                };
                
                const deleteText = document.getElementById('indikator-deleteText');
                if (deleteText) {
                    deleteText.innerHTML = '<div class="mb-2">Anda yakin ingin menghapus:</div><div class="bg-light p-3 rounded border-start border-4 border-danger"><strong>"' + (data.data.nama.substring(0, 100)) + (data.data.nama.length > 100 ? '..."' : '"') + '</strong></div>';
                }
                
                showModal('indikatorDeleteModal');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Gagal memuat data indikator', 'error');
            }
        }

        function executeDelete() {
            if (!deleteData) return;
            
            const btn = document.getElementById('indikator-delete-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menghapus...';
            
            fetch(deleteData.route, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(r => r.json())
            .then(data => {
                hideModal('indikatorDeleteModal');
                showToast(data.message || 'Indikator berhasil dihapus', 'success');
                setTimeout(() => location.reload(), 500);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash me-2"></i> Hapus Selamanya';
                showToast(err?.message || 'Gagal menghapus indikator', 'error');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('indikatorForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const errors = validateForm(this);
                    if (errors.length > 0) {
                        showToast(errors.join('\n'), 'error');
                        return;
                    }
                    
                    const isEdit = this.dataset.editId;
                    const url = isEdit 
                        ? '{{ route("admin.f01.indikator.update", ":id") }}'.replace(':id', this.dataset.editId)
                        : '{{ route("admin.f01.indikator.store") }}';
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    submitData(url, method, new FormData(this));
                });
            }

            const sortableElement = document.getElementById('indikatorSortable');
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
                            
                            const response = await fetch('{{ route("admin.f01.indikator.reorder") }}', {
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
                            showToast('Urutan indikator berhasil diperbarui', 'success');
                        } catch (error) {
                            sortable.sort(sortable.toArray());
                            evt.item.style.opacity = originalOpacity;
                            showToast(error.message || 'Gagal mengubah urutan', 'error');
                        } finally {
                            sortable.option('disabled', false);
                        }
                    }
                });
            }

            document.querySelectorAll('.indikator-modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideModal(this.id);
                    }
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const activeModal = document.querySelector('.indikator-modal-overlay.active');
                    if (activeModal) {
                        hideModal(activeModal.id);
                    }
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-btn')) {
                    const id = e.target.closest('.delete-btn').dataset.id;
                    openDeleteModal(id);
                }
            });
        });

        function openCreateModal() {
            clearForm('indikatorForm');
            
            const label = document.getElementById('indikator-createModalLabel');
            if (label) label.textContent = 'Tambah Indikator Baru';
            
            const form = document.getElementById('indikatorForm');
            if (form) {
                form.removeAttribute('data-edit-id');
                form.dataset.mode = 'create';
            }
            
            showModal('indikatorCreateModal');
        }
    </script>
@endpush

<script>
{!! file_get_contents(public_path('js/multi-sort.js')) !!}
</script>

@endsection
