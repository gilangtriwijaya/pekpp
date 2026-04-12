@extends('layouts.app')

@section('title', 'F02 Validasi Penilaian')
@section('page_title', 'F02 Validasi Penilaian')

@section('content')
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  
  .f02-container {
    height: calc(100vh - 60px);
    display: flex;
    flex-direction: column;
    background: #f5f5f5;
    padding: 16px;
    margin: 0;
  }

  .f02-header {
    background: white;
    padding: 16px 24px;
    border-bottom: 1px solid #e0e0e0;
    flex-shrink: 0;
    border-radius: 6px 6px 0 0;
    margin-bottom: 12px;
  }

  .f02-header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
  }

  .f02-header-left {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .f02-header-title {
    font-size: 16px;
    font-weight: 700;
    margin-top: 4px;
  }

  .f02-header-periode {
    font-size: 11px;
    color: #666;
  }

  .f02-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .f02-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: white;
    color: #2196f3;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #2196f3;
    border-radius: 4px;
    transition: all 0.2s;
    white-space: nowrap;
  }

  .f02-back-link:hover {
    background: #e3f2fd;
    color: #1976d2;
    border-color: #1976d2;
  }

  .f02-status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 12px;
  }

  .f02-status-draft { background: #e3f2fd; color: #1976d2; }
  .f02-status-selesai { background: #c8e6c9; color: #388e3c; }

  /* Aspek Tabs Container */
  .aspek-tabs-container {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    overflow-x: auto;
    overflow-y: hidden;
    background: #fafafa;
    width: 100%;
    flex-shrink: 0;
    max-height: 90px;
    padding: 8px 24px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 12px;
  }

  .aspek-tabs-container::-webkit-scrollbar {
    height: 6px;
  }

  .aspek-tabs-container::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  .aspek-tabs-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
  }

  .aspek-tabs-container::-webkit-scrollbar-thumb:hover {
    background: #555;
  }

  .aspek-tab {
    flex-shrink: 0;
    min-width: 152px;
    padding: 12px 14px;
    text-align: center;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    color: #666;
    background: transparent;
    transition: all 0.2s;
    white-space: nowrap;
    border-bottom: 3px solid transparent;
    margin: 0 4px;
  }

  .aspek-tab:hover {
    background: #ebebeb;
    border-radius: 4px 4px 0 0;
  }
  .aspek-tab.active {
    border-bottom-color: #2196f3;
    color: #2196f3;
    background: #e3f2fd;
    border-radius: 4px 4px 0 0;
  }

  .aspek-tab-name { font-size: 12px; font-weight: 600; }
  .aspek-tab-bobot { font-size: 10px; color: #999; margin-top: 3px; }

  /* Main Content Area */
  .f02-main {
    flex: 1;
    display: flex;
    gap: 16px;
    padding: 16px 20px;
    min-height: 0;
    overflow: hidden;
  }

  .f02-form {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }

  .f02-content {
    flex: 1;
    background: white;
    border-radius: 8px;
    overflow-y: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    min-height: 0;
    padding: 16px;
  }

  .indikator-card {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 14px;
    margin-bottom: 14px;
    background: #f9f9f9;
    transition: all 0.2s;
  }

  .indikator-card:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
  }

  .indikator-header {
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
  }

  .indikator-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #2196f3;
    color: white;
    border-radius: 50%;
    font-weight: 700;
    font-size: 12px;
    margin-right: 10px;
  }

  .indikator-name {
    display: flex;
    align-items: center;
    font-size: 13px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
  }

  .indikator-average {
    font-size: 11px;
    color: #666;
  }

  .indikator-average strong {
    color: #2196f3;
    font-weight: 600;
  }

  /* Bukti Section */
  .bukti-section {
    margin-bottom: 12px;
    padding: 10px;
    background: #fff;
    border-radius: 3px;
    border-left: 3px solid #ff9800;
  }

  .bukti-section-title {
    font-weight: 600;
    font-size: 11px;
    color: #333;
    margin-bottom: 6px;
  }

  .bukti-link {
    color: #2196f3;
    text-decoration: none;
    display: block;
    word-break: break-word;
    font-size: 10px;
    padding: 4px 0;
  }

  .bukti-link:hover {
    text-decoration: underline;
  }

  /* Pertanyaan Section */
  .pertanyaan-section {
    margin-bottom: 12px;
    padding: 10px;
    background: #fff;
    border-radius: 3px;
    border-left: 3px solid #2196f3;
  }

  .pertanyaan-section-title {
    font-weight: 600;
    font-size: 11px;
    color: #333;
    margin-bottom: 8px;
  }

  .pertanyaan-item {
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
    font-size: 11px;
  }

  .pertanyaan-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
  }

  .pertanyaan-number {
    display: inline-block;
    font-weight: 700;
    color: #2196f3;
    min-width: 20px;
  }

  .pertanyaan-text {
    color: #333;
    margin-bottom: 4px;
    font-weight: 500;
    line-height: 1.4;
  }

  .jawaban-text {
    color: #555;
    line-height: 1.4;
    padding-left: 20px;
    border-left: 2px solid #e0e0e0;
    padding-top: 4px;
    padding-bottom: 4px;
  }

  /* Form Section */
  .form-section {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 10px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e0e0e0;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
  }

  .form-input {
    padding: 6px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 11px;
    font-family: inherit;
    resize: none;
  }

  .form-input:focus {
    outline: none;
    border-color: #2196f3;
    box-shadow: 0 0 3px rgba(33, 150, 243, 0.3);
  }

  /* Sidebar */
  .f02-sidebar {
    width: 280px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
    padding-right: 4px;
  }

  .sidebar-card {
    background: white;
    padding: 14px;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    flex-shrink: 0;
  }

  .sidebar-card-title {
    font-size: 10px;
    color: #999;
    margin-bottom: 6px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .total-nilai {
    border-top: 3px solid #2196f3;
  }

  .total-nilai-value {
    font-size: 36px;
    font-weight: 700;
    color: #2196f3;
    margin-bottom: 2px;
  }

  .total-nilai-label {
    font-size: 10px;
    color: #666;
  }

  .formula-box {
    background: #e3f2fd;
    padding: 8px;
    border-radius: 4px;
    margin-top: 8px;
    font-size: 9px;
    color: #1976d3;
    line-height: 1.4;
  }

  .sidebar-content {
    font-size: 10px;
    max-height: 150px;
    overflow-y: auto;
  }

  .sidebar-item {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    border-bottom: 1px solid #eee;
    color: #666;
  }

  .sidebar-bobot {
    font-weight: 600;
    color: #2196f3;
    flex-shrink: 0;
  }

  .scale-item {
    margin-bottom: 6px;
    color: #666;
  }

  .scale-label {
    font-weight: 600;
  }

  .action-buttons {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex-shrink: 0;
  }

  .btn {
    width: 100%;
    padding: 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 11px;
    transition: all 0.2s;
    color: white;
  }

  .btn-draft { background: #999; }
  .btn-draft:hover { background: #777; }

  .btn-finalize { background: #4caf50; }
  .btn-finalize:hover { background: #388e3c; }

  .btn-reject { background: #f44336; }
  .btn-reject:hover { background: #da190b; }

  .history-box {
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    font-size: 10px;
    flex-shrink: 0;
  }

  .history-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
  }

  .history-item {
    color: #666;
    margin-bottom: 4px;
  }

  .history-value {
    font-weight: 600;
    color: #2196f3;
  }

  .aspekContent { display: none; }
  .aspekContent.active { display: block; }
</style>

<div class="f02-container">
  <!-- Header -->
  <div class="f02-header">
    <div class="f02-header-content">
      <div class="f02-header-left">
        <div class="f02-header-title">Validasi F01: {{ $pengisian->upp?->nama }}</div>
        <div class="f02-header-periode">Periode: {{ $pengisian->periode?->nama }}</div>
      </div>
      <div class="f02-header-right">
        <div class="f02-status-badge f02-status-{{ $f02->status === 'draft' ? 'draft' : 'selesai' }}">
          {{ ucfirst($f02->status) }}
        </div>
      <a href="{{ route('f02.index') }}" class="f02-back-link">
        <span style="font-size: 14px;">←</span> Kembali
      </a>
      </div>
    </div>
  </div>

  <!-- Aspek Tabs (Horizontal Scroll Only) -->
  <div class="aspek-tabs-container">
    @foreach($aspekData as $index => $aspek)
      <button type="button" 
              class="aspek-tab {{ $index === 0 ? 'active' : '' }}"
              data-aspek-id="{{ $aspek['id'] }}"
              onclick="switchAspek(event, this)">
        <div class="aspek-tab-name">{{ $aspek['nama'] }}</div>
        <div class="aspek-tab-bobot">{{ $aspek['bobot'] ?? 0 }}%</div>
      </button>
    @endforeach
  </div>

  <!-- Main Content Area -->
  <div class="f02-main">
    <form id="validasiForm" class="f02-form" method="POST" {{ $f02->status === 'selesai' ? 'disabled' : '' }}>
      @csrf
      
      <!-- Indikator Content (Vertical Scroll Only) -->
      <div class="f02-content" style="{{ $f02->status === 'selesai' ? 'opacity: 0.8;' : '' }}">
        @foreach($aspekData as $index => $aspek)
          <div class="aspekContent {{ $index === 0 ? 'active' : '' }}" data-aspek-id="{{ $aspek['id'] }}">
            @foreach($aspek['indikators'] as $indIdx => $indikator)
              <div class="indikator-card">
                <!-- Indikator Header dengan Numbering -->
                <div class="indikator-header">
                  <div class="indikator-name">
                    <span class="indikator-number">{{ $indIdx + 1 }}</span>
                    <span>{{ $indikator['nama'] }}</span>
                  </div>
                </div>

                <!-- Bukti Section (Show Only 1 Per Indikator) -->
                @if(!empty($indikator['pertanyaan'][0]['bukti']))
                  <div class="bukti-section">
                    <div class="bukti-section-title">🔗 Bukti Dukung:</div>
                    @foreach($indikator['pertanyaan'][0]['bukti'] as $bukti)
                      <a href="{{ $bukti['path_atau_url'] }}" target="_blank" class="bukti-link">
                        {{ substr($bukti['path_atau_url'], 0, 60) }}{{ strlen($bukti['path_atau_url']) > 60 ? '...' : '' }}
                      </a>
                    @endforeach
                  </div>
                @endif

                <!-- Pertanyaan & Jawaban (Format Nomor) -->
                <div class="pertanyaan-section">
                  <div class="pertanyaan-section-title">❓ Pertanyaan & Jawaban:</div>
                  @if(!empty($indikator['pertanyaan']) && is_array($indikator['pertanyaan']) && count($indikator['pertanyaan']) > 0)
                    @foreach($indikator['pertanyaan'] as $qIdx => $tanya)
                      <div class="pertanyaan-item">
                        <div class="pertanyaan-text">
                          <span class="pertanyaan-number">{{ $qIdx + 1 }})</span>
                          {{ $tanya['pertanyaan'] ?? 'N/A' }}
                        </div>
                        <div class="jawaban-text">{{ $tanya['jawaban'] ?? '-' }}</div>
                      </div>
                    @endforeach
                  @else
                    <div style="padding: 8px; color: #999; font-size: 10px;">Tidak ada pertanyaan tersedia</div>
                  @endif
                </div>

                <!-- Form: Nilai & Catatan -->
                <div class="form-section">
                  @if($f02->status === 'selesai')
                    <!-- Display Hasil Validasi (Read-only) -->
                    <div class="hasil-validasi-section" style="background: #f0f9ff; padding: 12px; border-radius: 6px; border-left: 4px solid #3b82f6;">
                      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                          <label style="font-weight: 600; color: #374151; font-size: 13px; display: block; margin-bottom: 4px;">Nilai Validasi</label>
                          <div style="font-size: 24px; font-weight: 700; color: #1e40af;">{{ $indikator['nilai'] ?? '-' }}</div>
                          <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                            @switch($indikator['nilai'])
                              @case(0)Tidak Memenuhi@break
                              @case(1)Buruk@break
                              @case(2)Kurang@break
                              @case(3)Cukup@break
                              @case(4)Baik@break
                              @case(5)Sangat Baik@break
                              @default-@endswitch
                          </div>
                        </div>
                        <div>
                          <label style="font-weight: 600; color: #374151; font-size: 13px; display: block; margin-bottom: 4px;">Catatan Validasi</label>
                          <div style="background: white; padding: 8px; border-radius: 4px; font-size: 13px; color: #374151; min-height: 50px; border: 1px solid #e5e7eb;">{{ $indikator['catatan'] ?? 'Tidak ada catatan' }}</div>
                        </div>
                      </div>
                      <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #dbeafe;">
                        <div style="font-size: 11px; color: #6b7280;">
                          <strong>✓ Divalidasi pada:</strong> 
                          @if($f02->divalidasi_pada)
                            {{ \Carbon\Carbon::parse($f02->divalidasi_pada)->format('d M Y H:i') }} WIB
                          @else
                            N/A
                          @endif
                        </div>
                      </div>
                    </div>
                    <!-- Edit Form (Draft/In Progress) -->
                    <div class="form-group">
                      <label class="form-label">Nilai *</label>
                      <select name="nilai[{{ $indikator['id'] }}]" 
                              class="form-input nilaiSelect"
                              data-indikator-id="{{ $indikator['id'] }}"
                              onchange="updateTotalNilai(); showSkorNarasi(this)">
                        <option value="">--</option>
                        <option value="0" {{ $indikator['nilai'] == 0 ? 'selected' : '' }}>0 - Tidak Memenuhi</option>
                        <option value="1" {{ $indikator['nilai'] == 1 ? 'selected' : '' }}>1 - Buruk</option>
                        <option value="2" {{ $indikator['nilai'] == 2 ? 'selected' : '' }}>2 - Kurang</option>
                        <option value="3" {{ $indikator['nilai'] == 3 ? 'selected' : '' }}>3 - Cukup</option>
                        <option value="4" {{ $indikator['nilai'] == 4 ? 'selected' : '' }}>4 - Baik</option>
                        <option value="5" {{ $indikator['nilai'] == 5 ? 'selected' : '' }}>5 - Sangat Baik</option>
                      </select>
                    </div>
                    <!-- Score Narrative Display -->
                    <div class="skor-narasi-box" id="narasi-{{ $indikator['id'] }}" style="display: none; background: #f0f9ff; padding: 12px; border-radius: 6px; border-left: 3px solid #3b82f6; margin-bottom: 12px; font-size: 12px;">
                      <div style="font-weight: 600; color: #1e40af; margin-bottom: 6px;">📋 Narasi Skor:</div>
                      <div id="narasi-text-{{ $indikator['id'] }}" style="color: #374151; line-height: 1.5;"></div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Catatan *</label>
                      <textarea name="catatan[{{ $indikator['id'] }}]" 
                                rows="2"
                                class="form-input"
                                placeholder="Min. 5 karakter">{{ $indikator['catatan'] ?? '' }}</textarea>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endforeach
      </div>
    </form>

    <!-- Sidebar -->
    <div class="f02-sidebar">
      <!-- Total Nilai F02 -->
      <div class="sidebar-card total-nilai">
        <div class="sidebar-card-title">Total Nilai F02</div>
        <div class="total-nilai-value" id="totalNilaiDisplay">{{ number_format($totalNilai, 2) }}</div>
        <div class="total-nilai-label">dari 5.00 poin</div>
        <div class="formula-box">
          <strong>Formula:</strong><br>Σ(nilai × bobot) / 100
        </div>
      </div>

      <!-- Action Buttons -->
      @if($f02->status !== 'selesai')
        <div class="action-buttons">
          <button type="button" id="saveDraftBtn" class="btn btn-draft">💾 Draft</button>
          <button type="button" id="finalizeBtn" class="btn btn-finalize">✓ Finalize</button>
          <button type="button" id="rejectBtn" class="btn btn-reject">↩ Reject</button>
        </div>
      @else
        <!-- Validation Complete Info -->
        <div class="sidebar-card" style="background: #dcfce7; border-color: #86efac;">
          <div style="text-align: center; padding: 16px;">
            <div style="font-size: 28px; margin-bottom: 8px;">✓</div>
            <div style="font-weight: 700; color: #166534; margin-bottom: 4px;">Validasi Selesai</div>
            <div style="font-size: 12px; color: #4b7c35; margin-bottom: 12px;">Semua indikator telah divalidasi</div>
            <div style="background: white; padding: 8px; border-radius: 4px; font-size: 11px; color: #374151;">
              <strong>Total Nilai:</strong><br>
              <span style="font-size: 20px; font-weight: 700; color: #16a34a;">{{ number_format($f02->total_nilai, 2) }}</span>
              <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">dari 5.00 poin</div>
            </div>
          </div>
        </div>
      @endif

      <!-- History -->
      @if($f02->divalidasi_pada)
        <div class="history-box">
          <div class="history-title">Riwayat Validasi</div>
          <div class="history-item">
            <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($f02->divalidasi_pada)->format('d M Y H:i') }} WIB
          </div>
          <div class="history-item">
            <strong>Nilai:</strong> <span class="history-value">{{ number_format($f02->total_nilai, 2) }}</span>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

<script>
  const pengisianId = {{ $pengisian->id }};
  const aspekData = @json($aspekData);

  function switchAspek(event, btn) {
    event.preventDefault();
    
    document.querySelectorAll('.aspekContent').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.aspek-tab').forEach(el => el.classList.remove('active'));
    
    const aspekId = btn.dataset.aspekId;
    document.querySelector(`.aspekContent[data-aspek-id="${aspekId}"]`).classList.add('active');
    btn.classList.add('active');
  }

  function updateTotalNilai() {
    let total = 0;
    
    aspekData.forEach(aspek => {
      const bobot = aspek.bobot || 0;
      let nilaiList = [];
      
      aspek.indikators.forEach(ind => {
        const select = document.querySelector(`select[name="nilai[${ind.id}]"]`);
        if (select && select.value) {
          nilaiList.push(parseInt(select.value));
        }
      });
      
      if (nilaiList.length > 0) {
        const avgNilai = nilaiList.reduce((a, b) => a + b, 0) / nilaiList.length;
        total += (avgNilai * bobot) / 100;
      }
    });
    
    document.getElementById('totalNilaiDisplay').textContent = total.toFixed(2);
  }

  document.getElementById('saveDraftBtn').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('validasiForm'));
    
    fetch(`{{ url('/f02') }}/${pengisianId}/save`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
        'Accept': 'application/json',
      }
    })
    .then(r => {
      if (!r.ok) {
        throw new Error(`HTTP ${r.status}: ${r.statusText}`);
      }
      return r.json();
    })
    .then(data => {
      if (data.success) {
        alert('✓ Tersimpan sebagai draft');
        location.reload();
      } else {
        alert('✕ Gagal: ' + (data.errors ? JSON.stringify(data.errors) : data.message));
      }
    })
    .catch(e => alert('✕ Error: ' + e.message));
  });

  document.getElementById('finalizeBtn').addEventListener('click', function() {
    if (confirm('Finalize validasi ini?')) {
      const formData = new FormData(document.getElementById('validasiForm'));
      
      // Debug: log form data
      console.log('Form data being sent:');
      for (let [key, value] of formData.entries()) {
        console.log(key, '=', value);
      }
      
      fetch(`{{ url('/f02') }}/${pengisianId}/finalize`, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
          'Accept': 'application/json',
        }
      })
      .then(r => r.json().then(data => ({status: r.status, data})))
      .then(({status, data}) => {
        if (status === 200 || status === 201) {
          alert('✓ Validasi selesai. Total nilai: ' + data.data.total_nilai);
          window.location.href = '{{ route('f02.index') }}';
        } else if (status === 422) {
          const errors = data.errors || {};
          let errorMsg = 'Validasi gagal:\n';
          Object.keys(errors).forEach(field => {
            errorMsg += `\n${field}: ${errors[field].join(', ')}`;
          });
          alert('✕ ' + errorMsg);
        } else {
          alert('✕ Error ' + status + ': ' + (data.message || 'Unknown error'));
        }
      })
      .catch(e => alert('✕ Error: ' + e.message));
    }
  });

  document.getElementById('rejectBtn').addEventListener('click', function() {
    if (confirm('Kembalikan untuk perbaikan?')) {
      const formData = new FormData(document.getElementById('validasiForm'));
      
      fetch(`{{ url('/f02') }}/${pengisianId}/reject`, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
          'Accept': 'application/json',
        }
      })
      .then(r => {
        if (!r.ok) {
          throw new Error(`HTTP ${r.status}: ${r.statusText}`);
        }
        return r.json();
      })
      .then(data => {
        if (data.success) {
          alert('✓ Dikembalikan untuk perbaikan');
          window.location.href = '{{ route('f02.index') }}';
        } else {
          alert('✕ Gagal: ' + (data.errors ? JSON.stringify(data.errors) : data.message));
        }
      })
      .catch(e => alert('✕ Error: ' + e.message));
    }
  });

  document.addEventListener('DOMContentLoaded', function() {
    updateTotalNilai();
    document.querySelectorAll('.nilaiSelect').forEach(select => {
      select.addEventListener('change', updateTotalNilai);
    });
    
    // Disable form inputs if validation is complete
    const f02Status = '{{ $f02->status }}';
    if (f02Status === 'selesai') {
      // Disable all form inputs
      document.querySelectorAll('select[name^="nilai"], textarea[name^="catatan"]').forEach(input => {
        input.disabled = true;
        input.style.opacity = '0.6';
        input.style.cursor = 'not-allowed';
      });
      // Disable action buttons
      ['saveDraftBtn', 'finalizeBtn', 'rejectBtn'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
          btn.disabled = true;
          btn.style.opacity = '0.5';
          btn.style.cursor = 'not-allowed';
        }
      });
    }
  });

  function showSkorNarasi(selectElement) {
    const indikatorId = selectElement.getAttribute('data-indikator-id');
    const selectedValue = selectElement.value;
    const narasiBox = document.getElementById(`narasi-${indikatorId}`);
    const narasiText = document.getElementById(`narasi-text-${indikatorId}`);

    if (!selectedValue) {
      narasiBox.style.display = 'none';
      return;
    }

    // Fetch F02Skor for this indikator
    fetch(`{{ route('f02.skor.get', ['indikatorId' => ':id']) }}`.replace(':id', indikatorId))
      .then(r => r.json())
      .then(data => {
        if (data.success && data.skor) {
          const scorKey = `skor_${selectedValue}`;
          const narrative = data.skor[scorKey];
          
          if (narrative) {
            narasiText.textContent = narrative;
            narasiBox.style.display = 'block';
          } else {
            narasiBox.style.display = 'none';
          }
        } else {
          narasiBox.style.display = 'none';
        }
      })
      .catch(() => {
        narasiBox.style.display = 'none';
      });
  }
</script>
@endsection
