@extends('layouts.app')

@section('title', 'Detail Pendataan UPP')
@section('page_title', 'Detail Pengisian Pendataan')

@section('content')
<style>
  .f01-container { max-width: 1000px; margin: 0 auto; padding: 24px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .f01-header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; }
  .f01-title { font-size: 20px; font-weight: 700; color: #111827; }
  .f01-subtitle { font-size: 14px; color: #6b7280; margin-top: 4px; }
  .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .info-item { display: flex; flex-direction: column; }
  .info-label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
  .info-value { font-size: 14px; font-weight: 500; color: #111827; }
  
  .aspek-section { margin-bottom: 32px; }
  .aspek-title { font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb; }
  
  .pertanyaan-item { margin-bottom: 20px; background: #fafafa; padding: 16px; border-radius: 8px; border: 1px solid #f3f4f6; }
  .pertanyaan-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
  .jawaban-value { font-size: 14px; color: #111827; background: #fff; padding: 12px; border-radius: 6px; border: 1px solid #e5e7eb; min-height: 44px; white-space: pre-wrap; }
  
  .file-link { display: inline-flex; align-items: center; gap: 8px; margin-top: 12px; padding: 8px 12px; background: #eff6ff; color: #2563eb; font-size: 13px; font-weight: 500; border-radius: 6px; text-decoration: none; border: 1px solid #bfdbfe; transition: all 0.2s; }
  .file-link:hover { background: #dbeafe; text-decoration: none; }
  
  .btn-back { display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; background-color: #f3f4f6; color: #374151; font-size: 14px; font-weight: 500; border-radius: 6px; text-decoration: none; border: 1px solid #d1d5db; transition: background 0.2s; }
  .btn-back:hover { background-color: #e5e7eb; color: #111827; text-decoration: none; }
</style>

<div class="f01-container">
    <div class="f01-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 class="f01-title">Detail Hasil Pendataan UPP</h1>
            <p class="f01-subtitle">Melihat isian form pendataan dalam mode baca (Read-only)</p>
        </div>
        <a href="{{ route('admin.pendataan.pengisian.index') }}" class="btn-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="info-box">
        <div class="info-item">
            <span class="info-label">Nama UPP</span>
            <span class="info-value">{{ $pengisian->upp->nama ?? '-' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Periode</span>
            <span class="info-value">{{ $pengisian->periode->tahun ?? '-' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value">
                @if($pengisian->status === 'submitted')
                    <span style="color: #059669; font-weight: 600;">Sudah Disubmit</span>
                @else
                    <span style="color: #6b7280; font-weight: 600;">Masih Draft</span>
                @endif
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Waktu Submit</span>
            <span class="info-value">{{ $pengisian->submitted_at ? $pengisian->submitted_at->format('d M Y H:i:s') : '-' }}</span>
        </div>
    </div>

    <div class="jawaban-container">
        @foreach($aspeks as $aspek)
            <div class="aspek-section">
                <h2 class="aspek-title">{{ $aspek->kode ? $aspek->kode . ' - ' : '' }}{{ $aspek->nama }}</h2>
                
                @php
                    $pertanyaanAspek = $pertanyaans->where('pendataan_aspek_id', $aspek->id);
                @endphp
                
                @if($pertanyaanAspek->isEmpty())
                    <p style="font-size: 13px; color: #6b7280; font-style: italic;">Tidak ada pertanyaan di aspek ini.</p>
                @else
                    @foreach($pertanyaanAspek as $pertanyaan)
                        @php
                            $jawaban = $pengisian->jawaban->where('pendataan_pertanyaan_id', $pertanyaan->id)->first();
                        @endphp
                        <div class="pertanyaan-item">
                            <div class="pertanyaan-label">
                                {{ $pertanyaan->kode ? $pertanyaan->kode . '. ' : '' }}{{ $pertanyaan->label }}
                            </div>
                            
                            <div class="jawaban-value">
                                @if($jawaban && $jawaban->nilai)
                                    {{ $jawaban->nilai }}
                                @else
                                    <span style="color: #9ca3af; font-style: italic;">Tidak ada jawaban</span>
                                @endif
                            </div>
                            
                            @if($jawaban && $jawaban->file_path)
                                <div>
                                    <a href="{{ asset('storage/' . $jawaban->file_path) }}" target="_blank" class="file-link">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        Lihat Dokumen Bukti Dukung
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
