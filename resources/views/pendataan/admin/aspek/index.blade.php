@extends('layouts.app')
@section('title', 'Aspek Pendataan')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="pd-aspek-container">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.6rem; font-weight:700; color:#1F2937; margin:0;">📋 Manajemen Aspek Pendataan</h1>
            <p style="color:#6B7280; margin:4px 0 0;">Kelola aspek kuesioner Pendataan per periode</p>
        </div>
        <button class="pd-btn pd-btn-primary" onclick="openCreateModal()">+ Tambah Aspek</button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="pd-alert pd-alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="pd-alert pd-alert-error">✗ {{ session('error') }}</div>
    @endif

    {{-- Filter Periode --}}
    <div class="pd-filter-bar">
        <form method="GET" action="{{ url('/admin/pendataan/aspek') }}" style="display:flex; gap:12px; align-items:center;">
            <label style="font-size:14px; font-weight:500; color:#475569;">Filter Periode:</label>
            <select name="periode_id" onchange="this.form.submit()" class="pd-form-control" style="min-width:220px;">
                <option value="">Semua Periode</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periode_id == $p->id ? 'selected' : '' }}>
                        {{ $p->nama ?? $p->tahun }} ({{ $p->tahun }}) {{ $p->is_aktif ? '✓' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Stats --}}
    <div class="pd-stats-bar">
        <div class="pd-stat-card">
            <div class="pd-stat-val">{{ $aspeks->count() }}</div>
            <div class="pd-stat-lbl">Total Aspek</div>
        </div>
        <div class="pd-stat-card">
            <div class="pd-stat-val">{{ $aspeks->where('aktif', 1)->count() }}</div>
            <div class="pd-stat-lbl">Aspek Aktif</div>
        </div>
        <div class="pd-stat-card">
            <div class="pd-stat-val">{{ $aspeks->sum('pertanyaan_count') }}</div>
            <div class="pd-stat-lbl">Total Pertanyaan</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="pd-table-card">
        <table class="pd-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th style="width:80px;">Urutan</th>
                    <th style="width:80px;">Kode</th>
                    <th>Nama Aspek</th>
                    <th style="width:130px;">Periode</th>
                    <th style="width:100px;">Pertanyaan</th>
                    <th style="width:90px;">Status</th>
                    <th style="width:130px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aspeks as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align:center;">{{ $a->urutan }}</td>
                    <td><strong>{{ $a->kode ?? '-' }}</strong></td>
                    <td>
                        <strong>{{ $a->nama }}</strong>
                        @if($a->keterangan)
                            <br><small style="color:#6B7280;">{{ Str::limit($a->keterangan, 60) }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="pd-badge pd-badge-info">
                            {{ $a->periode->tahun ?? '-' }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <a href="{{ url('/admin/pendataan/pertanyaan?aspek_id=' . $a->id . '&periode_id=' . $a->periode_id) }}" 
                           style="font-weight:600; color:#3B82F6;">
                            {{ $a->pertanyaan_count }}
                        </a>
                    </td>
                    <td style="text-align:center;">
                        @if($a->aktif)
                            <span class="pd-badge pd-badge-success">Aktif</span>
                        @else
                            <span class="pd-badge pd-badge-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <button class="pd-btn-icon" onclick="openEditModal(@js($a))" title="Edit">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="pd-btn-icon btn-danger" onclick="confirmDelete({{ $a->id }}, '{{ addslashes($a->nama) }}')" title="Hapus">
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
                        Belum ada data aspek. Klik "+ Tambah Aspek" untuk memulai.
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
@include('pendataan.admin.aspek.modals.create', ['periodes' => $periodes])
@include('pendataan.admin.aspek.modals.edit', ['periodes' => $periodes])

<style>
    .pd-aspek-container { max-width:1300px; margin:0 auto; padding:30px 24px; }
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
    .pd-table td { padding:14px 16px; font-size:14px; color:#374151; border-bottom:1px solid #F3F4F6; }
    .pd-badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; }
    .pd-badge-info { background:#DBEAFE; color:#1E40AF; }
    .pd-badge-success { background:#DCFCE7; color:#166534; }
    .pd-badge-secondary { background:#E5E7EB; color:#6B7280; }
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
const STORE_ROUTE = '{{ route("admin.pendataan.aspek.store") }}';
const UPDATE_ROUTE = '{{ route("admin.pendataan.aspek.update", ":id") }}';
const DELETE_ROUTE = '{{ route("admin.pendataan.aspek.destroy", ":id") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('show'); document.body.style.overflow='hidden'; }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('show'); document.body.style.overflow=''; }
}
function showToast(msg, type='success') {
    const t = document.getElementById('pd-toast');
    t.textContent = msg; t.className = `pd-toast ${type}`; t.style.display='block';
    setTimeout(() => t.style.display='none', 3000);
}

function openCreateModal() {
    document.getElementById('create-periode').value = '';
    document.getElementById('create-nama').value = '';
    document.getElementById('create-kode').value = '';
    document.getElementById('create-urutan').value = '';
    document.getElementById('create-keterangan').value = '';
    document.getElementById('create-aktif').checked = true;
    openModal('pdAspekCreateModal');
}

function openEditModal(data) {
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-periode').value = data.periode_id;
    document.getElementById('edit-nama').value = data.nama;
    document.getElementById('edit-kode').value = data.kode || '';
    document.getElementById('edit-urutan').value = data.urutan || '';
    document.getElementById('edit-keterangan').value = data.keterangan || '';
    document.getElementById('edit-aktif').checked = !!data.aktif;
    openModal('pdAspekEditModal');
}

function submitCreateForm(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('createForm'));
    fetch(STORE_ROUTE, { method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:formData })
        .then(r => r.json())
        .then(d => { showToast(d.message || 'Berhasil disimpan'); closeModal('pdAspekCreateModal'); setTimeout(() => location.reload(), 900); })
        .catch(() => showToast('Gagal menyimpan aspek', 'error'));
}

function submitEditForm(event) {
    event.preventDefault();
    const id = document.getElementById('edit-id').value;
    const formData = new FormData(document.getElementById('editForm'));
    fetch(UPDATE_ROUTE.replace(':id', id), { method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-HTTP-Method-Override':'PUT'}, body:formData })
        .then(r => r.json())
        .then(d => { showToast(d.message || 'Berhasil diperbarui'); closeModal('pdAspekEditModal'); setTimeout(() => location.reload(), 900); })
        .catch(() => showToast('Gagal memperbarui aspek', 'error'));
}

function confirmDelete(id, nama) {
    if (!confirm(`Yakin hapus aspek "${nama}"?\nPastikan tidak ada pertanyaan yang terkait.`)) return;
    fetch(DELETE_ROUTE.replace(':id', id), { method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-HTTP-Method-Override':'DELETE'} })
        .then(r => r.json())
        .then(d => { showToast(d.message || 'Berhasil dihapus'); setTimeout(() => location.reload(), 900); })
        .catch(() => showToast('Gagal menghapus aspek', 'error'));
}
</script>

@endsection
