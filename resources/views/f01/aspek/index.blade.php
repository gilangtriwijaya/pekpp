@extends('layouts.app')
@section('title','Aspek')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('components.crud-table.css', ['prefix' => 'aspek'])

<div class="aspek-container">
    @include('components.crud-table.header', [
        'prefix' => 'aspek',
        'title' => 'Manajemen Aspek Penilaian',
        'subtitle' => 'Kelola data aspek untuk setiap periode',
        'buttonText' => 'Buat Aspek',
        'buttonAction' => 'openCreateModal()'
    ])

    @if(!empty($locked) && $locked)
        <div class="aspek-alert aspek-alert-warning">
            <strong>⚠️ Perubahan dikunci</strong><br>
            Perubahan struktur dikunci karena sudah ada pengisian F01 berstatus final. Aksi create/update/delete/reorder dinonaktifkan.
        </div>
    @endif

    @include('components.crud-table.stats', [
        'prefix' => 'aspek',
        'stats' => [
            ['label' => 'Total Aspek', 'value' => $aspeks->total()],
            ['label' => 'Aspek Aktif', 'value' => $aspeks->where('aktif', 1)->count()]
        ]
    ])

    @include('components.crud-table.table-card', [
        'prefix' => 'aspek',
        'tableTitle' => 'Daftar Aspek',
        'tableId' => 'aspekTable'
    ])

    @include('components.crud-table.search', [
        'prefix' => 'aspek',
        'searchInputId' => 'aspekSearch',
        'tableId' => 'aspekTable'
    ])
    
    <div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; justify-content: flex-end; background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form method="GET" action="{{ route('admin.f01.aspek.index') }}" style="display: flex; gap: 0.75rem; align-items: center;">
            <label for="filter_periode" style="font-size: 14px; font-weight: 500; color: #475569;">Filter Periode:</label>
            <select name="periode_id" id="filter_periode" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px; min-width: 200px; outline: none; transition: border-color 0.2s;" onchange="this.form.submit()" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#cbd5e1'">
                <option value="">Semua Periode</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <table class="aspek-table" id="aspekTable">
        <thead>
            <tr>
                <th style="width: 150px;">Periode</th>
                <th style="width: 80px;">Kode</th>
                <th>Nama Aspek</th>
                <th style="width: 100px;">Domain</th>
                <th style="width: 80px;">Bobot</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 160px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aspeks as $a)
            <tr data-id="{{ $a->id }}">
                <td>
                    <small class="aspek-badge aspek-badge-info">{{ $a->periode->nama ?? '-' }} ({{ $a->periode->tahun ?? '-' }})</small>
                </td>
                <td>
                    <strong>{{ $a->kode }}</strong>
                </td>
                <td>
                    <strong>{{ $a->nama }}</strong>
                    @if($a->keterangan)
                        <br><small class="aspek-text-muted">{{ \Str::limit($a->keterangan, 60) }}</small>
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="aspek-badge aspek-badge-light">{{ ucfirst($a->domain) }}</span>
                </td>
                <td style="text-align: center;">
                    <span class="aspek-badge aspek-badge-info">{{ number_format($a->bobot ?? 0, 2) }}%</span>
                </td>
                <td style="text-align: center;">
                    @if($a->aktif)
                        <span class="aspek-badge aspek-badge-success">Aktif</span>
                    @else
                        <span class="aspek-badge aspek-badge-secondary">Nonaktif</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <div class="aspek-action-buttons">
                        <button class="aspek-btn-icon" onclick="viewDetail(@js($a))" title="Detail">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                        <button class="aspek-btn-icon" onclick="openEditModal(@js($a))" title="Edit" {{ $locked ? 'disabled' : '' }}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <button class="aspek-btn-icon btn-danger" onclick="confirmDelete(@js($a), '{{ $a->kode }} - {{ $a->nama }}')" title="Hapus" {{ $locked ? 'disabled' : '' }}>
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
                <td colspan="7" style="text-align: center; padding: 40px;">
                    <p class="aspek-text-muted">Belum ada data aspek</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('components.crud-table.pagination', [
        'prefix' => 'aspek',
        'items' => $aspeks
    ])
</div>

<!-- Modals -->
@include('f01.aspek.modals.create', ['periodes' => $periodes])
@include('f01.aspek.modals.edit', ['periodes' => $periodes])
@include('f01.aspek.modals.detail')
@include('f01.aspek.modals.delete')

<!-- Toast Notification -->
<div class="aspek-toast" id="aspek-toast"></div>

@include('components.crud-table.js', [
    'prefix' => 'aspek',
    'route' => route('admin.f01.aspek.destroy', ':id')
])

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    // Aspek-specific functions
    const locked = {{ !empty($locked) && $locked ? 'true' : 'false' }};

    function openCreateModal() {
        document.getElementById('create-periode').value = '';
        document.getElementById('create-kode').value = '';
        document.getElementById('create-nama').value = '';
        document.getElementById('create-domain').value = '';
        document.getElementById('create-bobot').value = '';
        document.getElementById('create-keterangan').value = '';
        document.getElementById('create-aktif').checked = true;
        openModal('aspekCreateModal');
    }

    function openEditModal(data) {
        document.getElementById('edit-id').value = data.id;
        document.getElementById('edit-periode').value = data.periode_id;
        document.getElementById('edit-kode').value = data.kode;
        document.getElementById('edit-nama').value = data.nama;
        document.getElementById('edit-domain').value = data.domain;
        document.getElementById('edit-bobot').value = data.bobot || '';
        document.getElementById('edit-keterangan').value = data.keterangan || '';
        document.getElementById('edit-aktif').checked = data.aktif ? true : false;
        openModal('aspekEditModal');
    }

    function viewDetail(data) {
        document.getElementById('aspek-detail-id').textContent = data.id;
        document.getElementById('aspek-detail-periode').textContent = data.periode && data.periode.nama ? data.periode.nama + ' (' + data.periode.tahun + ')' : '-';
        document.getElementById('aspek-detail-kode').textContent = data.kode;
        document.getElementById('aspek-detail-nama').textContent = data.nama;
        document.getElementById('aspek-detail-domain').textContent = data.domain ? data.domain.charAt(0).toUpperCase() + data.domain.slice(1) : '-';
        document.getElementById('aspek-detail-bobot').textContent = data.bobot ? data.bobot.toFixed(2) + '%' : '-';
        document.getElementById('aspek-detail-keterangan').textContent = data.keterangan || '-';
        document.getElementById('aspek-detail-urutan').textContent = data.urutan;
        document.getElementById('aspek-detail-aktif').textContent = data.aktif ? 'Aktif' : 'Nonaktif';
        openModal('aspekDetailModal');
    }

    function submitCreateForm(e) {
        e.preventDefault();
        const formId = 'createForm';
        const modalId = 'aspekCreateModal';
        const action = '{{ route("admin.f01.aspek.store") }}';
        submitForm(formId, modalId, action, 'POST');
    }

    function submitEditForm(e) {
        e.preventDefault();
        const formId = 'editForm';
        const modalId = 'aspekEditModal';
        const editId = document.getElementById('edit-id').value;
        const action = '{{ route("admin.f01.aspek.update", ":id") }}'.replace(':id', editId);
        submitForm(formId, modalId, action, 'PUT');
    }

    // Initialize drag & drop for reordering
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.querySelector('#aspekTable tbody');
        if (!locked && tbody) {
            new Sortable(tbody, {
                handle: '.aspek-drag-handle',
                animation: 150,
                onEnd: function () {
                    const order = Array.from(tbody.querySelectorAll('tr')).map(tr => tr.getAttribute('data-id'));
                    fetch('{{ route("admin.f01.aspek.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (!j.success) {
                            showToast(j.error || 'Gagal menyimpan urutan', 'error');
                        } else {
                            showToast('Urutan aspek berhasil diperbarui', 'success');
                        }
                    })
                    .catch(e => {
                        showToast('Gagal menyimpan urutan', 'error');
                    });
                }
            });
        } else {
            // Disable drag handles when locked
            document.querySelectorAll('.aspek-drag-handle').forEach(d => {
                d.style.opacity = '0.3';
                d.style.cursor = 'not-allowed';
            });
        }
    });
</script>

<script>
{!! file_get_contents(public_path('js/multi-sort.js')) !!}
</script>

@endsection
