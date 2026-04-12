@extends('layouts.app')
@section('title','Periode')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('components.crud-table.css', ['prefix' => 'periode'])

<div class="periode-container">
    @include('components.crud-table.header', [
        'prefix' => 'periode',
        'title' => 'Manajemen Periode',
        'subtitle' => 'Kelola data periode penilaian',
        'buttonText' => 'Buat Periode',
        'buttonAction' => 'openCreateModal()'
    ])

    @include('components.crud-table.stats', [
        'prefix' => 'periode',
        'stats' => [
            ['label' => 'Total Periode', 'value' => $periodes->total()],
            ['label' => 'Periode Aktif', 'value' => $periodes->where('is_aktif', 1)->count()]
        ]
    ])

    @include('components.crud-table.table-card', [
        'prefix' => 'periode',
        'tableTitle' => 'Daftar Periode',
        'tableId' => 'periodeTable'
    ])

    @include('components.crud-table.search', [
        'prefix' => 'periode',
        'searchInputId' => 'periodeSearch',
        'tableId' => 'periodeTable'
    ])
    
    <table class="periode-table" id="periodeTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tahun</th>
                        <th>Nama</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Aktif</th>
                        <th>Target F03</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodes as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>{{ $p->tahun }}</td>
                        <td>{{ $p->nama }}</td>
                        <td>{{ $p->tanggal_mulai }}</td>
                        <td>{{ $p->tanggal_selesai }}</td>
                        <td>
                            @if(auth()->user()->role_sso === 'superadmin')
                                <button class="periode-badge {{ $p->is_aktif ? 'periode-badge-active' : 'periode-badge-inactive' }}" 
                                        onclick="togglePeriodeAktif({{ $p->id }}, this)"
                                        style="border: none; cursor: pointer; background: inherit; padding: 4px 8px; border-radius: 4px; transition: all 0.2s;"
                                        title="Klik untuk mengubah status (Superadmin)">
                                    <span class="periode-badge-dot"></span>
                                    {{ $p->is_aktif ? 'Ya' : 'Tidak' }}
                                </button>
                            @else
                                <span class="periode-badge {{ $p->is_aktif ? 'periode-badge-active' : 'periode-badge-inactive' }}">
                                    <span class="periode-badge-dot"></span>
                                    {{ $p->is_aktif ? 'Ya' : 'Tidak' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="periode-badge periode-badge-info" style="background-color: #E0E7FF; color: #4F46E5; border: 1px solid #C7D2FE;">
                                {{ $p->target_responden_f03 === 0 ? 'Unlimited' : $p->target_responden_f03 }}
                            </span>
                        </td>
                        <td>
                            <div class="periode-actions-cell">
                                <button class="periode-btn-icon" onclick="viewDetail(@js($p))" title="Lihat Detail">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                                <button class="periode-btn-icon" onclick="openEditModal(@js($p))" title="Edit">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <button class="periode-btn-icon btn-danger" onclick="confirmDelete(@js($p), '{{ $p->nama }} ({{ $p->tahun }})')" title="Hapus">
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
                        <td colspan="8" style="text-align: center; padding: 24px; color: #64748b;">Tidak ada data periode</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('components.crud-table.pagination', [
        'prefix' => 'periode',
        'data' => $periodes
    ])
</div>

<!-- Modals -->
@include('periode.modals.create')
@include('periode.modals.edit')
@include('periode.modals.detail')
@include('periode.modals.delete')

<!-- Toast Notification -->
<div class="periode-toast" id="periode-toast"></div>

@include('components.crud-table.js', [
    'prefix' => 'periode',
    'route' => route('admin.periode.destroy', ':id')
])

<script>
    // Periode-specific functions
    function openCreateModal() {
        document.getElementById('create-nama').value = '';
        document.getElementById('create-tahun').value = new Date().getFullYear();
        document.getElementById('create-mulai').value = '';
        document.getElementById('create-selesai').value = '';
        document.getElementById('create-target-f03').value = '0';
        document.getElementById('create-status-pengisian').value = 'open';
        document.getElementById('create-aktif').checked = false;
        openModal('periodeCreateModal');
    }

    function openEditModal(data) {
        document.getElementById('edit-id').value = data.id;
        document.getElementById('edit-nama').value = data.nama;
        document.getElementById('edit-tahun').value = data.tahun;
        document.getElementById('edit-mulai').value = data.tanggal_mulai;
        document.getElementById('edit-selesai').value = data.tanggal_selesai;
        document.getElementById('edit-target-f03').value = data.target_responden_f03 || '0';
        document.getElementById('edit-status-pengisian').value = data.status_pengisian || 'open';
        document.getElementById('edit-aktif').checked = !!data.is_aktif;
        openModal('periodeEditModal');
    }

    function viewDetail(data) {
        document.getElementById('periode-detail-id').textContent = data.id;
        document.getElementById('periode-detail-nama').textContent = data.nama;
        document.getElementById('periode-detail-tahun').textContent = data.tahun;
        document.getElementById('periode-detail-mulai').textContent = data.tanggal_mulai;
        document.getElementById('periode-detail-selesai').textContent = data.tanggal_selesai;
        document.getElementById('periode-detail-aktif').textContent = data.is_aktif ? 'Ya' : 'Tidak';
        
        // Display status pengisian
        const statusPengisian = data.status_pengisian || 'open';
        const statusLabels = {
            'open': '🟢 Open - Menerima Input',
            'locked': '🔒 Locked - Input Terkunci',
            'closed': '🗂️ Closed - Ditutup/Arsip'
        };
        document.getElementById('periode-detail-status-pengisian').textContent = statusLabels[statusPengisian] || statusPengisian;
        
        // Store current detail data for edit button
        window.currentDetailData = data;
        
        openModal('periodeDetailModal');
    }

    function submitCreateForm(e) {
        e.preventDefault();
        const formId = 'createForm';
        const modalId = 'periodeCreateModal';
        const action = '{{ route("admin.periode.store") }}';
        submitForm(formId, modalId, action, 'POST');
    }

    function submitEditForm(e) {
        e.preventDefault();
        const formId = 'editForm';
        const modalId = 'periodeEditModal';
        const editId = document.getElementById('edit-id').value;
        const action = '{{ route("admin.periode.update", ":id") }}'.replace(':id', editId);
        submitForm(formId, modalId, action, 'PUT');
    }

    // Toggle Periode Aktif (AJAX - Superadmin only)
    async function togglePeriodeAktif(periodeId, element) {
        try {
            const response = await fetch('{{ route("admin.periode.toggle-aktif", ":id") }}'.replace(':id', periodeId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Gagal mengubah status');
            }

            const result = await response.json();
            
            if (result.success) {
                // Update badge appearance
                const badge = element;
                const isActive = result.is_aktif;
                
                if (isActive) {
                    badge.classList.remove('periode-badge-inactive');
                    badge.classList.add('periode-badge-active');
                    badge.innerHTML = '<span class="periode-badge-dot"></span>Ya';
                } else {
                    badge.classList.remove('periode-badge-active');
                    badge.classList.add('periode-badge-inactive');
                    badge.innerHTML = '<span class="periode-badge-dot"></span>Tidak';
                }
                
                // Show success message
                showNotification(result.message, 'success');
                
                // Reload table to update all badges
                setTimeout(() => {
                    location.reload();
                }, 500);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message || 'Gagal mengubah status periode', 'error');
        }
    }
</script>

@endsection
