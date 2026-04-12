@extends('layouts.app')

@section('title', 'F02 Validasi Penilaian')
@section('page_title', 'F02 Validasi Penilaian')

@section('content')
<style>
  .f02-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px 20px;
  }

  .f02-header {
    margin-bottom: 32px;
  }

  .f02-title {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
  }

  .f02-subtitle {
    font-size: 14px;
    color: #6B7280;
    margin-bottom: 24px;
  }

  .f02-filter-section {
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
    display: flex;
    gap: 12px;
    align-items: flex-end;
    flex-wrap: wrap;
  }

  .f02-filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .f02-filter-group label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
  }

  .f02-filter-group select {
    padding: 8px 12px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    font-size: 14px;
    background-color: #FFFFFF;
    min-width: 220px;
    cursor: pointer;
  }

  .f02-filter-group select:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .f02-filter-buttons {
    display: flex;
    gap: 8px;
  }

  .f02-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
  }

  .f02-btn-primary {
    background-color: #3B82F6;
    color: #FFFFFF;
  }

  .f02-btn-primary:hover {
    background-color: #2563EB;
  }

  .f02-btn-secondary {
    background-color: #EF4444;
    color: #FFFFFF;
    text-decoration: none;
    display: inline-block;
  }

  .f02-btn-secondary:hover {
    background-color: #DC2626;
    text-decoration: none;
  }

  .f02-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .f02-stat-card {
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
  }

  .f02-stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #3B82F6;
    margin-bottom: 8px;
  }

  .f02-stat-label {
    font-size: 13px;
    color: #6B7280;
    font-weight: 500;
  }

  .f02-table-card {
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    overflow: hidden;
  }

  .f02-table-header {
    padding: 20px;
    border-bottom: 1px solid #E5E7EB;
  }

  .f02-table-title {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
  }

  .f02-table {
    width: 100%;
    border-collapse: collapse;
  }

  .f02-table thead tr {
    background-color: #F9FAFB;
    border-bottom: 2px solid #E5E7EB;
  }

  .f02-table th {
    padding: 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
  }

  .f02-table tbody tr {
    border-bottom: 1px solid #E5E7EB;
    transition: background-color 0.2s ease;
  }

  .f02-table tbody tr:hover {
    background-color: #F9FAFB;
  }

  .f02-table td {
    padding: 16px;
    font-size: 14px;
    color: #374151;
  }

  .f02-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }

  .f02-badge-main {
    background-color: #DBEAFE;
    color: #1E40AF;
  }

  .f02-badge-warning {
    background-color: #FEF3C7;
    color: #92400E;
  }

  .f02-badge-info {
    background-color: #CFFAFE;
    color: #0C4A6E;
  }

  .f02-badge-success {
    background-color: #DCFCE7;
    color: #166534;
  }

  .f02-total-nilai {
    font-weight: 700;
    color: #2563EB;
    text-align: center;
  }

  .f02-action-button {
    display: inline-block;
    padding: 8px 16px;
    background-color: #3B82F6;
    color: #FFFFFF;
    text-decoration: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    transition: background-color 0.2s ease;
  }

  .f02-action-button:hover {
    background-color: #2563EB;
    text-decoration: none;
  }

  .f02-empty {
    text-align: center;
    padding: 60px 20px;
    color: #9CA3AF;
  }

  .f02-empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
  }

  .f02-empty-text {
    font-size: 16px;
    font-weight: 500;
  }

  .f02-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    margin-top: 12px;
    width: fit-content;
  }

  .f02-status-badge.open {
    background-color: #DCFCE7;
    color: #166534;
    border-left: 3px solid #16A34A;
  }

  .f02-status-badge.locked {
    background-color: #FED7AA;
    color: #92400E;
    border-left: 3px solid #F59E0B;
  }

  .f02-status-badge.closed {
    background-color: #FECACA;
    color: #7F1D1D;
    border-left: 3px solid #DC2626;
  }

  .f02-bulk-actions {
    display: none;
    padding: 16px;
    background-color: #EFF6FF;
    border: 1px solid #93C5FD;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
  }

  .f02-bulk-actions.show {
    display: flex;
  }

  .f02-bulk-actions-info {
    font-size: 14px;
    color: #1E40AF;
    font-weight: 500;
  }

  .f02-bulk-actions-buttons {
    display: flex;
    gap: 8px;
  }

  .f02-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .f02-btn-resubmit {
    background-color: #8B5CF6;
    color: #FFFFFF;
  }

  .f02-btn-resubmit:hover {
    background-color: #7C3AED;
  }

  .f02-btn-danger {
    background-color: #EF4444;
    color: #FFFFFF;
  }

  .f02-btn-danger:hover {
    background-color: #DC2626;
  }
</style>

<div class="f02-container">
  <div class="f02-header">
    <div class="f02-title">F02 Validasi Penilaian</div>
    <div class="f02-subtitle">Review dan validasi pengisian F01 yang telah disubmit</div>
    @if($activePeriode)
    <div class="f02-status-badge {{ $activePeriode->status_pengisian }}">
      @if($activePeriode->status_pengisian === 'open')
        <span>🟢</span>
        <span>Status: Menerima Input</span>
      @elseif($activePeriode->status_pengisian === 'locked')
        <span>🔒</span>
        <span>Status: Input Terkunci (Tidak Menerima Input Baru)</span>
      @else
        <span>🗂️</span>
        <span>Status: Arsip (Ditutup)</span>
      @endif
    </div>
    @endif
  </div>

  <div class="f02-filter-section">
    <form method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; width: 100%;">
      <div class="f02-filter-group">
        <label for="periode_id">Periode</label>
        <select name="periode_id" id="periode_id">
          <option value="">Semua Periode</option>
          @foreach(($periodes ?? []) as $pd)
          <option value="{{ $pd->id }}" {{ request('periode_id') == $pd->id ? 'selected' : '' }}>{{ $pd->nama }} ({{ $pd->tahun }})</option>
          @endforeach
        </select>
      </div>
      <div class="f02-filter-buttons">
        <button type="submit" class="f02-btn f02-btn-primary">Filter</button>
        <a href="{{ route('f02.index') }}" class="f02-btn f02-btn-secondary">Reset</a>
      </div>
    </form>
  </div>

  @php
    $total = $pengisians->count();
    $belum_validasi = $pengisians->filter(fn($p) => $p->f02_status === 'belum_divalidasi')->count();
    $dalam_proses = $pengisians->filter(fn($p) => in_array($p->f02_status, ['draft', 'dalam_proses']))->count();
    $selesai = $pengisians->filter(fn($p) => $p->f02_status === 'selesai')->count();
  @endphp

  <div class="f02-stats">
    <div class="f02-stat-card">
      <div class="f02-stat-value">{{ $total }}</div>
      <div class="f02-stat-label">Total Submit Pengisian</div>
    </div>
    <div class="f02-stat-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(37, 99, 235, 0.08) 100%); border-left: 3px solid #3B82F6; cursor: pointer; transition: all 0.2s ease;" onclick="openUppProgressModal()" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
      <div class="f02-stat-value" style="color: #2563EB;">{{ $uppDalamProgress }}</div>
      <div class="f02-stat-label">UPP Dalam Progress</div>
      <div style="font-size: 11px; color: #6B7280; margin-top: 4px;">Mulai mengisi, belum submit</div>
      <div style="font-size: 10px; color: #3B82F6; margin-top: 6px; font-weight: 600;">📋 Klik untuk detail</div>
    </div>
    <div class="f02-stat-card">
      <div class="f02-stat-value">{{ $belum_validasi }}</div>
      <div class="f02-stat-label">Belum Divalidasi</div>
    </div>
    <div class="f02-stat-card">
      <div class="f02-stat-value">{{ $dalam_proses }}</div>
      <div class="f02-stat-label">Dalam Proses</div>
    </div>
    <div class="f02-stat-card">
      <div class="f02-stat-value">{{ $selesai }}</div>
      <div class="f02-stat-label">Selesai</div>
    </div>
  </div>

  <!-- Export Progress Section -->
  <div style="margin-bottom: 24px; display: flex; gap: 12px;">
    <a href="{{ route('f02.export.progress', ['format' => 'csv']) }}" class="f02-action-button" style="background-color: #10B981; display: inline-flex; align-items: center; gap: 6px;">
      📥 Export CSV
    </a>
    <a href="{{ route('f02.export.progress', ['format' => 'pdf']) }}" class="f02-action-button" style="background-color: #EF4444; display: inline-flex; align-items: center; gap: 6px;">
      📄 Export PDF
    </a>
  </div>

  <!-- Bulk Actions Bar (Hidden by default) -->
  <div id="bulkActionsBar" class="f02-bulk-actions">
    <div class="f02-bulk-actions-info">
      <span id="selectedCount">0</span> pengisian dipilih
    </div>
    <div class="f02-bulk-actions-buttons">
      <button id="cancelBulkBtn" class="f02-btn f02-btn-secondary" onclick="cancelBulkSelection()">Batal</button>
      <button id="allowResubmitBulkBtn" class="f02-btn f02-btn-resubmit" onclick="allowResubmitBulk()">Izinkan Pengisian Ulang (Terpilih)</button>
    </div>
  </div>

  <div class="f02-table-card">
    <div class="f02-table-header">
      <div class="f02-table-title">Daftar Pengisian Validasi</div>
    </div>
    
    @forelse($pengisians as $pengisian)
      @if ($loop->first)
      <table class="f02-table">
        <thead>
          <tr>
            <th style="width: 40px; text-align: center;">
              <input type="checkbox" id="selectAllChk" class="f02-checkbox" onchange="toggleSelectAll(event)">
            </th>
            <th style="width: 200px;">UPP</th>
            <th style="width: 150px;">Periode</th>
            <th style="width: 130px;">Status F01</th>
            <th style="width: 140px;">Status Validasi</th>
            <th style="width: 110px; text-align: center;">Total Skor</th>
            <th style="width: 110px; text-align: center;">Nilai F02</th>
            <th style="width: 170px;">Tanggal Diisi (WIB)</th>
            <th style="width: 180px; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
      @endif
      <tr id="f02-row-{{ $pengisian->f02_id }}">
        <td style="text-align: center;">
          <input type="checkbox" class="f02-checkbox pengisian-checkbox" value="{{ $pengisian->f02_id }}" onchange="updateBulkSelection()" {{ $pengisian->f02_status === 'selesai' ? '' : 'disabled' }} style="{{ $pengisian->f02_status !== 'selesai' ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
        </td>
        <td>
          <strong>{{ $pengisian->upp?->nama ?? 'N/A' }}</strong>
        </td>
        <td>
          <span class="f02-badge f02-badge-main">{{ $pengisian->periode?->nama ?? $pengisian->periode?->tahun ?? 'N/A' }}</span>
        </td>
        <td>
          <span class="f02-badge {{ 
            $pengisian->status === 'draft' ? 'f02-badge-info' : 
            ($pengisian->status === 'submitted' ? 'f02-badge-warning' : 'f02-badge-success') 
          }}">
            {{ ucfirst(str_replace('_', ' ', $pengisian->status)) }}
          </span>
        </td>
        <td>
          <div>
            <span class="f02-badge {{ 
              $pengisian->f02_status === 'belum_divalidasi' ? 'f02-badge-warning' : 
              (in_array($pengisian->f02_status, ['draft', 'dalam_proses']) ? 'f02-badge-info' : 'f02-badge-success') 
            }}">
              @switch($pengisian->f02_status)
                @case('belum_divalidasi')
                  Belum Divalidasi
                @break
                @case('draft')
                  Dalam Proses
                @break
                @case('dalam_proses')
                  Dalam Proses
                @break
                @case('selesai')
                  Selesai
                @break
                @default
                  {{ ucfirst(str_replace('_', ' ', $pengisian->f02_status)) }}
              @endswitch
            </span>
            
            {{-- Show validator name --}}
            @if($pengisian->f02_status === 'selesai')
              @if($pengisian->f02_validator && $pengisian->f02_validator->nama)
                <div style="font-size: 11px; color: #6B7280; margin-top: 4px; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  {{ $pengisian->f02_validator->nama }}
                </div>
              @endif
            @elseif(in_array($pengisian->f02_status, ['draft', 'dalam_proses']))
              @if($pengisian->f02_updated_by && $pengisian->f02_updated_by->nama)
                <div style="font-size: 11px; color: #6B7280; margin-top: 4px; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  {{ $pengisian->f02_updated_by->nama }}
                </div>
              @endif
            @endif
          </div>
        </td>
        <td class="f02-total-nilai" style="text-align: center;">
          @if($pengisian->f02?->nilai_mentah !== null)
            {{ number_format($pengisian->f02->nilai_mentah, 0) }}
          @else
            <span style="color: #D1D5DB;">-</span>
          @endif
        </td>
        <td class="f02-total-nilai" style="text-align: center;">
          @if($pengisian->f02?->total_nilai !== null)
            {{ number_format($pengisian->f02->total_nilai, 2) }}
          @else
            <span style="color: #D1D5DB;">-</span>
          @endif
        </td>
        <td>{{ $pengisian->created_at->format('d M Y H:i') }} WIB</td>
        <td style="text-align: center;">
          <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
            <a href="{{ route('f02.show', $pengisian->id) }}" class="f02-action-button">Validasi</a>
            @if($pengisian->f02_status === 'selesai')
            <button onclick="allowResubmitSingle({{ $pengisian->f02_id }})" class="f02-btn f02-btn-resubmit" style="padding: 8px 12px; font-size: 12px; white-space: nowrap;">
              🔄 Ulang
            </button>
            @endif
          </div>
        </td>
      </tr>
      @if ($loop->last)
        </tbody>
      </table>
      @endif
    @empty
    <div class="f02-empty">
      <div class="f02-empty-icon">📋</div>
      <div class="f02-empty-text">Tidak ada pengisian yang perlu divalidasi.</div>
    </div>
    @endforelse
  </div>
</div>

<!-- Modal UPP Dalam Progress -->
<div id="uppProgressModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; justify-content: center; align-items: center;">
  <div style="background-color: white; border-radius: 12px; padding: 0; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);">
    
    <!-- Modal Header -->
    <div style="padding: 24px; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">UPP Dalam Progress</h2>
        <p style="font-size: 13px; color: #6B7280; margin: 4px 0 0 0;">Organisasi yang sudah mulai mengisi F01 tapi belum submit</p>
      </div>
      <button onclick="closeUppProgressModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; color: #6B7280; transition: color 0.2s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6B7280'">✕</button>
    </div>

    <!-- Modal Body -->
    <div style="padding: 24px;">
      @if($uppDalamProgressDetail->isEmpty())
        <div style="text-align: center; padding: 40px 20px; color: #6B7280;">
          <div style="font-size: 48px; margin-bottom: 12px;">✓</div>
          <p>Semua organisasi sudah submit pengisiannya</p>
        </div>
      @else
        <div style="display: flex; flex-direction: column; gap: 12px;">
          @foreach($uppDalamProgressDetail as $upp)
          <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px; background: #FAFAFA; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#F3F4F6'; this.style.borderColor='#3B82F6'" onmouseout="this.style.backgroundColor='#FAFAFA'; this.style.borderColor='#E5E7EB'">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
              <div style="flex: 1;">
                <h3 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 4px 0;">{{ $upp['upp_nama'] }}</h3>
                <p style="font-size: 12px; color: #6B7280; margin: 0; text-transform: capitalize;">Status: <strong>{{ str_replace('_', ' ', $upp['status'] ?? 'draft') }}</strong></p>
                <p style="font-size: 12px; color: #6B7280; margin: 4px 0 0 0;"><strong>{{ $upp['answered_indikator'] ?? 0 }} / {{ $upp['total_indikator'] ?? 0 }}</strong> indikator telah diisi</p>
              </div>
              <span style="display: inline-block; padding: 4px 8px; background: #DBEAFE; color: #1E40AF; font-size: 11px; font-weight: 600; border-radius: 4px;">{{ $upp['aspek_progress'] ?? 0 }}% Progress</span>
            </div>
            
            <!-- Progress Bar -->
            <div style="width: 100%; height: 6px; background-color: #E5E7EB; border-radius: 3px; overflow: hidden;">
              <div style="height: 100%; background: linear-gradient(90deg, #3B82F6 0%, #2563EB 100%); width: {{ $upp['aspek_progress'] ?? 0 }}%; transition: width 0.3s ease;"></div>
            </div>
            
            <div style="margin-top: 12px; display: flex; gap: 12px; justify-content: space-between; align-items: center;">
              <span style="font-size: 11px; color: #9CA3AF;">
                Updated: <strong>{{ $upp['last_update']?->diffForHumans() ?? 'Tidak ada update' }}</strong>
              </span>
            </div>
          </div>
          @endforeach
        </div>
      @endif
    </div>

    <!-- Modal Footer -->
    <div style="padding: 16px 24px; border-top: 1px solid #E5E7EB; background: #F9FAFB; border-radius: 0 0 12px 12px; text-align: right;">
      <button onclick="closeUppProgressModal()" style="padding: 8px 16px; background-color: #3B82F6; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#2563EB'" onmouseout="this.style.backgroundColor='#3B82F6'">Tutup</button>
    </div>
  </div>
</div>

<script>
function openUppProgressModal() {
  document.getElementById('uppProgressModal').style.display = 'flex';
}

function closeUppProgressModal() {
  document.getElementById('uppProgressModal').style.display = 'none';
}

// Close modal when clicking outside modal content
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('uppProgressModal');
  if (modal) {
    modal.addEventListener('click', function(event) {
      if (event.target === modal) {
        closeUppProgressModal();
      }
    });
  }
});

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeUppProgressModal();
  }
});

// ========== Resubmit Functionality ==========

function toggleSelectAll(event) {
  const checkboxes = document.querySelectorAll('.pengisian-checkbox:not([disabled])');
  checkboxes.forEach(checkbox => {
    checkbox.checked = event.target.checked;
  });
  updateBulkSelection();
}

function updateBulkSelection() {
  const checkboxes = document.querySelectorAll('.pengisian-checkbox:checked');
  const count = checkboxes.length;
  const bulkBar = document.getElementById('bulkActionsBar');
  const selectedCount = document.getElementById('selectedCount');
  
  if (count > 0) {
    selectedCount.textContent = count;
    bulkBar.classList.add('show');
  } else {
    bulkBar.classList.remove('show');
    document.getElementById('selectAllChk').checked = false;
  }
}

function cancelBulkSelection() {
  document.querySelectorAll('.f02-checkbox').forEach(cb => cb.checked = false);
  document.getElementById('bulkActionsBar').classList.remove('show');
}

function allowResubmitSingle(f02Id) {
  if (confirm('Yakin ingin mengizinkan UPP ini untuk mengisi ulang pengisian F01?')) {
    fetch('{{ route("f02.allow-resubmit", ":f02") }}'.replace(':f02', f02Id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        catatan: prompt('Masukkan catatan (opsional):') || ''
      })
    })
    .then(async response => {
      const text = await response.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('Non-JSON response', text);
        throw new Error('Server response bukan JSON: ' + text);
      }

      if (data.success) {
        alert(data.message);
        // Reload page untuk update counter dan list
        window.location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan: ' + (error.message || 'silakan coba lagi'));
    });
  }
}

function allowResubmitBulk() {
  const checkboxes = document.querySelectorAll('.pengisian-checkbox:checked');
  const f02Ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
  
  if (f02Ids.length === 0) {
    alert('Pilih minimal 1 pengisian');
    return;
  }
  
  if (!confirm(`Yakin ingin mengizinkan ${f02Ids.length} UPP untuk mengisi ulang pengisian F01?`)) {
    return;
  }
  
  fetch('{{ route("f02.allow-resubmit-bulk") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({
      f02_ids: f02Ids
    })
  })
  .then(async response => {
    const text = await response.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error('Non-JSON response', text);
      throw new Error('Server response bukan JSON: ' + text);
    }

    const summary = data.summary;
    let message = `Proses selesai!\n`;
    message += `✓ Berhasil: ${summary.success_count}\n`;
    if (summary.failed_count > 0) {
      message += `✗ Gagal: ${summary.failed_count}\n`;
    }

    alert(message);
    // Reload page untuk update counter dan list
    window.location.reload();
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan: ' + (error.message || 'silakan coba lagi'));
  });
}
</script>

@endsection
