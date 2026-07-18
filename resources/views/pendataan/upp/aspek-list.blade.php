@extends('layouts.app')

@section('title', 'Pendataan F03')

@section('content')
<div class="f01-container">
    <style>
        .f01-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .f01-header {
            margin-bottom: 40px;
        }

        .f01-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .f01-header p {
            color: #6B7280;
            font-size: 1rem;
        }

        .f01-status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .f01-status-draft {
            background: #FEF3C7;
            color: #92400E;
        }

        .f01-status-submit {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .f01-status-selesai {
            background: #DCFCE7;
            color: #166534;
        }

        .f01-aspek-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 40px;
        }

        .f01-aspek-row {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .f01-aspek-row:hover {
            border-color: #4F46E5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        }

        .f01-aspek-row-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .f01-aspek-info {
            flex: 1;
        }

        .f01-aspek-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .f01-aspek-meta {
            display: flex;
            gap: 30px;
            font-size: 0.95rem;
            color: #6B7280;
        }

        .f01-aspek-progress {
            text-align: right;
            min-width: 200px;
        }

        .f01-progress-bar {
            width: 100%;
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .f01-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4F46E5, #7C3AED);
            transition: width 0.3s ease;
        }

        .f01-progress-text {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }

        .f01-actions {
            display: flex;
            gap: 12px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .f01-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .f01-btn-primary {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
        }

        .f01-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        .f01-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
    </style>

    {{-- Header --}}
    <div class="f01-header">
        <h1>📋 Pendataan Penyelenggaraan Pelayanan Publik Ramah Kelompok Rentan</h1>
        <p>Periode {{ $pengisian->periode->tahun ?? '-' }} - {{ $pengisian->upp->nama ?? '-' }}</p>
    </div>

    {{-- Status Badge --}}
    <div>
        @if($pengisian->status === 'draft')
            <span class="f01-status-badge f01-status-draft">📝 Draft - Sedang Diisi</span>
        @elseif($pengisian->status === 'final')
            <span class="f01-status-badge f01-status-submit">✓ Selesai - Disubmit</span>
        @endif
    </div>

    {{-- Aspek List --}}
    <div class="f01-aspek-list">
        @foreach($aspeks as $aspekData)
            <div class="f01-aspek-row" 
                 onclick="window.location.href='{{ route('pendataan.aspek.detail', ['pengisianId' => $pengisian->id, 'aspekId' => $aspekData['aspek']->id]) }}'">
                <div class="f01-aspek-row-content">
                    <div class="f01-aspek-info">
                        <div class="f01-aspek-name">{{ $aspekData['aspek']->urutan }}. {{ $aspekData['aspek']->nama }}</div>
                        <div class="f01-aspek-meta">
                            <span>📌 Pertanyaan: {{ $aspekData['total_questions'] }}</span>
                            <span>✓ Dijawab: {{ $aspekData['answered_questions'] }}/{{ $aspekData['total_questions'] }}</span>
                        </div>
                    </div>
                    <div class="f01-aspek-progress">
                        <div class="f01-progress-bar">
                            <div class="f01-progress-fill" style="width: {{ $aspekData['progress'] }}%"></div>
                        </div>
                        <div class="f01-progress-text">{{ $aspekData['progress'] }}%</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Actions --}}
    <div class="f01-actions">
        @if($pengisian->status === 'draft')
            <form action="{{ route('pendataan.submit', $pengisian->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin submit pendataan ini? Data tidak dapat diubah setelah disubmit.');">
                @csrf
                <button type="submit" class="f01-btn f01-btn-primary">
                    🔒 Finalisasi & Submit
                </button>
            </form>
        @else
            <button type="button" class="f01-btn f01-btn-primary" disabled>
                ✓ Selesai
            </button>
        @endif
    </div>
</div>
@endsection
