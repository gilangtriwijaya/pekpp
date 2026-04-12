@extends('layouts.app')

@section('title', 'Kelola Skor F02')

@section('content')
<div class="f02-skor-container">
    <style>
        .f02-skor-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .f02-skor-header {
            margin-bottom: 40px;
        }

        .f02-skor-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .f02-skor-header p {
            color: #6B7280;
            font-size: 1rem;
        }

        .f02-aspek-card {
            background: white;
            padding: 20px 24px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            border: 1px solid #E5E7EB;
            transition: all 0.2s;
            cursor: pointer;
        }

        .f02-aspek-card:hover {
            border-color: #D1D5DB;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .f02-aspek-info {
            flex: 1;
        }

        .f02-aspek-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 4px;
        }

        .f02-aspek-meta {
            font-size: 0.9rem;
            color: #6B7280;
        }

        .f02-aspek-arrow {
            font-size: 1.5rem;
            color: #9CA3AF;
            margin-left: 16px;
        }

        .f02-error {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            color: #991B1B;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>

    {{-- Header --}}
    <div class="f02-skor-header">
        <h1>⚙️ Kelola Skor F02</h1>
        <p>Periode: <strong>{{ $periode->tahun ?? 'N/A' }} {{ $periode->nama ?? '' }}</strong></p>
        <p style="margin-top: 8px; color: #6B7280; font-size: 0.9rem;">
            Atur narasi skor (1-5) untuk setiap indikator. Narasi ini akan ditampilkan saat melakukan validasi.
        </p>
    </div>

    {{-- Error Message --}}
    @if(isset($error))
        <div class="f02-error">
            {{ $error }}
        </div>
    @endif

    {{-- Aspek List --}}
    <div class="f02-aspek-list">
        @forelse($aspeks as $aspek)
            <div class="f02-aspek-card" onclick="window.location.href = '{{ route('f02.skor.show', ['aspek' => $aspek->id]) }}'">
                <div class="f02-aspek-info">
                    <div class="f02-aspek-name">{{ $aspek->nama }}</div>
                    <div class="f02-aspek-meta">
                        {{ $aspek->indikator->count() }} indikator
                        @php
                        $withSkor = $aspek->indikator->filter(function($ind) {
                            return \App\Models\F02Skor::where('indikator_id', $ind->id)
                                ->where('periode_id', $aspek->periode_id ?? 1)->exists();
                        })->count();
                        @endphp
                        • {{ $withSkor }} sudah dikonfigurasi
                    </div>
                </div>
                <div class="f02-aspek-arrow">→</div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #6B7280;">
                <p>Tidak ada aspek yang ditemukan</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
