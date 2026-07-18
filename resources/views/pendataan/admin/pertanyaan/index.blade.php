@extends('layouts.app')
@section('title', 'Pertanyaan Pendataan')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="pd-pertanyaan-container">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.6rem; font-weight:700; color:#1F2937; margin:0;">❓ Pertanyaan Pendataan</h1>
            <p style="color:#6B7280; margin:4px 0 0;">Kelola daftar pertanyaan per aspek pendataan</p>
        </div>
        <button class="pd-btn pd-btn-primary" onclick="openCreateModal()">+ Tambah Pertanyaan</button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="pd-alert pd-alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="pd-alert pd-alert-error">✗ {{ session('error') }}</div>
    @endif

    {{-- Filter --}}
    <div class="pd-filter-bar">
        <form method="GET" action="{{ url('/admin/pendataan/pertanyaan') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <div style="display:flex; gap:8px; align-items:center;">
                <label style="font-size:14px; font-weight:500; color:#475569;">Periode:</label>
                <select name="periode_id" id="filter_periode" onchange="updateAspekFilter(this.value, null)" class="pd-form-control" style="min-width:200px;">
                    <option value="">Semua</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ $periode_id == $p->id ? 'selected' : '' }}>
                            {{ $p->nama ?? $p->tahun }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <label style="font-size:14px; font-weight:500; color:#475569;">Aspek:</label>
                <select name="aspek_id" id="filter_aspek" onchange="this.form.submit()" class="pd-form-control" style="min-width:220px;">
                    <option value="">Semua Aspek</option>
                    @foreach($aspeks as $a)
                        <option value="{{ $a->id }}" {{ $aspek_id == $a->id ? 'selected' : '' }}>
                            {{ $a->urutan }}. {{ $a->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="pd-btn pd-btn-secondary">Filter</button>
        </form>
    </div>

    {{-- Stats --}}
    <div class="pd-stats-bar">
        <div class="pd-stat-card">
            <div class="pd-stat-val">{{ $pertanyaan->count() }}</div>
            <div class="pd-stat-lbl">Total Pertanyaan</div>
        </div>
        <div class="pd-stat-card">
            <div class="pd-stat-val">{{ $pertanyaan->where('aktif', 1)->count() }}</div>
            <div class="pd-stat-lbl">Pertanyaan Aktif</div>
        </div>
        <div class="pd-stat-card">
            <div class="pd-stat-val">{{ $pertanyaan->where('wajib', 1)->count() }}</div>
            <div class="pd-stat-lbl">Wajib Diisi</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="pd-table-card">
        <table class="pd-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th style="width:70px;">Urutan</th>
                    <th>Pertanyaan</th>
                    <th style="width:150px;">Aspek</th>
                    <th style="width:100px;">Tipe Input</th>
                    <th style="width:80px;">Wajib</th>
                    <th style="width:80px;">Status</th>
                    <th style="width:130px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pertanyaan as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align:center;">{{ $p->urutan }}</td>
                    <td>
                        <div style="font-weight:500;">{{ $p->label }}</div>
                        @if($p->kode)
                            <small style="color:#9CA3AF;">{{ $p->kode }}</small>
                        @endif
                        @if($p->opsi_jawaban)
                            @php
                                $opsi = is_string($p->opsi_jawaban) ? json_decode($p->opsi_jawaban, true) : $p->opsi_jawaban;
                            @endphp
                            @if($opsi && count($opsi) > 0)
                                <div style="margin-top:6px; display:flex; gap:6px; flex-wrap:wrap;">
                                    @foreach(array_slice($opsi, 0, 4) as $o)
                                        <span class="pd-badge pd-badge-light">{{ $o['label'] ?? $o['value'] ?? $o }}</span>
                                    @endforeach
                                    @if(count($opsi) > 4)
                                        <span style="color:#9CA3AF; font-size:12px;">+{{ count($opsi) - 4 }} lainnya</span>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </td>
                    <td>
                        <span class="pd-badge pd-badge-info">{{ $p->aspek->nama ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="pd-badge pd-badge-warning">{{ strtoupper($p->tipe_input) }}</span>
                    </td>
                    <td style="text-align:center;">
                        @if($p->wajib)
                            <span class="pd-badge pd-badge-danger">Wajib</span>
                        @else
                            <span class="pd-badge pd-badge-secondary">Opsional</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($p->aktif)
                            <span class="pd-badge pd-badge-success">Aktif</span>
                        @else
                            <span class="pd-badge pd-badge-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <button class="pd-btn-icon" onclick="openEditModal(@js($p))" title="Edit">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="pd-btn-icon btn-danger" onclick="confirmDelete({{ $p->id }}, '{{ addslashes(Str::limit($p->label, 40)) }}')" title="Hapus">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:#9CA3AF;">
                        Belum ada pertanyaan. Klik "+ Tambah Pertanyaan" untuk menambahkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- Toast --}}
<div class="pd-toast" id="pd-toast"></div>

{{-- Modals --}}
@include('pendataan.admin.pertanyaan.modals.create', ['aspeks' => $aspeks])
@include('pendataan.admin.pertanyaan.modals.edit', ['aspeks' => $aspeks])

<style>
    .pd-pertanyaan-container { max-width:1400px; margin:0 auto; padding:30px 24px; }
    .pd-btn { padding:10px 20px; border:none; border-radius:7px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; }
    .pd-btn-primary { background:linear-gradient(135deg,#4F46E5,#7C3AED); color:white; }
    .pd-btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(79,70,229,0.3); }
    .pd-btn-secondary { background:#E5E7EB; color:#374151; }
    .pd-btn-secondary:hover { background:#D1D5DB; }
    .pd-alert { padding:14px 18px; border-radius:8px; margin-bottom:16px; font-size:14px; }
    .pd-alert-success { background:#D1FAE5; color:#065F46; border-left:4px solid #10B981; }
    .pd-alert-error { background:#FEE2E2; color:#991B1B; border-left:4px solid #EF4444; }
    .pd-filter-bar { background:white; padding:16px; border-radius:10px; border:1px solid #E5E7EB; margin-bottom:20px; }
    .pd-stats-bar { display:flex; gap:16px; margin-bottom:20px; }
    .pd-stat-card { background:white; border:1px solid #E5E7EB; border-radius:10px; padding:18px 28px; flex:1; }
    .pd-stat-val { font-size:1.8rem; font-weight:700; color:#4F46E5; }
    .pd-stat-lbl { color:#6B7280; font-size:13px; margin-top:2px; }
    .pd-table-card { background:white; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden; }
    .pd-table { width:100%; border-collapse:collapse; }
    .pd-table thead tr { background:#F9FAFB; border-bottom:2px solid #E5E7EB; }
    .pd-table th { padding:14px 16px; text-align:left; font-size:13px; font-weight:600; color:#374151; }
    .pd-table td { padding:14px 16px; font-size:14px; color:#374151; border-bottom:1px solid #F3F4F6; vertical-align:top; }
    .pd-badge { display:inline-block; padding:3px 9px; border-radius:20px; font-size:12px; font-weight:600; }
    .pd-badge-info { background:#DBEAFE; color:#1E40AF; }
    .pd-badge-success { background:#DCFCE7; color:#166534; }
    .pd-badge-warning { background:#FEF3C7; color:#92400E; }
    .pd-badge-danger { background:#FEE2E2; color:#991B1B; }
    .pd-badge-secondary { background:#E5E7EB; color:#6B7280; }
    .pd-badge-light { background:#F3F4F6; color:#374151; }
    .pd-btn-icon { background:none; border:none; cursor:pointer; padding:6px; color:#3B82F6; border-radius:5px; transition:all 0.2s; }
    .pd-btn-icon:hover { background:#EFF6FF; }
    .pd-btn-icon.btn-danger { color:#EF4444; }
    .pd-btn-icon.btn-danger:hover { background:#FEF2F2; }
    .pd-toast { position:fixed; top:20px; right:20px; padding:14px 20px; border-radius:8px; color:white; font-size:14px; z-index:100000; display:none; }
    .pd-toast.success { background:#10B981; }
    .pd-toast.error { background:#EF4444; }
    .pd-form-control { padding:8px 12px; border:1px solid #D1D5DB; border-radius:6px; font-size:14px; font-family:inherit; }
    .pd-form-control:focus { outline:none; border-color:#4F46E5; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
</style>

<script>
const STORE_ROUTE = '{{ route("admin.pendataan.pertanyaan.store") }}';
const UPDATE_ROUTE = '{{ route("admin.pendataan.pertanyaan.update", ":id") }}';
const DELETE_ROUTE = '{{ route("admin.pendataan.pertanyaan.destroy", ":id") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// All aspeks data for JS population
const allAspeks = {{ Js::from($aspeks->map(function($a) { return ['id' => $a->id, 'nama' => $a->nama, 'urutan' => $a->urutan, 'periode_id' => $a->periode_id]; })->values()) }};

function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('show'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('show'); document.body.style.overflow = ''; }
}
function showToast(msg, type='success') {
    const t = document.getElementById('pd-toast');
    t.textContent = msg; t.className = `pd-toast ${type}`; t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 3000);
}

function updateAspekFilter(periodeId, form) {
    if (form) form.submit();
}

// ====== OPSI JAWABAN - baris per baris ======

function addOpsiRow(prefix, value) {
    value = value || '';
    const list = document.getElementById(prefix + '-opsi-list');
    const idx = list.children.length + 1;
    const row = document.createElement('div');
    row.className = 'opsi-row';
    const safeVal = value.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    row.innerHTML =
        '<span class="opsi-row-num">' + idx + '</span>' +
        '<input type="text" class="opsi-input" placeholder="Pilihan ' + idx + '" value="' + safeVal + '">' +
        '<button type="button" class="opsi-delete-btn" onclick="removeOpsiRow(this, \'' + prefix + '\')" title="Hapus">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
        '</button>';
    list.appendChild(row);
    renumberOpsiRows(prefix);
}

function removeOpsiRow(btn, prefix) {
    btn.closest('.opsi-row').remove();
    renumberOpsiRows(prefix);
}

function renumberOpsiRows(prefix) {
    const list = document.getElementById(prefix + '-opsi-list');
    list.querySelectorAll('.opsi-row').forEach(function(row, i) {
        row.querySelector('.opsi-row-num').textContent = i + 1;
        row.querySelector('.opsi-input').placeholder = 'Pilihan ' + (i + 1);
    });
}

function collectOpsi(prefix) {
    const list = document.getElementById(prefix + '-opsi-list');
    const rows = list.querySelectorAll('.opsi-input');
    const result = [];
    rows.forEach(function(input, i) {
        const val = input.value.trim();
        if (val) {
            const letter = String.fromCharCode(97 + i);
            result.push({ value: letter, label: val });
        }
    });
    return result.length > 0 ? JSON.stringify(result) : '';
}

function populateOpsiRows(prefix, jsonStr) {
    const list = document.getElementById(prefix + '-opsi-list');
    list.innerHTML = '';
    if (!jsonStr) return;
    let opsi = [];
    try { opsi = JSON.parse(jsonStr); } catch(e) { return; }
    opsi.forEach(function(item) {
        addOpsiRow(prefix, item.label || item.value || '');
    });
}

// ====== TOGGLE OPSI GROUP ======

function toggleOpsi(prefix) {
    const tipe = document.getElementById(prefix + '-tipe').value;
    const opsiGroup = document.getElementById(prefix + '-opsi-group');
    if (tipe === 'radio' || tipe === 'checkbox' || tipe === 'select') {
        opsiGroup.style.display = 'block';
    } else {
        opsiGroup.style.display = 'none';
    }
}

// ====== OPEN MODALS ======

function openCreateModal() {
    document.getElementById('create-aspek').value = '';
    document.getElementById('create-label').value = '';
    document.getElementById('create-kode').value = '';
    document.getElementById('create-tipe').value = 'text';
    document.getElementById('create-urutan').value = '0';
    document.getElementById('create-wajib').checked = false;
    document.getElementById('create-aktif').checked = true;
    document.getElementById('create-opsi-list').innerHTML = '';
    toggleOpsi('create');
    openModal('pdPertanyaanCreateModal');
}

function openEditModal(data) {
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-aspek').value = data.pendataan_aspek_id;
    document.getElementById('edit-label').value = data.label;
    document.getElementById('edit-kode').value = data.kode || '';
    document.getElementById('edit-tipe').value = data.tipe_input || 'text';
    document.getElementById('edit-urutan').value = data.urutan || '';
    document.getElementById('edit-wajib').checked = !!data.wajib;
    document.getElementById('edit-aktif').checked = !!data.aktif;
    populateOpsiRows('edit', data.opsi_jawaban || '');
    toggleOpsi('edit');
    openModal('pdPertanyaanEditModal');
}

// ====== SUBMIT ======

function submitCreateForm(event) {
    event.preventDefault();
    document.getElementById('create-opsi-hidden').value = collectOpsi('create');
    const formData = new FormData(document.getElementById('createPForm'));
    fetch(STORE_ROUTE, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: formData })
        .then(function(r) { return r.json(); })
        .then(function(d) { showToast(d.message || 'Berhasil disimpan'); closeModal('pdPertanyaanCreateModal'); setTimeout(function() { location.reload(); }, 900); })
        .catch(function() { showToast('Gagal menyimpan pertanyaan', 'error'); });
}

function submitEditForm(event) {
    event.preventDefault();
    document.getElementById('edit-opsi-hidden').value = collectOpsi('edit');
    const id = document.getElementById('edit-id').value;
    const formData = new FormData(document.getElementById('editPForm'));
    fetch(UPDATE_ROUTE.replace(':id', id), { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT' }, body: formData })
        .then(function(r) { return r.json(); })
        .then(function(d) { showToast(d.message || 'Berhasil diperbarui'); closeModal('pdPertanyaanEditModal'); setTimeout(function() { location.reload(); }, 900); })
        .catch(function() { showToast('Gagal memperbarui pertanyaan', 'error'); });
}

function confirmDelete(id, label) {
    if (!confirm('Yakin hapus pertanyaan:\n"' + label + '"?')) return;
    fetch(DELETE_ROUTE.replace(':id', id), { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-HTTP-Method-Override': 'DELETE' } })
        .then(function(r) { return r.json(); })
        .then(function(d) { showToast(d.message || 'Berhasil dihapus'); setTimeout(function() { location.reload(); }, 900); })
        .catch(function() { showToast('Gagal menghapus pertanyaan', 'error'); });
}
</script>



@endsection
