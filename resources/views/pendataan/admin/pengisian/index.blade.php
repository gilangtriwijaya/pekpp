@extends('layouts.app')

@section('title', 'Tabel Hasil Pendataan')
@section('page_title', 'Hasil Pendataan UPP')

@section('content')
<style>
  .f02-container { max-width: 1400px; margin: 0 auto; padding: 24px 20px; }
  .f02-header { margin-bottom: 32px; }
  .f02-title { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 4px; }
  .f02-subtitle { font-size: 14px; color: #6B7280; margin-bottom: 24px; }
  .f02-filter-section { background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
  .f02-filter-group { display: flex; flex-direction: column; gap: 6px; }
  .f02-filter-group label { font-size: 13px; font-weight: 600; color: #374151; }
  .f02-filter-group select { padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; background-color: #FFFFFF; min-width: 220px; cursor: pointer; }
  .f02-filter-group select:focus { outline: none; border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
  .f02-filter-buttons { display: flex; gap: 8px; }
  .f02-btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; border: none; transition: all 0.2s ease; }
  .f02-btn-primary { background-color: #3B82F6; color: #FFFFFF; }
  .f02-btn-primary:hover { background-color: #2563EB; }
  .f02-btn-secondary { background-color: #EF4444; color: #FFFFFF; text-decoration: none; display: inline-block; }
  .f02-btn-secondary:hover { background-color: #DC2626; text-decoration: none; }
  .f02-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .f02-stat-card { background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; text-align: center; }
  .f02-stat-value { font-size: 32px; font-weight: 700; color: #3B82F6; margin-bottom: 8px; }
  .f02-stat-label { font-size: 13px; color: #6B7280; font-weight: 500; }
  .f02-table-card { background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; overflow: hidden; }
  .f02-table-header { padding: 20px; border-bottom: 1px solid #E5E7EB; }
  .f02-table-title { font-size: 18px; font-weight: 700; color: #111827; }
  .f02-table { width: 100%; border-collapse: collapse; }
  .f02-table thead tr { background-color: #F9FAFB; border-bottom: 2px solid #E5E7EB; }
  .f02-table th { padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #374151; white-space: nowrap; }
  .f02-table tbody tr { border-bottom: 1px solid #E5E7EB; transition: background-color 0.2s ease; }
  .f02-table tbody tr:hover { background-color: #F9FAFB; }
  .f02-table td { padding: 16px; font-size: 14px; color: #374151; }
  .f02-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
  .f02-badge-main { background-color: #DBEAFE; color: #1E40AF; }
  .f02-badge-warning { background-color: #FEF3C7; color: #92400E; }
  .f02-badge-success { background-color: #D1FAE5; color: #065F46; }
  .f02-badge-draft { background-color: #F3F4F6; color: #4B5563; }
  .f02-action-button { display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; background-color: #F3F4F6; color: #374151; font-size: 13px; font-weight: 600; border-radius: 6px; text-decoration: none; transition: all 0.2s; border: 1px solid #E5E7EB; }
  .f02-action-button:hover { background-color: #E5E7EB; color: #111827; text-decoration: none; }
  .f02-empty-state { padding: 48px 20px; text-align: center; }
  .f02-empty-icon { width: 48px; height: 48px; color: #9CA3AF; margin: 0 auto 16px auto; }
  .f02-empty-text { font-size: 14px; color: #6B7280; font-weight: 500; }
</style>

<div class="f02-container">
  <div class="f02-header">
    <div class="f02-title">Hasil Pendataan UPP</div>
    <div class="f02-subtitle">Daftar semua isian form pendataan yang telah dilakukan oleh setiap UPP</div>
  </div>

  <form method="GET" action="{{ route('admin.pendataan.pengisian.index') }}" class="f02-filter-section">
    <div class="f02-filter-group">
      <label for="periode_id">Filter Periode</label>
      <select name="periode_id" id="periode_id" onchange="this.form.submit()">
        <option value="">-- Semua Periode --</option>
        @foreach($periodes as $p)
          <option value="{{ $p->id }}" {{ ($periode && $periode->id == $p->id) ? 'selected' : '' }}>
            {{ $p->tahun }} {{ $p->is_aktif ? '(Aktif)' : '' }}
          </option>
        @endforeach
      </select>
    </div>
    
    <div class="f02-filter-buttons">
      <button type="submit" class="f02-btn f02-btn-primary">Terapkan Filter</button>
      <a href="{{ route('admin.pendataan.pengisian.index') }}" class="f02-btn f02-btn-secondary">Reset</a>
    </div>
  </form>

  <div class="f02-stats">
    <div class="f02-stat-card">
      <div class="f02-stat-value">{{ $total_upp }}</div>
      <div class="f02-stat-label">Total UPP Terdata</div>
    </div>
    <div class="f02-stat-card">
      <div class="f02-stat-value">{{ $sudah_submit }}</div>
      <div class="f02-stat-label">Sudah Submit</div>
    </div>
    <div class="f02-stat-card">
      <div class="f02-stat-value">{{ $draft }}</div>
      <div class="f02-stat-label">Masih Draft</div>
    </div>
  </div>

  <div class="f02-table-card">
    <div class="f02-table-header">
      <div class="f02-table-title">Daftar Hasil Pendataan</div>
    </div>
    
    @if($tablePengisians->count() > 0)
      <div style="overflow-x: auto;">
        <table class="f02-table">
          <thead>
            <tr>
              <th style="width: 50px; text-align: center;">No</th>
              <th>Nama UPP</th>
              <th>Periode</th>
              <th style="width: 140px;">Status</th>
              <th style="width: 200px;">Tanggal Submit</th>
              <th style="width: 100px; text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($tablePengisians as $index => $pengisian)
            <tr>
              <td style="text-align: center;">{{ $index + 1 }}</td>
              <td>
                <div style="font-weight: 600; color: #111827; margin-bottom: 4px;">{{ $pengisian->upp->nama ?? '-' }}</div>
                <div style="font-size: 12px; color: #6B7280;">ID UPP: {{ $pengisian->upp->id ?? '-' }}</div>
              </td>
              <td>{{ $pengisian->periode->tahun ?? '-' }}</td>
              <td>
                @if($pengisian->status === 'submitted')
                  <span class="f02-badge f02-badge-success">Submitted</span>
                @else
                  <span class="f02-badge f02-badge-draft">Draft</span>
                @endif
              </td>
              <td>
                @if($pengisian->submitted_at)
                  <div style="font-weight: 500; color: #374151;">{{ $pengisian->submitted_at->format('d M Y') }}</div>
                  <div style="font-size: 12px; color: #6B7280;">{{ $pengisian->submitted_at->format('H:i') }} WIB</div>
                @else
                  <span style="color: #9CA3AF; font-style: italic;">Belum disubmit</span>
                @endif
              </td>
              <td style="text-align: center;">
                <a href="{{ route('admin.pendataan.pengisian.show', $pengisian->id) }}" class="f02-action-button" target="_blank" title="Lihat Detail">Detail</a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="f02-empty-state">
        <svg class="f02-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <div class="f02-empty-text">Belum ada data pengisian.</div>
      </div>
    @endif
  </div>
</div>
@endsection
