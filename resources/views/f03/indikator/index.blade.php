@extends('layouts.app')
@section('title','F03 Indikator Kuesioner')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('components.crud-table.css', ['prefix' => 'f03indikator'])

<div class="f03indikator-container">
    @include('components.crud-table.header', [
        'prefix' => 'f03indikator',
        'title' => 'Manajemen Indikator F03 Kuesioner',
        'subtitle' => 'Kelola pertanyaan dan tipe jawaban untuk setiap aspek',
        'buttonText' => 'Buat Indikator',
        'buttonAction' => 'openCreateModal()'
    ])

    @include('components.crud-table.stats', [
        'prefix' => 'f03indikator',
        'stats' => [
            ['label' => 'Total Indikator F03', 'value' => $indikators->total()],
            ['label' => 'Indikator Aktif', 'value' => $indikators->where('aktif', 1)->count()]
        ]
    ])

    @include('components.crud-table.table-card', [
        'prefix' => 'f03indikator',
        'tableTitle' => 'Daftar Indikator F03',
        'tableId' => 'f03indikatorTable'
    ])

    @include('components.crud-table.search', [
        'prefix' => 'f03indikator',
        'searchInputId' => 'f03indikatorSearch',
        'tableId' => 'f03indikatorTable'
    ])
    
    <table class="f03indikator-table" id="f03indikatorTable">
        <thead>
            <tr>
                <th style="width: 70px; text-align: center;">Urutan</th>
                <th style="width: 150px;">Aspek</th>
                <th>Pertanyaan</th>
                <th style="width: 120px;">Tipe Jawaban</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 160px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($indikators as $i)
            <tr data-id="{{ $i->id }}">
                <td style="text-align: center;">
                    <span class="f03indikator-badge f03indikator-badge-light" style="background-color: #FEF3C7; color: #92400E; font-size: 16px; font-weight: 600;">{{ $i->urutan ?? '-' }}</span>
                </td>
                <td>
                    <small class="f03indikator-badge f03indikator-badge-info">{{ $i->aspek->kode }} - {{ $i->aspek->nama ?? '-' }}</small>
                </td>
                <td>
                    <strong>{{ \Str::limit($i->pertanyaan, 80) }}</strong>
                </td>
                <td style="text-align: center;">
                    <span class="f03indikator-badge f03indikator-badge-light">{{ ucfirst($i->tipe_jawaban) }}</span>
                </td>
                <td style="text-align: center;">
                    @if($i->aktif)
                        <span class="f03indikator-badge f03indikator-badge-success">Aktif</span>
                    @else
                        <span class="f03indikator-badge f03indikator-badge-secondary">Nonaktif</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <div class="f03indikator-action-buttons">
                        <button class="f03indikator-btn-icon" onclick="openEditModal(@js($i))" title="Edit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <button class="f03indikator-btn-icon btn-danger" onclick="confirmDelete(@js($i), '{{ $i->kode }} - ' + '{{ \Str::limit($i->pertanyaan, 30) }}')" title="Hapus">
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
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <p class="f03indikator-text-muted">Belum ada data indikator F03</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('components.crud-table.pagination', [
        'prefix' => 'f03indikator',
        'items' => $indikators
    ])
</div>

<!-- Toast Notification -->
<div class="f03indikator-toast" id="f03indikator-toast"></div>

@include('components.crud-table.js', [
    'prefix' => 'f03indikator',
    'route' => route('admin.f03.indikator.destroy', ':id')
])

<script>
    const locked = false;

    function openCreateModal() {
        const aspekEl = document.getElementById('create-aspek_id');
        const periodeEl = document.getElementById('create-periode_id');
        const pertanyaanEl = document.getElementById('create-pertanyaan');
        const tipeEl = document.getElementById('create-tipe_jawaban');
        const pilihanEl = document.getElementById('create-pilihan_jawaban');
        const urutanEl = document.getElementById('create-urutan');
        const aktifEl = document.getElementById('create-aktif');
        
        if (aspekEl) aspekEl.value = '';
        if (periodeEl) periodeEl.value = '';
        if (pertanyaanEl) pertanyaanEl.value = '';
        if (tipeEl) tipeEl.value = 'radio';
        if (pilihanEl) pilihanEl.value = JSON.stringify(['Ya', 'Tidak', 'Kurang Jelas']);
        if (urutanEl) urutanEl.value = '';
        if (aktifEl) aktifEl.checked = true;
        
        openModal('f03indikatorCreateModal');
    }

    function updatePilihanJawaban(modalType) {
        const prefix = modalType === 'create' ? 'create' : 'edit';
        const tipeSelect = document.getElementById(`${prefix}-tipe_jawaban`);
        const pilihanInput = document.getElementById(`${prefix}-pilihan_jawaban`);
        
        if (!tipeSelect || !pilihanInput) {
            console.error('Element not found for pilihan jawaban update');
            return;
        }
        
        try {
            const tipe = tipeSelect.value;
            let pilihanDefault = '';
            
            if (tipe === 'likert_5') {
                pilihanDefault = JSON.stringify([
                    'Sangat Tidak Setuju',
                    'Tidak Setuju',
                    'Netral',
                    'Setuju',
                    'Sangat Setuju'
                ]);
            }
            
            if (pilihanDefault) {
                pilihanInput.value = pilihanDefault;
            }
        } catch (error) {
            console.error('Error in updatePilihanJawaban:', error);
        }
    }

    function updatePeriodeId(modalType) {
        const prefix = modalType === 'create' ? 'create' : 'edit';
        const aspekSelect = document.getElementById(`${prefix}-aspek_id`);
        const periodeInput = document.getElementById(`${prefix}-periode_id`);
        
        if (aspekSelect && periodeInput) {
            const selectedOption = aspekSelect.options[aspekSelect.selectedIndex];
            const periodeId = selectedOption.getAttribute('data-periode-id');
            periodeInput.value = periodeId || '';
        }
    }

    function openEditModal(data) {
        const idEl = document.getElementById('edit-id');
        const aspekEl = document.getElementById('edit-aspek_id');
        const periodeEl = document.getElementById('edit-periode_id');
        const pertanyaanEl = document.getElementById('edit-pertanyaan');
        const tipeEl = document.getElementById('edit-tipe_jawaban');
        const pilihanEl = document.getElementById('edit-pilihan_jawaban');
        const urutanEl = document.getElementById('edit-urutan');
        const aktifEl = document.getElementById('edit-aktif');
        
        if (idEl) idEl.value = data.id;
        if (aspekEl) aspekEl.value = data.f03_aspek_id;
        if (periodeEl) periodeEl.value = data.periode_id;
        if (pertanyaanEl) pertanyaanEl.value = data.pertanyaan;
        if (tipeEl) tipeEl.value = data.tipe_jawaban;
        if (pilihanEl) pilihanEl.value = JSON.stringify(data.pilihan_jawaban || []);
        if (urutanEl) urutanEl.value = data.urutan || '';
        if (aktifEl) aktifEl.checked = data.aktif ? true : false;
        
        openModal('f03indikatorEditModal');
    }

    function submitCreateForm(event) {
        event.preventDefault();
        const formEl = document.getElementById('createForm');
        const formData = new FormData(formEl);
        
        // Validate pilihan_jawaban JSON if provided
        const pilihanValue = formData.get('pilihan_jawaban');
        if (pilihanValue && pilihanValue.trim()) {
            try {
                JSON.parse(pilihanValue);
            } catch (e) {
                showToast('Format JSON Pilihan Jawaban tidak valid', 'error');
                return;
            }
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route("admin.f03.indikator.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json().then(data => ({ status: res.status, data: data })))
        .then(({ status, data }) => {
            if (status === 200 || status === 201) {
                showToast(data.message || 'Indikator F03 berhasil dibuat', 'success');
                closeModal('f03indikatorCreateModal');
                setTimeout(() => location.reload(), 1000);
            } else if (data.errors) {
                const errorMsg = Object.values(data.errors).flat().join(', ');
                showToast(errorMsg || 'Validasi gagal', 'error');
                console.error('Validation errors:', data.errors);
            } else {
                showToast(data.message || 'Gagal membuat indikator', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Gagal membuat indikator: ' + err.message, 'error');
        });
    }

    function submitEditForm(event) {
        event.preventDefault();
        const formEl = document.getElementById('editForm');
        const formData = new FormData(formEl);
        const id = document.getElementById('edit-id').value;
        
        // Validate pilihan_jawaban JSON if provided
        const pilihanValue = formData.get('pilihan_jawaban');
        if (pilihanValue && pilihanValue.trim()) {
            try {
                JSON.parse(pilihanValue);
            } catch (e) {
                showToast('Format JSON Pilihan Jawaban tidak valid', 'error');
                return;
            }
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route("admin.f03.indikator.update", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(res => res.json().then(data => ({ status: res.status, data: data })))
        .then(({ status, data }) => {
            if (status === 200) {
                showToast(data.message || 'Indikator F03 berhasil diperbarui', 'success');
                closeModal('f03indikatorEditModal');
                setTimeout(() => location.reload(), 1000);
            } else if (data.errors) {
                const errorMsg = Object.values(data.errors).flat().join(', ');
                showToast(errorMsg || 'Validasi gagal', 'error');
                console.error('Validation errors:', data.errors);
            } else {
                showToast(data.message || 'Gagal memperbarui indikator', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Gagal memperbarui indikator: ' + err.message, 'error');
        });
    }

    function confirmDelete(data, title) {
        if (confirm(`Yakin hapus Indikator: ${title}?`)) {
            deleteItem(data.id);
        }
    }

    function deleteItem(id) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('{{ route("admin.f03.indikator.destroy", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-HTTP-Method-Override': 'DELETE'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.message) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Gagal menghapus indikator', 'error');
        });
    }

    function showToast(message, type = 'info') {
        const toast = document.getElementById('f03indikator-toast');
        toast.textContent = message;
        toast.className = `f03indikator-toast f03indikator-toast-${type}`;
        toast.style.display = 'block';
        setTimeout(() => toast.style.display = 'none', 3000);
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
</script>

<style>
    .f03indikator-container { max-width: 1400px; margin: 0 auto; padding: 24px 20px; }
    .f03indikator-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .f03indikator-badge-info { background-color: #DBEAFE; color: #1E40AF; }
    .f03indikator-badge-light { background-color: #F3F4F6; color: #374151; }
    .f03indikator-badge-success { background-color: #DCFCE7; color: #166534; }
    .f03indikator-badge-secondary { background-color: #E5E7EB; color: #6B7280; }
    .f03indikator-text-muted { color: #6B7280; }
    .f03indikator-table { width: 100%; border-collapse: collapse; }
    .f03indikator-table thead tr { background-color: #F9FAFB; border-bottom: 2px solid #E5E7EB; }
    .f03indikator-table th { padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #374151; }
    .f03indikator-table td { padding: 16px; font-size: 14px; color: #374151; border-bottom: 1px solid #E5E7EB; }
    .f03indikator-action-buttons { display: flex; gap: 8px; justify-content: center; }
    .f03indikator-btn-icon { background: none; border: none; cursor: pointer; padding: 6px; color: #3B82F6; transition: color 0.2s; }
    .f03indikator-btn-icon:hover { color: #2563EB; }
    .f03indikator-btn-icon.btn-danger { color: #EF4444; }
    .f03indikator-btn-icon.btn-danger:hover { color: #DC2626; }
</style>

@push('modals')
@include('f03.indikator.modals.create', ['aspeks' => $aspeks])
@include('f03.indikator.modals.edit', ['aspeks' => $aspeks])
@include('f03.indikator.modals.delete')
@endpush

@endsection
