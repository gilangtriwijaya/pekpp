@extends('layouts.app')

@section('title', 'Validasi Indikator F02')

@section('content')
<div class="f02-detail-container">
    <style>
        .f02-detail-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .f02-detail-header {
            margin-bottom: 30px;
        }

        .f02-detail-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .f02-detail-header p {
            color: #6B7280;
            font-size: 0.95rem;
        }

        .f02-nav-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 30px;
            overflow-x: auto;
            padding-bottom: 8px;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .f02-nav-tabs::-webkit-scrollbar {
            height: 4px;
        }

        .f02-nav-tabs::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .f02-nav-tabs::-webkit-scrollbar-thumb {
            background: #d0d0d0;
            border-radius: 10px;
        }

        .f02-nav-tabs::-webkit-scrollbar-thumb:hover {
            background: #b0b0b0;
        }

        .f02-nav-tab {
            padding: 10px 16px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #6B7280;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .f02-nav-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .f02-nav-tab:hover {
            color: #374151;
            background: linear-gradient(135deg, #F3F4F6, #FFFFFF);
            border-color: #D1D5DB;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .f02-nav-tab:hover::before {
            left: 100%;
        }

        .f02-nav-tab.active {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            font-weight: 600;
        }

        .f02-nav-tab.active:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .f02-content-wrapper {
            display: grid;
            grid-template-columns: 65% 35%;
            gap: 24px;
            margin-bottom: 40px;
        }

        .f02-left-section {}

        .f02-right-section {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .f02-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            border: 1px solid #E5E7EB;
        }

        .f02-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #F3F4F6;
        }

        .f02-questions-card {
            background: #FAFAFA;
            border: 1px solid #E5E7EB;
        }

        .f02-question {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #E5E7EB;
        }

        .f02-question:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .f02-question-label {
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .f02-form-input,
        .f02-form-select,
        .f02-form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .f02-form-input:disabled,
        .f02-form-select:disabled,
        .f02-form-textarea:disabled {
            background: #FFFFFF;
            color: #1F2937;
            cursor: default;
            -webkit-text-fill-color: #1F2937;
            opacity: 1;
        }

        .f02-form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .f02-radio-group, .f02-checkbox-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .f02-checkbox-group {
            flex-direction: column;
            gap: 12px;
        }

        .f02-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: default;
        }

        .f02-option input {
            cursor: default;
            accent-color: #3B82F6;
        }

        .f02-option label {
            cursor: default;
            user-select: none;
        }
        
        .f02-lainnya-input {
            width: 100%;
            max-width: 500px;
        }

        .f02-descriptions-card {
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
        }

        .f02-descriptions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .f02-descriptions-item {
            padding: 10px 0;
            color: #1E40AF;
            font-size: 0.9rem;
            line-height: 1.6;
            border-bottom: 1px solid #BAE6FD;
        }

        .f02-descriptions-item:last-child {
            border-bottom: none;
        }

        .f02-descriptions-item strong {
            color: #0C2340;
            font-weight: 700;
        }

        .f02-skor-card {
            background: #F9FAFB;
    border: 1px solid #E5E7EB;
        }

        .f02-form-group {
            margin-bottom: 20px;
        }

        .f02-form-group:last-child {
            margin-bottom: 0;
        }

        .f02-form-label {
            display: block;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .f02-form-label-required {
            color: #EF4444;
        }

        .f02-form-input:focus,
        .f02-form-select:focus,
        .f02-form-textarea:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .f02-skor-option-label {
            font-size: 0.85rem;
            color: #6B7280;
            display: block;
            margin-top: 4px;
        }

        .f02-persyaratan-card {
            background: #FFFBEB;
            border: 1px solid #FCD34D;
        }

        .f02-persyaratan-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .f02-persyaratan-item {
            padding: 10px 0;
            color: #92400E;
            font-size: 0.9rem;
            line-height: 1.6;
            border-bottom: 1px solid #FCD34D;
        }

        .f02-persyaratan-item:last-child {
            border-bottom: none;
        }

        .f02-bukti-link {
            color: #4F46E5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .f02-bukti-link:hover {
            color: #7C3AED;
            text-decoration: underline;
        }

        .f02-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .f02-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .f02-btn-secondary {
            background: white;
            color: #4F46E5;
            border: 2px solid #4F46E5;
        }

        .f02-btn-secondary:hover {
            background: #EEF2FF;
        }

        .f02-btn-primary {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
        }

        .f02-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        .f02-validation-message {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: none;
        }

        .f02-validation-message.error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
            display: block;
        }

        .f02-validation-message.success {
            background: #DCFCE7;
            color: #166534;
            border: 1px solid #BBF7D0;
            display: block;
            animation: slideIn 0.3s ease-in-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .f02-detail-container {
                max-width: 100%;
                padding: 35px 25px;
            }

            .f02-content-wrapper {
                grid-template-columns: 1fr;
            }

            .f02-right-section {
                position: static;
                top: auto;
            }
        }

        @media (max-width: 768px) {
            .f02-detail-container {
                padding: 25px 18px;
            }

            .f02-detail-header h1 {
                font-size: 1.3rem;
            }

            .f02-nav-tabs {
                gap: 6px;
            }

            .f02-nav-tab {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .f02-card {
                padding: 18px;
            }

            .f02-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
                flex: 1;
                min-width: 120px;
            }
        }

        @media (max-width: 480px) {
            .f02-detail-container {
                padding: 20px 15px;
            }

            .f02-detail-header h1 {
                font-size: 1.1rem;
            }

            .f02-card {
                padding: 16px;
            }

            .f02-question {
                margin-bottom: 16px;
                padding-bottom: 16px;
            }

            .f02-btn {
                padding: 10px 16px;
                font-size: 0.85rem;
            }
        }
    </style>

    {{-- Header --}}
    <div class="f02-detail-header">
        <a href="{{ route('f02.aspek-list', ['validasi' => $validasi->id]) }}" 
           style="color: #6B7280; text-decoration: none; margin-bottom: 12px; display: inline-block; font-size: 0.95rem;">
            ← Kembali ke Daftar Aspek
        </a>
        <h1>{{ $aspek->nama }}</h1>
        <p>Periode {{ $validasi->periode->tahun }} - {{ $validasi->upp->nama }} | {{ $indikator->nama }}</p>
    </div>

    {{-- Indikator Tabs --}}
    <div class="f02-nav-tabs" id="indikatorTabs">
        @foreach($indikators as $ind)
            @php
                $status = isset($indikatorStatuses) && isset($indikatorStatuses[$ind->id]) ? $indikatorStatuses[$ind->id] : null;
                $tabBadge = '';
                if ($status && isset($isResubmit) && $isResubmit) {
                    if ($status['is_changed']) {
                        $tabBadge = ' <span style="display:inline-block; width:8px; height:8px; background-color:#F59E0B; border-radius:50%; margin-left:4px;" title="Berubah"></span>';
                    } elseif ($status['is_carried_over']) {
                        $tabBadge = ' <span style="display:inline-block; width:8px; height:8px; background-color:#3B82F6; border-radius:50%; margin-left:4px;" title="Carry-over"></span>';
                    } else {
                        $tabBadge = ' <span style="display:inline-block; width:8px; height:8px; background-color:#9CA3AF; border-radius:50%; margin-left:4px;" title="Tidak ada skor sebelumnya"></span>';
                    }
                }
            @endphp
            <button type="button" 
                    class="f02-nav-tab {{ $ind->id === $indikator->id ? 'active' : '' }}"
                    data-indikator-id="{{ $ind->id }}"
                    data-aspek-id="{{ $aspek->id }}"
                    onclick="switchIndikator({{ $ind->id }}, {{ $aspek->id }}, {{ $validasi->id }})">
                {{ $ind->urutan }}. {{ substr($ind->nama, 0, 30) }}{{ strlen($ind->nama) > 30 ? '...' : '' }}{!! $tabBadge !!}
            </button>
        @endforeach
    </div>

    {{-- Banner Status --}}
    @if(isset($isResubmit) && $isResubmit && isset($indikatorStatuses) && isset($indikatorStatuses[$indikator->id]))
        @php $currStatus = $indikatorStatuses[$indikator->id]; @endphp
        @if($currStatus['is_changed'])
            <div style="background: #FFFBEB; border: 1px solid #FCD34D; color: #B45309; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.5rem;">⚠️</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Perhatian: Indikator Diubah</strong>
                    UPP mengubah jawaban/bukti dukung pada indikator ini. Perlu validasi ulang.
                </div>
            </div>
        @elseif($currStatus['is_carried_over'])
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1D4ED8; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.5rem;">ℹ️</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Informasi: Carry-over</strong>
                    Jawaban tidak berubah. Skor disalin otomatis dari periode validasi sebelumnya.
                </div>
            </div>
        @else
            <div style="background: #F3F4F6; border: 1px solid #D1D5DB; color: #4B5563; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.5rem;">ℹ️</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Informasi</strong>
                    Indikator ini tidak dilaporkan berubah, namun tidak ditemukan skor dari validasi sebelumnya. Silakan isi skor secara manual.
                </div>
            </div>
        @endif
    @endif

    {{-- Content Area --}}
    <div class="f02-content-wrapper">
        {{-- Left Column: Pertanyaan --}}
        <div class="f02-left-section">
            {{-- Questions Card --}}
            <div class="f02-card f02-questions-card">
                <div class="f02-card-title">❓ Pertanyaan & Jawaban</div>
                
                @if($pertanyaan->isEmpty())
                    <div style="color: #9CA3AF; text-align: center; padding: 20px 0;">
                        Tidak ada pertanyaan untuk indikator ini
                    </div>
                @else
                    @foreach($pertanyaan as $p)
                        <div class="f02-question">
                            <div class="f02-question-label">{{ $loop->index + 1 }}. {{ $p->label }}</div>
                            
                            {{-- Get saved answer--}}
                            @php
                            $jawaban = $p->jawaban->first();
                            $savedAnswer = $jawaban ? $jawaban->nilai : '';
                            @endphp

                            {{-- Render Question Input --}}
                            @switch($p->tipe())
                                @case('text')
                                    <input type="text" 
                                           class="f02-form-input"
                                           value="{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}"
                                           disabled
                                           placeholder="Belum dijawab">
                                    @break

                                @case('textarea')
                                    <textarea class="f02-form-textarea"
                                              disabled
                                              placeholder="Belum dijawab">{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}</textarea>
                                    @break

                                @case('yesno')
                                    <div class="f02-radio-group">
                                        <label class="f02-option">
                                            <input type="radio" 
                                                   value="Ya"
                                                   {{ $savedAnswer === 'Ya' || $savedAnswer === '"Ya"' ? 'checked' : '' }}
                                                   disabled>
                                            <span>Ya</span>
                                        </label>
                                        <label class="f02-option">
                                            <input type="radio" 
                                                   value="Tidak"
                                                   {{ $savedAnswer === 'Tidak' || $savedAnswer === '"Tidak"' ? 'checked' : '' }}
                                                   disabled>
                                            <span>Tidak</span>
                                        </label>
                                    </div>
                                    @break

                                @case('select')
                                    <select class="f02-form-select" disabled>
                                        <option value="">-- Belum dijawab --</option>
                                        @php
                                        $optionsRaw = $p->opsi_jawaban ?? [];
                                        $options = is_array($optionsRaw) ? $optionsRaw : json_decode($optionsRaw, true) ?? [];
                                        @endphp
                                        @foreach($options as $option)
                                            <option value="{{ $option['value'] ?? $option }}"
                                                    {{ $savedAnswer === ($option['value'] ?? $option) || $savedAnswer === json_encode($option['value'] ?? $option) ? 'selected' : '' }}>
                                                {{ $option['label'] ?? $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('number')
                                    <input type="number" 
                                           class="f02-form-input"
                                           value="{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}"
                                           disabled
                                           placeholder="Belum dijawab">
                                    @break

                                @case('checkbox')
                                    <div class="f02-checkbox-group">
                                        @php
                                        $optionsRaw = $p->opsi_jawaban ?? [];
                                        $options = is_array($optionsRaw) ? $optionsRaw : json_decode($optionsRaw, true) ?? [];
                                        $savedValues = is_array($savedAnswer) ? $savedAnswer : (json_decode($savedAnswer, true) ?? []);
                                        $hasLainnya = isset($savedValues['lainnya']) && !empty($savedValues['lainnya']);
                                        $lainnyaValue = $savedValues['lainnya'] ?? '';
                                        @endphp
                                        @foreach($options as $option)
                                            <label class="f02-option">
                                                <input type="checkbox" 
                                                       value="{{ $option['value'] ?? $option }}"
                                                       {{ in_array($option['value'] ?? $option, $savedValues) ? 'checked' : '' }}
                                                       disabled>
                                                <span>{{ $option['label'] ?? $option }}</span>
                                            </label>
                                        @endforeach
                                        
                                        @if($p->allow_lainnya)
                                        <label class="f02-option">
                                            <input type="checkbox" 
                                                   value="__lainnya__"
                                                   {{ $hasLainnya ? 'checked' : '' }}
                                                   disabled>
                                            <span>Lainnya</span>
                                        </label>
                                        
                                        <div style="margin-left: 28px; margin-top: 8px; display: {{ $hasLainnya ? 'block' : 'none' }};">
                                            <input type="text" 
                                                   class="f02-form-input f02-lainnya-input"
                                                   value="{{ $lainnyaValue }}"
                                                   disabled>
                                        </div>
                                        @endif
                                    </div>
                                    @break

                                @default
                                    <input type="text" 
                                           class="f02-form-input"
                                           value="{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}"
                                           disabled
                                           placeholder="Belum dijawab">
                                    @break
                            @endswitch
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Right Column: Skor & Catatan --}}
        <div class="f02-right-section">
            <form id="f02DetailForm" method="POST" 
                  action="{{ route('f02.save-indikator', ['validasi' => $validasi->id, 'indikator' => $indikator->id]) }}">
                @csrf

                {{-- Validation Message --}}
                <div class="f02-validation-message" id="validationMessage"></div>

                {{-- Skor Card --}}
                <div class="f02-card f02-skor-card">
                    <div class="f02-card-title">⭐ Skor Validasi</div>

                    <div class="f02-form-group">
                        <label class="f02-form-label">
                            Nilai Indikator <span class="f02-form-label-required">*</span>
                        </label>
                        <select name="nilai" class="f02-form-select" id="skorSelect" required>
                            <option value="">-- Pilih Skor --</option>
                            @foreach($skors as $skor => $narrative)
                                <option value="{{ $skor }}" 
                                        @if($indikatorValidasi?->nilai == $skor) selected @endif>
                                    {{ $skor }} - {{ Str::limit($narrative, 60, '...') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Full Narrative Display --}}
                    <div id="skorNarrativeContainer" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #E5E7EB;">
                        <div style="font-size: 0.85rem; color: #6B7280; margin-bottom: 8px;">Narasi Lengkap:</div>
                        <div id="skorNarrative" style="padding: 12px; background: white; border: 1px solid #D1D5DB; 
                             border-radius: 6px; font-size: 0.9rem; color: #374151; line-height: 1.6; min-height: 60px;">
                            Pilih skor untuk melihat narasi lengkap
                        </div>
                    </div>
                </div>

                {{-- Catatan Card --}}
                <div class="f02-card">
                    <div class="f02-card-title">📝 Catatan Validasi</div>

                    <div class="f02-form-group">
                        <label class="f02-form-label">Tambahkan catatan (opsional)</label>
                        <textarea name="catatan" 
                                  class="f02-form-textarea"
                                  placeholder="Masukkan catatan validasi untuk indikator ini...">{{ $indikatorValidasi?->catatan ?? '' }}</textarea>
                    </div>
                </div>

                {{-- Persyaratan Bukti Dukung --}}
                @if($indikator->bukti_dukung)
                    <div class="f02-card f02-persyaratan-card">
                        <div class="f02-card-title">🔗 Persyaratan Bukti Dukung</div>
                        <ul class="f02-persyaratan-list">
                            @foreach(array_filter(array_map('trim', explode("\n", $indikator->bukti_dukung))) as $requirement)
                                <li class="f02-persyaratan-item">{{ $requirement }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Bukti Dukung Links --}}
                @if($buktiDukung && $buktiDukung->count() > 0)
                    <div class="f02-card" style="background: #F0F9FF; border: 1px solid #BAE6FD;">
                        <div class="f02-card-title">📎 Bukti Dukung dari UPP</div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($buktiDukung as $bukti)
                                @if($bukti->url_bukti)
                                    <div style="padding: 12px; background: white; border: 1px solid #BAE6FD; border-radius: 8px;">
                                        <a href="{{ $bukti->url_bukti }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="f02-bukti-link"
                                           style="word-break: break-all; font-size: 0.85rem; display: block;">
                                           🔗 {{ Str::limit($bukti->url_bukti, 80, '...') }}
                                        </a>
                                        <div style="font-size: 0.75rem; color: #6B7280; margin-top: 4px;">Indikator: {{ $bukti->indikator->nama ?? '-' }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="f02-card" style="background: #FEF3C7; border: 1px solid #FCD34D;">
                        <div class="f02-card-title">📎 Bukti Dukung dari UPP</div>
                        <div style="color: #92400E; font-size: 0.9rem; text-align: center; padding: 20px 0;">
                            Tidak ada bukti dukung untuk indikator ini
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="f02-actions">
                    <button type="button" class="f02-btn f02-btn-secondary" onclick="goBack()">
                        ← Kembali
                    </button>
                    <button type="submit" class="f02-btn f02-btn-primary" id="saveBtnModal">
                        🔒 Simpan Skor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Score narratives mapping
const skorNarratives = {
    @foreach($skors as $skor => $narrative)
        {{ $skor }}: @json($narrative),
    @endforeach
};

document.addEventListener('DOMContentLoaded', function() {
    const skorSelect = document.getElementById('skorSelect');
    
    // Initialize with selected skor narrative
    if (skorSelect.value) {
        updateSkorNarrative(skorSelect.value);
    }

    // Update narrative when user changes skor
    skorSelect.addEventListener('change', function() {
        updateSkorNarrative(this.value);
    });

    // Form submission - prevent default form post
    document.getElementById('f02DetailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('Form submitted');
        
        // Validation
        const nilai = skorSelect.value;
        if (!nilai) {
            showValidationMessage('⚠️ Skor harus dipilih sebelum menyimpan', 'error');
            return;
        }

        // Get catatan
        const catatan = document.querySelector('[name="catatan"]').value;

        console.log('Submitting with nilai:', nilai, 'catatan:', catatan);
        
        // Auto-save data via AJAX
        autoSaveF02({
            nilai: nilai,
            catatan: catatan
        });
    });
});

function updateSkorNarrative(skor) {
    const narrativeDiv = document.getElementById('skorNarrative');
    if (skorNarratives[skor]) {
        narrativeDiv.textContent = skorNarratives[skor];
    }
}

function autoSaveF02(data) {
    const validasiId = '{{ $validasi->id }}';
    const indikatorId = '{{ $indikator->id }}';
    const url = `{{ route('f02.auto-save', ['validasi' => 'ID', 'indikator' => 'INDID']) }}`
        .replace('ID', validasiId)
        .replace('INDID', indikatorId);

    console.log('Saving F02 data to:', url);
    console.log('Data:', data);
    
    // Disable save button while saving
    const saveBtn = document.getElementById('saveBtnModal');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = '⏳ Menyimpan...';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('Response result:', result);
        // Re-enable save button
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
        
        if (result.success) {
            showValidationMessage('✓ Skor dan catatan berhasil disimpan', 'success');
        } else {
            showValidationMessage(result.message || 'Terjadi kesalahan saat menyimpan', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving:', error);
        // Re-enable save button
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
        showValidationMessage('❌ Kesalahan koneksi: ' + error.message, 'error');
    });
}

function showValidationMessage(message, type = 'success') {
    const msgDiv = document.getElementById('validationMessage');
    msgDiv.textContent = message;
    msgDiv.className = `f02-validation-message ${type}`;
    
    // Scroll to notification
    msgDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Auto-hide success message after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            msgDiv.className = 'f02-validation-message';
            msgDiv.textContent = '';
        }, 5000);
    }
    
    console.log('Notification shown:', message, type);
}

function switchIndikator(indikatorId, aspekId, validasiId) {
    const url = `{{ route('f02.validasi-detail', ['validasi' => 'ID', 'aspek' => 'ASPK']) }}`
        .replace('ID', validasiId)
        .replace('ASPK', aspekId)
        + `?indikator=${indikatorId}`;
    window.location.href = url;
}

function goBack() {
    window.history.back();
}

// Scroll to active tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const tabsContainer = document.getElementById('indikatorTabs');
    const activeTab = tabsContainer?.querySelector('.f02-nav-tab.active');
    
    if (activeTab && tabsContainer) {
        // Get the active tab's position and container dimensions
        const tabRect = activeTab.getBoundingClientRect();
        const containerRect = tabsContainer.getBoundingClientRect();
        
        // Calculate scroll position to center the active tab
        const tabCenterPosition = activeTab.offsetLeft + (activeTab.offsetWidth / 2);
        const containerCenterPosition = tabsContainer.clientWidth / 2;
        const scrollPosition = tabCenterPosition - containerCenterPosition;
        
        // Smooth scroll to center the active tab
        tabsContainer.scrollTo({
            left: scrollPosition,
            behavior: 'smooth'
        });
        
        console.log('Scrolled to active indicator tab');
    }
});
</script>
@endsection
