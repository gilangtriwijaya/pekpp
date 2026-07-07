@extends('layouts.app')
@section('title','F03 Aspek Kuesioner')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('components.crud-table.css', ['prefix' => 'f03aspek'])

<div class="f03aspek-container">
    @include('components.crud-table.header', [
        'prefix' => 'f03aspek',
        'title' => 'Manajemen Aspek F03 Kuesioner',
        'subtitle' => 'Kelola data aspek kuesioner untuk setiap periode',
        'buttonText' => 'Buat Aspek',
        'buttonAction' => 'openCreateModal()'
    ])

    @include('components.crud-table.stats', [
        'prefix' => 'f03aspek',
        'stats' => [
            ['label' => 'Total Aspek F03', 'value' => $aspeks->total()],
            ['label' => 'Aspek Aktif', 'value' => $aspeks->where('aktif', 1)->count()]
        ]
    ])

    @include('components.crud-table.table-card', [
        'prefix' => 'f03aspek',
        'tableTitle' => 'Daftar Aspek F03',
        'tableId' => 'f03aspekTable'
    ])

    @include('components.crud-table.search', [
        'prefix' => 'f03aspek',
        'searchInputId' => 'f03aspekSearch',
        'tableId' => 'f03aspekTable'
    ])
    
    <div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; justify-content: flex-end; background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form method="GET" action="{{ route('admin.f03.aspek.index') }}" style="display: flex; gap: 0.75rem; align-items: center;">
            <label for="filter_periode" style="font-size: 14px; font-weight: 500; color: #475569;">Filter Periode:</label>
            <select name="periode_id" id="filter_periode" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; min-width: 200px; outline: none; transition: border-color 0.2s;" onchange="this.form.submit()" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
                <option value="">Semua Periode</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ (isset($selectedPeriodeId) && $selectedPeriodeId == $p->id) ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    
    <table class="f03aspek-table" id="f03aspekTable">
        <thead>
            <tr>
                <th style="width: 150px;">Periode</th>
                <th style="width: 80px;">Kode</th>
                <th>Nama Aspek</th>
                <th style="width: 80px;">Bobot (%)</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 160px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aspeks as $a)
            <tr data-id="{{ $a->id }}">
                <td>
                    <small class="f03aspek-badge f03aspek-badge-info">{{ $a->periode->nama ?? '-' }} ({{ $a->periode->tahun ?? '-' }})</small>
                </td>
                <td>
                    <strong>{{ $a->kode }}</strong>
                </td>
                <td>
                    <strong>{{ $a->nama }}</strong>
                    @if($a->keterangan)
                        <br><small class="f03aspek-text-muted">{{ \Str::limit($a->keterangan, 60) }}</small>
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="f03aspek-badge f03aspek-badge-info">{{ number_format($a->bobot ?? 0, 2) }}%</span>
                </td>
                <td style="text-align: center;">
                    @if($a->aktif)
                        <span class="f03aspek-badge f03aspek-badge-success">Aktif</span>
                    @else
                        <span class="f03aspek-badge f03aspek-badge-secondary">Nonaktif</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <div class="f03aspek-action-buttons">
                        <button class="f03aspek-btn-icon" onclick="openEditModal(@js($a))" title="Edit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <button class="f03aspek-btn-icon btn-danger" onclick="confirmDelete(@js($a), '{{ $a->kode }} - {{ $a->nama }}')" title="Hapus">
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
                    <p class="f03aspek-text-muted">Belum ada data aspek F03</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('components.crud-table.pagination', [
        'prefix' => 'f03aspek',
        'items' => $aspeks
    ])
</div>

<!-- Toast Notification -->
<div class="f03aspek-toast" id="f03aspek-toast"></div>

@include('components.crud-table.js', [
    'prefix' => 'f03aspek',
    'route' => route('admin.f03.aspek.destroy', ':id')
])

<script>
    const locked = false;

    function openCreateModal() {
        const periodeEl = document.getElementById('create-periode');
        const namaEl = document.getElementById('create-nama');
        const bobotEl = document.getElementById('create-bobot');
        const keteranganEl = document.getElementById('create-keterangan');
        const aktifEl = document.getElementById('create-aktif');
        
        if (periodeEl) periodeEl.value = '';
        if (namaEl) namaEl.value = '';
        if (bobotEl) bobotEl.value = '0';
        if (keteranganEl) keteranganEl.value = '';
        if (aktifEl) aktifEl.checked = true;
        
        openModal('f03aspekCreateModal');
    }

    function openEditModal(data) {
        const idEl = document.getElementById('edit-id');
        const periodeEl = document.getElementById('edit-periode');
        const namaEl = document.getElementById('edit-nama');
        const bobotEl = document.getElementById('edit-bobot');
        const keteranganEl = document.getElementById('edit-keterangan');
        const aktifEl = document.getElementById('edit-aktif');
        
        if (idEl) idEl.value = data.id;
        if (periodeEl) periodeEl.value = data.periode_id;
        if (namaEl) namaEl.value = data.nama;
        if (bobotEl) bobotEl.value = data.bobot || 0;
        if (keteranganEl) keteranganEl.value = data.keterangan || '';
        if (aktifEl) aktifEl.checked = data.aktif ? true : false;
        
        openModal('f03aspekEditModal');
    }

    function submitCreateForm(event) {
        event.preventDefault();
        const formData = new FormData(document.getElementById('createForm'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route("admin.f03.aspek.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.message) {
                showToast(data.message, 'success');
                closeModal('f03aspekCreateModal');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(err => showToast('Gagal membuat aspek', 'error'));
    }

    function submitEditForm(event) {
        event.preventDefault();
        const formData = new FormData(document.getElementById('editForm'));
        const id = document.getElementById('edit-id').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route("admin.f03.aspek.update", ":id") }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.message) {
                showToast(data.message, 'success');
                closeModal('f03aspekEditModal');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(err => showToast('Gagal memperbarui aspek', 'error'));
    }

    function confirmDelete(data, title) {
        if (confirm(`Yakin hapus Aspek: ${title}?`)) {
            deleteItem(data.id);
        }
    }

    function deleteItem(id) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('{{ route("admin.f03.aspek.destroy", ":id") }}'.replace(':id', id), {
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
        .catch(err => showToast('Gagal menghapus aspek', 'error'));
    }

    function showToast(message, type = 'info') {
        const toast = document.getElementById('f03aspek-toast');
        toast.textContent = message;
        toast.className = `f03aspek-toast f03aspek-toast-${type}`;
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
    .f03aspek-container { max-width: 1400px; margin: 0 auto; padding: 24px 20px; }
    .f03aspek-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .f03aspek-badge-info { background-color: #DBEAFE; color: #1E40AF; }
    .f03aspek-badge-light { background-color: #F3F4F6; color: #374151; }
    .f03aspek-badge-warning { background-color: #FEF3C7; color: #92400E; }
    .f03aspek-badge-success { background-color: #DCFCE7; color: #166534; }
    .f03aspek-badge-secondary { background-color: #E5E7EB; color: #6B7280; }
    .f03aspek-text-muted { color: #6B7280; }
    .f03aspek-table { width: 100%; border-collapse: collapse; }
    .f03aspek-table thead tr { background-color: #F9FAFB; border-bottom: 2px solid #E5E7EB; }
    .f03aspek-table th { padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #374151; }
    .f03aspek-table td { padding: 16px; font-size: 14px; color: #374151; border-bottom: 1px solid #E5E7EB; }
    .f03aspek-action-buttons { display: flex; gap: 8px; justify-content: center; }
    .f03aspek-btn-icon { background: none; border: none; cursor: pointer; padding: 6px; color: #3B82F6; transition: color 0.2s; }
    .f03aspek-btn-icon:hover { color: #2563EB; }
    .f03aspek-btn-icon.btn-danger { color: #EF4444; }
    .f03aspek-btn-icon.btn-danger:hover { color: #DC2626; }
</style>

@push('modals')
@include('f03.aspek.modals.create', ['periodes' => $periodes])
@include('f03.aspek.modals.edit', ['periodes' => $periodes])
@include('f03.aspek.modals.delete')
@endpush

@endsection
