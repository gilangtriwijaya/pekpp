@extends('layouts.app')

@section('title', 'Detail Aspek - ' . $aspek->nama)

@section('content')
<div class="aspek-detail-container">
    <style>
        .aspek-detail-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .aspek-detail-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .aspek-detail-header-info h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #1F2937;
        }

        .aspek-detail-header-info p {
            color: #6B7280;
            font-size: 0.95rem;
            margin: 4px 0;
        }

        .aspek-detail-back-btn {
            padding: 10px 20px;
            background: #E5E7EB;
            color: #374151;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .aspek-detail-back-btn:hover {
            background: #D1D5DB;
        }

        .aspek-detail-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid #E5E7EB;
            padding: 0 0 16px 0;
            margin-bottom: 30px;
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .aspek-detail-tabs::-webkit-scrollbar {
            height: 4px;
        }

        .aspek-detail-tabs::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .aspek-detail-tabs::-webkit-scrollbar-thumb {
            background: #d0d0d0;
            border-radius: 10px;
        }

        .aspek-detail-tab {
            padding: 12px 18px;
            cursor: pointer;
            border: none;
            background: white;
            color: #6B7280;
            font-weight: 500;
            border-radius: 8px;
            white-space: nowrap;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .aspek-detail-tab:hover {
            background: #F3F4F6;
            color: #374151;
        }

        .aspek-detail-tab.active {
            color: #4F46E5;
            border-bottom: 2px solid #4F46E5;
            background: transparent;
        }

        .aspek-detail-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 24px;
        }

        .aspek-detail-tabpanel {
            display: none;
        }

        .aspek-detail-tabpanel.active {
            display: block;
            animation: slideIn 0.2s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .aspek-indikator-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #F3F4F6;
        }

        .aspek-question {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #E5E7EB;
        }

        .aspek-question:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .aspek-question-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .aspek-question-answer {
            background: #F9FAFB;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            padding: 12px;
            color: #374151;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .aspek-question-answer-empty {
            color: #9CA3AF;
            font-style: italic;
        }

        .aspek-bukti-section {
            margin-top: 20px;
            padding: 16px;
            background: #F0F9FF;
            border: 1px solid #90CAF9;
            border-radius: 8px;
        }

        .aspek-bukti-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1976D2;
            margin-bottom: 8px;
        }

        .aspek-bukti-link {
            color: #1976D2;
            text-decoration: none;
            word-break: break-all;
            font-size: 0.9rem;
        }

        .aspek-bukti-link:hover {
            text-decoration: underline;
        }

        .aspek-bukti-link-empty {
            color: #9CA3AF;
            font-style: italic;
            font-size: 0.9rem;
        }

        .aspek-f02-section {
            margin-top: 0;
            margin-bottom: 24px;
            padding: 16px;
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
            border-radius: 8px;
        }

        .aspek-f02-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0C2340;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .aspek-f02-skor {
            font-size: 1.8rem;
            font-weight: 700;
            color: #166534;
            margin-bottom: 12px;
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .aspek-f02-skor-label {
            font-size: 0.85rem;
            color: #6B7280;
            font-weight: 400;
        }

        .aspek-f02-catatan {
            margin-top: 12px;
            padding: 12px;
            background: white;
            border: 1px solid #BAE6FD;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #374151;
            line-height: 1.6;
        }

        .aspek-f02-catatan-label {
            font-weight: 600;
            color: #1976D2;
            margin-bottom: 6px;
            display: block;
        }

        .aspek-f02-empty {
            color: #9CA3AF;
            font-style: italic;
            font-size: 0.9rem;
        }

        .aspek-detail-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid #E5E7EB;
        }

        .aspek-detail-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .aspek-detail-btn-primary {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
        }

        .aspek-detail-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        @media (max-width: 1024px) {
            .aspek-detail-container {
                padding: 30px 20px;
            }

            .aspek-detail-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .aspek-detail-back-btn {
                align-self: flex-end;
            }
        }

        @media (max-width: 768px) {
            .aspek-detail-container {
                padding: 20px 15px;
            }

            .aspek-detail-header-info h1 {
                font-size: 1.3rem;
            }

            .aspek-detail-content {
                padding: 16px;
            }

            .aspek-indikator-title {
                font-size: 1.1rem;
            }

            .aspek-detail-actions {
                flex-direction: column;
            }

            .aspek-detail-btn {
                width: 100%;
            }
        }
    </style>

    {{-- Header --}}
    <div class="aspek-detail-header">
        <div class="aspek-detail-header-info">
            <h1>{{ $aspek->nama }}</h1>
            <p>Periode {{ $pengisian->periode->tahun }} - {{ $pengisian->upp->nama }}</p>
            <p style="color: #10B981; font-weight: 600; margin-top: 8px;">
                @if($pengisian->status === 'completed')
                    ✓ Status: Selesai Divalidasi
                @elseif($pengisian->status === 'submitted')
                    ⏳ Status: Menunggu Validasi F02
                @else
                    📝 Status: Draft
                @endif
            </p>
        </div>
        <button class="aspek-detail-back-btn" onclick="window.location.href = '{{ route('f01.aspek-list', ['pengisian' => $pengisian->id]) }}'">
            ← Kembali ke Daftar Aspek
        </button>
    </div>

    {{-- Indikator Tabs --}}
    @if($indikatorData->count() > 0)
        <div class="aspek-detail-tabs" id="indikatorTabs">
            @foreach($indikatorData as $idx => $data)
                <button type="button" 
                        class="aspek-detail-tab {{ $idx === 0 ? 'active' : '' }}"
                        data-tab-index="{{ $idx }}"
                        onclick="switchTab({{ $idx }})">
                    {{ $data['indikator']->urutan }}. {{ substr($data['indikator']->nama, 0, 40) }}{{ strlen($data['indikator']->nama) > 40 ? '...' : '' }}
                </button>
            @endforeach
        </div>

        {{-- Tab Content --}}
        <div class="aspek-detail-content">
            @foreach($indikatorData as $idx => $data)
                <div class="aspek-detail-tabpanel {{ $idx === 0 ? 'active' : '' }}" data-tab-index="{{ $idx }}">
                    <h2 class="aspek-indikator-title">
                        {{ $data['indikator']->urutan }}. {{ $data['indikator']->nama }}
                    </h2>

                    {{-- F02 Validation Data (moved to top) --}}
                    @if($data['f02_data'])
                        <div class="aspek-f02-section">
                            <div class="aspek-f02-title">📋 Hasil Validasi F02</div>
                            <div class="aspek-f02-skor">
                                <span>{{ $data['f02_data']['nilai'] }}</span>
                                <span class="aspek-f02-skor-label">Poin</span>
                            </div>
                            @if($data['f02_data']['catatan'])
                                <div>
                                    <span class="aspek-f02-catatan-label">Catatan Validator:</span>
                                    <div class="aspek-f02-catatan">{{ $data['f02_data']['catatan'] }}</div>
                                </div>
                            @endif
                        </div>
                    @else
                        @if($isReadOnly)
                            <div class="aspek-f02-section">
                                <div class="aspek-f02-title">📋 Hasil Validasi F02</div>
                                <div class="aspek-f02-empty">Belum ada data validasi</div>
                            </div>
                        @endif
                    @endif

                    {{-- Pertanyaan & Jawaban --}}
                    @if($data['pertanyaan']->count() > 0)
                        <div style="margin-bottom: 24px;">
                            <div style="font-weight: 600; color: #1F2937; margin-bottom: 16px; font-size: 0.95rem;">❓ Pertanyaan & Jawaban</div>
                            @foreach($data['pertanyaan'] as $p)
                                <div class="aspek-question">
                                    <div class="aspek-question-label">
                                        {{ $p['urutan'] }}. {{ $p['label'] }}
                                    </div>
                                    <div class="aspek-question-answer">
                                        @if($p['jawaban'])
                                            {{ is_array($p['jawaban']) ? implode(', ', $p['jawaban']) : $p['jawaban'] }}
                                        @else
                                            <span class="aspek-question-answer-empty">(Belum dijawab)</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Bukti Dukung --}}
                    @if($data['bukti_dukung'])
                        <div class="aspek-bukti-section">
                            <div class="aspek-bukti-label">🔗 Bukti Dukung yang Disertakan</div>
                            <a href="{{ $data['bukti_dukung']->url_bukti }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="aspek-bukti-link">
                               {{ $data['bukti_dukung']->url_bukti }}
                            </a>
                        </div>
                    @else
                        <div class="aspek-bukti-section">
                            <div class="aspek-bukti-label">🔗 Bukti Dukung</div>
                            <div class="aspek-bukti-link-empty">Tidak ada bukti dukung</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="aspek-detail-actions">
            <button type="button" class="aspek-detail-btn aspek-detail-btn-primary" 
                    onclick="window.location.href = '{{ route('f01.aspek-list', ['pengisian' => $pengisian->id]) }}'">
                ← Kembali ke Daftar Aspek
            </button>
        </div>
    @else
        <div style="background: white; padding: 40px; border-radius: 12px; text-align: center; color: #9CA3AF;">
            <div style="font-size: 1.1rem;">Tidak ada indikator untuk aspek ini</div>
        </div>
    @endif
</div>

<script>
function switchTab(index) {
    // Hide all panels
    document.querySelectorAll('.aspek-detail-tabpanel').forEach(panel => {
        panel.classList.remove('active');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.aspek-detail-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected panel
    document.querySelector(`[data-tab-index="${index}"].aspek-detail-tabpanel`).classList.add('active');

    // Add active class to selected tab
    document.querySelector(`[data-tab-index="${index}"].aspek-detail-tab`).classList.add('active');

    // Scroll tab into view
    document.querySelector(`[data-tab-index="${index}"].aspek-detail-tab`).scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'center'
    });
}
</script>
@endsection
