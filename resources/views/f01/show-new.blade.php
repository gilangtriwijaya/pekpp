@extends('layouts.app')

@section('title', 'Detail Pengisian F01')

@section('content')
<div class="f01-detail-container">
    <style>
        .f01-detail-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        .f01-detail-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .f01-detail-header-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #1F2937;
        }

        .f01-detail-header-info p {
            color: #6B7280;
            font-size: 0.95rem;
            margin: 4px 0;
        }

        .f01-back-btn {
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

        .f01-back-btn:hover {
            background: #D1D5DB;
        }

        .f01-content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* ====== Modern Indikator Tabs Redesign ====== */
        .f01-indikator-tabs {
            display: flex;
            gap: 8px;
            border-bottom: none;
            padding: 16px 0;
            background: transparent;
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .f01-indikator-tabs::-webkit-scrollbar {
            height: 4px;
        }

        .f01-indikator-tabs::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .f01-indikator-tabs::-webkit-scrollbar-thumb {
            background: #d0d0d0;
            border-radius: 10px;
        }

        .f01-indikator-tabs::-webkit-scrollbar-thumb:hover {
            background: #b0b0b0;
        }

        .f01-indikator-tab {
            padding: 12px 18px;
            cursor: pointer;
            border: none;
            background: white;
            color: #6B7280;
            font-weight: 500;
            border-radius: 8px;
            white-space: nowrap;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
            border: 1px solid #E5E7EB;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .f01-indikator-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .f01-indikator-tab:hover {
            color: #374151;
            background: linear-gradient(135deg, #F3F4F6, #FFFFFF);
            border-color: #D1D5DB;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .f01-indikator-tab:hover::before {
            left: 100%;
        }

        /* Active Indikator Tab - Blue Indicator */
        .f01-indikator-tab.border-b-2 {
            background: linear-gradient(135deg, #0EA5E9, #06B6D4);
            color: white !important;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
            font-weight: 600;
            border-bottom: 3px solid #0284C7 !important;
            position: relative;
        }

        .f01-indikator-tab.border-b-2::after {
            content: '';
            position: absolute;
            top: -4px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0284C7, #0EA5E9, #06B6D4);
            border-radius: 3px;
        }

        .f01-indikator-tab.border-b-2:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(14, 165, 233, 0.5);
            background: linear-gradient(135deg, #0096EF, #0891B2);
        }

        .f01-indikator-tab-btn.border-b-2 {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white !important;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            font-weight: 600;
        }

        .f01-indikator-tab-btn.border-b-2:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .f01-tab-content {
            display: none;
            padding: 24px 0;
        }

        .f01-tab-content.active {
            display: block;
            animation: fadeInTab 0.3s ease;
        }

        @keyframes fadeInTab {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .f01-tab-content > div {
            display: grid;
            gap: 24px;
        }

        .f01-question {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #E5E7EB;
            animation: slideInQuestion 0.3s ease;
        }

        @keyframes slideInQuestion {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .f01-question:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .f01-question-label {
            font-size: 1rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .f01-question-required {
            color: #EF4444;
            margin-left: 4px;
        }

        .f01-form-group {
            margin-bottom: 12px;
        }

        .f01-form-input, .f01-form-select, .f01-form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .f01-form-input:focus, .f01-form-select:focus, .f01-form-textarea:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .f01-form-input:disabled, .f01-form-select:disabled, .f01-form-textarea:disabled {
            background: #F9FAFB;
            color: #6B7280;
            cursor: not-allowed;
        }

        .f01-form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .f01-radio-group, .f01-checkbox-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .f01-checkbox-group {
            flex-direction: column;
            gap: 12px;
        }

        .f01-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .f01-option input {
            cursor: pointer;
        }

        .f01-option label {
            cursor: pointer;
            user-select: none;
        }

        .f01-lainnya-input-wrapper {
            width: 100%;
        }

        .f01-lainnya-input {
            width: 100%;
            max-width: 500px;
            padding: 8px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .f01-lainnya-input:focus {
            outline: none;
            border-color: #8B5CF6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .f01-bukti-section {
            margin-top: 16px;
            padding: 16px;
            background: #F0F9FF;
            border: 1px solid #90CAF9;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .f01-bukti-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1976D2;
            margin-bottom: 8px;
        }

        .f01-bukti-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #90CAF9;
            border-radius: 6px;
            font-size: 0.9rem;
            box-sizing: border-box;
        }

        .f01-bukti-input:focus {
            outline: none;
            border-color: #1976D2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }

        .f01-bukti-current {
            margin-top: 8px;
            padding: 8px;
            background: white;
            border: 1px solid #90CAF9;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .f01-bukti-link {
            color: #1976D2;
            text-decoration: none;
            word-break: break-all;
        }

        .f01-bukti-link:hover {
            text-decoration: underline;
        }

        .f01-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid #E5E7EB;
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

        .f01-btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        .f01-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .f01-autosave-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 0.9rem;
            color: #6B7280;
            display: none;
            align-items: center;
            gap: 8px;
            z-index: 100;
            animation: slideIn 0.3s ease;
        }

        .f01-autosave-indicator.saving {
            background: #FEF3C7;
            color: #92400E;
        }

        .f01-autosave-indicator.saved {
            background: #DCFCE7;
            color: #166534;
        }

        .f01-autosave-indicator.error {
            background: #FEE2E2;
            color: #991B1B;
        }

        .f01-read-only-notice {
            background: #DBEAFE;
            color: #1E40AF;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        @keyframes slideIn {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ====== 2-Column Layout for Tab Content ====== */
        .f01-tab-content-wrapper {
            display: flex;
            gap: 24px;
            margin-top: 20px;
            min-height: auto;
        }

        .f01-left-section {
            flex: 0 0 65%;
        }

        .f01-right-section {
            flex: 0 0 35%;
        }

        .f01-questions-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .f01-descriptions-card {
            background: linear-gradient(135deg, #EFF6FF 0%, #E0E7FF 100%);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #4F46E5;
            position: sticky;
            top: 20px;
        }

        .f01-description-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(79, 70, 229, 0.2);
        }

        .f01-description-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .f01-description-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1E3A8A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .f01-description-content {
            font-size: 0.9rem;
            line-height: 1.5;
            color: #1E3A8A;
        }

        @media (max-width: 1024px) {
            .f01-tab-content-wrapper {
                flex-direction: column;
            }

            .f01-left-section {
                flex: 0 0 100%;
            }

            .f01-right-section {
                flex: 0 0 100%;
            }

            .f01-descriptions-card {
                position: static;
            }
        }
    </style>

    <style>
        @media (max-width: 768px) {
            .f01-detail-container {
                padding: 20px 15px;
            }

            .f01-detail-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .f01-back-btn {
                align-self: flex-end;
            }

            .f01-indikator-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .f01-actions {
                flex-direction: column;
            }

            .f01-btn {
                width: 100%;
            }

            /* Mobile 2-column layout fix */
            .f01-tab-content-wrapper {
                flex-direction: column !important;
            }

            .f01-left-section {
                flex: 0 0 100% !important;
                width: 100% !important;
            }

            .f01-right-section {
                flex: 0 0 100% !important;
                width: 100% !important;
            }

            .f01-descriptions-card {
                position: static !important;
                top: 0 !important;
            }
        }
    </style>

    {{-- Header with Back Button --}}
    <div class="f01-detail-header">
        <div class="f01-detail-header-info">
            <h1>📋 Detail Penilaian F01{{ $selectedAspek ? ' - ' . $selectedAspek->nama : '' }}</h1>
            <p>Periode {{ $pengisian->periode->tahun }} - {{ $pengisian->upp->nama }} @if($selectedAspek) | Aspek: <strong>{{ $selectedAspek->nama }}</strong> @endif</p>
            @if($pengisian->catatan_umum)
                <p style="margin-top:8px; color:#2563EB; font-weight:600;">📝 Catatan UPP: {{ $pengisian->catatan_umum }}</p>
            @endif
        </div>
        <button class="f01-back-btn" onclick="window.location.href = '{{ route('f01.aspek-list', ['pengisian' => $pengisian->id]) }}'">
            ← Kembali ke Daftar Aspek
        </button>
    </div>

    {{-- Read-only Notice --}}
    @if($isReadOnly)
        <div class="f01-read-only-notice">
            ⏳ Status: <strong>{{ ucfirst($pengisian->status) }}</strong> - Pengisian tidak bisa diedit dalam status ini
        </div>
    @endif

    {{-- Indikators Tabs --}}
    @if($selectedAspek)
        {{-- Indikator Tabs --}}
        <div class="f01-indikator-tabs" id="indikatorTabs">
            @foreach($selectedAspek->indikator as $idx => $indikator)
                <button class="f01-indikator-tab {{ $idx === 0 ? 'border-b-2 border-purple-600' : '' }}"
                        data-indikator-idx="{{ $idx }}">
                    {{ $indikator->urutan }}. {{ substr($indikator->nama, 0, 40) }}{{ strlen($indikator->nama) > 40 ? '...' : '' }}
                </button>
            @endforeach
        </div>

        {{-- Indikator Tab Contents - Main Grid Layout --}}
        @foreach($selectedAspek->indikator as $idx => $indikator)
            <div class="f01-tab-content-main {{ $idx === 0 ? 'block' : 'hidden' }}" data-indikator-idx="{{ $idx }}">
                <div class="f01-tab-content-wrapper">
                    {{-- LEFT COLUMN (65%) - Form --}}
                    <div class="f01-left-section">
                        <div class="f01-questions-card">
                            {{-- Indikator Title --}}
                            <h2 class="text-base font-bold text-gray-800 mb-4">
                                {{ $indikator->urutan }}. {{ $indikator->nama }}
                            </h2>

                            {{-- Questions Form --}}
                            <form id="form-indikator-{{ $indikator->id }}" class="f01-questions-form" data-indikator-id="{{ $indikator->id }}">
                                @csrf
                                @foreach($indikator->pertanyaan as $pertanyaan)
                                    <div class="f01-question" 
                                         data-question-id="{{ $pertanyaan->id }}"
                                         data-parent-question="{{ $pertanyaan->parent_pertanyaan_id ?? '' }}"
                                         data-show-when="{{ $pertanyaan->show_when ?? 'keduanya' }}"
                                         data-skip-if-answer="{{ $pertanyaan->skip_if_answer ?? '' }}"
                                         style="{{ $pertanyaan->parent_pertanyaan_id ? 'display: none;' : '' }}">
                                        <label class="f01-question-label">
                                            {{ $pertanyaan->urutan }}. {{ $pertanyaan->label }}
                                            @if($pertanyaan->aktif)
                                                <span class="f01-question-required">*</span>
                                            @endif
                                        </label>

                                        {{-- Get saved answer--}}
                                        @php
                                        $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $pertanyaan->id)->first();
                                        $savedAnswer = $jawaban ? $jawaban->nilai : '';
                                        @endphp

                                        {{-- Render Question Input --}}
                                        @switch($pertanyaan->tipe())
                                            @case('text')
                                                <input type="text" 
                                                       class="f01-form-input"
                                                       name="jawaban_{{ $pertanyaan->id }}"
                                                       value="{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}"
                                                       {{ $isReadOnly ? 'disabled' : '' }}
                                                       placeholder="Masukkan jawaban">
                                                @break

                                            @case('textarea')
                                                <textarea class="f01-form-textarea"
                                                          name="jawaban_{{ $pertanyaan->id }}"
                                                          {{ $isReadOnly ? 'disabled' : '' }}
                                                          placeholder="Masukkan jawaban">{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}</textarea>
                                                @break

                                            @case('yesno')
                                                <div class="f01-radio-group">
                                                    <label class="f01-option">
                                                        <input type="radio" 
                                                               name="jawaban_{{ $pertanyaan->id }}" 
                                                               value="Ya"
                                                               {{ $savedAnswer === 'Ya' || $savedAnswer === '"Ya"' ? 'checked' : '' }}
                                                               {{ $isReadOnly ? 'disabled' : '' }}>
                                                        <span>Ya</span>
                                                    </label>
                                                    <label class="f01-option">
                                                        <input type="radio" 
                                                               name="jawaban_{{ $pertanyaan->id }}" 
                                                               value="Tidak"
                                                               {{ $savedAnswer === 'Tidak' || $savedAnswer === '"Tidak"' ? 'checked' : '' }}
                                                               {{ $isReadOnly ? 'disabled' : '' }}>
                                                        <span>Tidak</span>
                                                    </label>
                                                </div>
                                                @break

                                            @case('select')
                                                <select class="f01-form-select"
                                                        name="jawaban_{{ $pertanyaan->id }}"
                                                        {{ $isReadOnly ? 'disabled' : '' }}>
                                                    <option value="">-- Pilih --</option>
                                                    @php
                                                    $optionsRaw = $pertanyaan->opsi_jawaban ?? [];
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
                                                       class="f01-form-input"
                                                       name="jawaban_{{ $pertanyaan->id }}"
                                                       value="{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}"
                                                       min="{{ $pertanyaan->min ?? 0 }}"
                                                       max="{{ $pertanyaan->max ?? 999999 }}"
                                                       {{ $isReadOnly ? 'disabled' : '' }}
                                                       placeholder="Masukkan angka">
                                                @break

                                            @case('checkbox')
                                                <div class="f01-checkbox-group">
                                                    @php
                                                    $optionsRaw = $pertanyaan->opsi_jawaban ?? [];
                                                    $options = is_array($optionsRaw) ? $optionsRaw : json_decode($optionsRaw, true) ?? [];
                                                    $savedValues = is_array($savedAnswer) ? $savedAnswer : (json_decode($savedAnswer, true) ?? []);
                                                    $hasLainnya = isset($savedValues['lainnya']) && !empty($savedValues['lainnya']);
                                                    $lainnyaValue = $savedValues['lainnya'] ?? '';
                                                    @endphp
                                                    @foreach($options as $option)
                                                        <label class="f01-option">
                                                            <input type="checkbox" 
                                                                   name="jawaban_{{ $pertanyaan->id }}[]"
                                                                   value="{{ $option['value'] ?? $option }}"
                                                                   {{ in_array($option['value'] ?? $option, $savedValues) ? 'checked' : '' }}
                                                                   {{ $isReadOnly ? 'disabled' : '' }}>
                                                            <span>{{ $option['label'] ?? $option }}</span>
                                                        </label>
                                                    @endforeach
                                                    
                                                    {{-- Opsi "Lainnya" (conditional: only if enabled) --}}
                                                    @if($pertanyaan->allow_lainnya)
                                                    <label class="f01-option">
                                                        <input type="checkbox" 
                                                               name="jawaban_{{ $pertanyaan->id }}[]"
                                                               id="lainnya_{{ $pertanyaan->id }}"
                                                               class="f01-lainnya-checkbox"
                                                               data-question-id="{{ $pertanyaan->id }}"
                                                               value="__lainnya__"
                                                               {{ $hasLainnya ? 'checked' : '' }}
                                                               {{ $isReadOnly ? 'disabled' : '' }}>
                                                        <span>Lainnya</span>
                                                    </label>
                                                    
                                                    {{-- Input untuk opsi "Lainnya" --}}
                                                    <div id="lainnya_input_{{ $pertanyaan->id }}" 
                                                         class="f01-lainnya-input-wrapper" 
                                                         style="margin-left: 28px; margin-top: 8px; display: {{ $hasLainnya ? 'block' : 'none' }};">
                                                        <input type="text" 
                                                               name="jawaban_lainnya_{{ $pertanyaan->id }}"
                                                               class="f01-form-input f01-lainnya-input"
                                                               placeholder="Tuliskan jawaban lainnya..."
                                                               value="{{ $lainnyaValue }}"
                                                               {{ $isReadOnly ? 'disabled' : '' }}>
                                                        <small style="color: #888; display: block; margin-top: 4px;">Silakan tuliskan pilihan Anda yang lain</small>
                                                    </div>
                                                    @endif
                                                </div>
                                                @break

                                            @default
                                                <input type="text" 
                                                       class="f01-form-input"
                                                       name="jawaban_{{ $pertanyaan->id }}"
                                                       value="{{ is_array($savedAnswer) ? json_encode($savedAnswer) : $savedAnswer }}"
                                                       {{ $isReadOnly ? 'disabled' : '' }}
                                                       placeholder="Masukkan jawaban">
                                                @break
                                        @endswitch
                                    </div>
                                @endforeach

                                {{-- Bukti Dukung URL Section --}}
                                <div class="f01-bukti-section">
                                    <div class="f01-bukti-label">🔗 Link Bukti Dukung </div>
                                        <p class="bukti-desc">
                                            Bukti dukung indikator ini disatukan dalam satu folder Google Drive dan 
                                            URLnya dilampirkan pada form ini.
                                        </p>
                                @php
                                    $buktiDukung = $pengisian->buktiDukung()
                                        ->where('indikator_id', $indikator->id)
                                        ->first();
                                    $savedUrl = $buktiDukung ? $buktiDukung->url_bukti : '';
                                    @endphp
                                    <input type="url" 
                                           class="f01-bukti-input"
                                           name="bukti_dukung_url_{{ $indikator->id }}"
                                           value="{{ $savedUrl }}"
                                           {{ $isReadOnly ? 'disabled' : '' }}
                                           placeholder="Masukkan link dokumen atau file yang mendukung jawaban indikator ini">
                                    
                                    {{-- Persyaratan Bukti Dukung (moved from right column for better UX) --}}
                                    @if($indikator->bukti_dukung)
                                        <div style="margin-top: 16px; padding: 12px; background: #F0F9FF; border: 1px solid #90CAF9; border-radius: 6px; box-sizing: border-box;">
                                            <div style="font-weight: 600; color: #0D47A1; margin-bottom: 10px; font-size: 0.95rem;">📋 Persyaratan Bukti Dukung</div>
                                            <ol style="list-style-type: decimal; margin-left: 1.5rem; color: #555; font-size: 0.9rem; margin-right: 0; margin-top: 0; margin-bottom: 0;">
                                                @php
                                                $buktiItems = explode("\n", $indikator->bukti_dukung);
                                                @endphp
                                                @foreach($buktiItems as $item)
                                                    @if(trim($item))
                                                        <li style="margin-bottom: 6px; line-height: 1.5;">{{ trim($item) }}</li>
                                                    @endif
                                                @endforeach
                                            </ol>
                                        </div>
                                    @endif
                                </div>
                            </form>

                            {{-- Save Button --}}
                            @if(!$isReadOnly)
                                <div class="f01-actions">
                                    <button class="f01-btn f01-btn-primary"
                                            onclick="saveProgress()">
                                        💾 Simpan Progress
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- RIGHT COLUMN (35%) - Sticky Info Panels --}}
                    <div class="f01-right-section">
                        <div class="f01-descriptions-card">
                            {{-- Card: Deskripsi Indikator --}}
                            @if($indikator->deskripsi)
                                <div class="f01-description-item">
                                    <div class="f01-description-title">📋 Deskripsi Indikator</div>
                                    <div class="f01-description-content">
                                        @php
                                        // Parse deskripsi dengan support /n marker untuk narasi
                                        $deskripsiItems = explode("\n", str_replace("\r\n", "\n", $indikator->deskripsi));
                                        $pointNumber = 0;
                                        $html = '';
                                        
                                        foreach ($deskripsiItems as $item) {
                                            $item = trim($item);
                                            if (empty($item)) continue;
                                            
                                            if (strpos($item, '/n') === 0) {
                                                // Narasi - remove /n marker
                                                $narasi = trim(substr($item, 2));
                                                if ($narasi) {
                                                    $html .= '<div style="margin-bottom: 12px; text-align: justify; color: #555; line-height: 1.6;">' . htmlspecialchars($narasi) . '</div>';
                                                }
                                                $pointNumber = 0; // Reset numbering
                                            } else {
                                                // Poin - auto-number
                                                $pointNumber++;
                                                $html .= '<div style="margin-bottom: 8px; margin-left: 1.5rem; color: #555; line-height: 1.6;"><strong>' . $pointNumber . '.</strong> ' . htmlspecialchars($item) . '</div>';
                                            }
                                        }
                                        @endphp
                                        {!! $html !!}
                                    </div>
                                </div>
                            @endif

                            @php
                                $f02Data = $f02IndicatorMap[$indikator->id] ?? null;
                            @endphp

                            @if($f02Data)
                                <div class="f01-description-item" style="background: #ECFDF5; border: 1px solid #6EE7B7;">
                                    <div class="f01-description-title">📊 Skor Validasi F02 Sebelumnya</div>
                                    <div class="f01-description-content" style="padding-left: 0;">
                                        <p><strong>Nilai:</strong> {{ number_format($f02Data['nilai'] ?? 0, 2) }}</p>
                                        <p><strong>Catatan:</strong> {{ $f02Data['catatan'] ?? '-' }}</p>
                                        <p><strong>Status:</strong> {{ ucfirst(str_replace('_',' ',$f02Data['status'] ?? '-')) }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Fallback if no descriptions --}}
                            @if(!$indikator->deskripsi)
                                <div class="f01-description-item" style="text-align: center; color: #E5E7EB;">
                                    Tidak ada informasi tambahan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Autosave Indicator --}}
    <div class="f01-autosave-indicator" id="autosaveIndicator"></div>
</div>

<script>
// Initialize conditional questions visibility on page load and tab switch
function initializeConditionalQuestions() {
    // For each question that has an answer, check if it affects dependent questions
    document.querySelectorAll('[name^="jawaban_"]').forEach(input => {
        updateConditionalQuestions(input);
    });
    // Also check skip logic
    checkSkipLogic();
}

// Tab switching
document.querySelectorAll('.f01-indikator-tab').forEach(button => {
    button.addEventListener('click', function() {
        const idx = this.dataset.indikatorIdx;
        
        // Update active tab button styling
        document.querySelectorAll('.f01-indikator-tab').forEach(b => {
            b.classList.remove('border-b-2', 'border-purple-600');
        });
        this.classList.add('border-b-2', 'border-purple-600');
        
        // Update active content with fade animation
        document.querySelectorAll('.f01-tab-content-main').forEach(c => {
            c.classList.add('hidden');
            c.classList.remove('block');
        });
        const activeContent = document.querySelector(`.f01-tab-content-main[data-indikator-idx="${idx}"]`);
        activeContent.classList.remove('hidden');
        activeContent.classList.add('block');
        
        // Smooth scroll tabs container
        const tabsContainer = document.getElementById('indikatorTabs');
        this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });
});

// Autosave on input change
let autosaveTimeout;
document.addEventListener('change', function(e) {
    if (e.target.matches('[name^="jawaban_"], [name^="bukti_"]')) {
        // Handle conditional questions visibility
        updateConditionalQuestions(e.target);
        
        // Check for skip logic
        checkSkipLogic();
        
        clearTimeout(autosaveTimeout);
        showAutosaveIndicator('Menyimpan...', 'saving');
        
        autosaveTimeout = setTimeout(() => {
            autoSaveData();
        }, 2000);
    }
});

// Handle conditional questions visibility (show/hide based on parent answer)
function updateConditionalQuestions(changedInput) {
    const questionId = parseInt(changedInput.name.replace('jawaban_', ''));
    const selectedValue = getSelectedValue(changedInput);
    
    // Find the form that contains this input
    const form = changedInput.closest('.f01-questions-form');
    if (!form) return;
    
    // Find all questions that depend on this question (within same form)
    form.querySelectorAll(`.f01-question[data-parent-question="${questionId}"]`).forEach(depQuestion => {
        const showWhen = depQuestion.getAttribute('data-show-when');
        let shouldShow = false;
        
        if (showWhen === 'keduanya') {
            shouldShow = true;
        } else if (showWhen === 'ya' && (selectedValue === 'Ya' || selectedValue === '"Ya"')) {
            shouldShow = true;
        } else if (showWhen === 'tidak' && (selectedValue === 'Tidak' || selectedValue === '"Tidak"')) {
            shouldShow = true;
        }
        
        // Show or hide with smooth animation
        if (shouldShow) {
            depQuestion.style.display = '';
            setTimeout(() => depQuestion.style.opacity = '1', 50);
            depQuestion.style.transition = 'opacity 0.3s ease';
        } else {
            depQuestion.style.opacity = '0';
            setTimeout(() => depQuestion.style.display = 'none', 300);
        }
    });
}

// Check if any question answer triggers skip logic
function checkSkipLogic() {
    // Get all form groups (by indikator)
    document.querySelectorAll('.f01-questions-form').forEach(form => {
        // First, reset all questions - show everything initially
        form.querySelectorAll('.f01-question').forEach(q => {
            const parentQuestion = q.getAttribute('data-parent-question');
            if (parentQuestion && parentQuestion !== '') {
                // Keep child questions hidden initially
                q.style.display = 'none';
                q.style.opacity = '0';
            } else {
                // Parent questions should be visible
                q.style.display = '';
                q.style.opacity = '1';
            }
        });

        // Now check skip logic for each question in this form
        form.querySelectorAll('.f01-question').forEach(question => {
            const skipIfAnswer = question.getAttribute('data-skip-if-answer');
            if (!skipIfAnswer) return;
            
            const questionId = question.getAttribute('data-question-id');
            const input = form.querySelector(`[name="jawaban_${questionId}"]`) || 
                         form.querySelector(`[name="jawaban_${questionId}[]"]`);
            
            if (!input) return;
            
            const selectedValue = getSelectedValue(input, form);
            
            // Case-insensitive comparison for skip trigger
            const normalizedSkipValue = String(skipIfAnswer).toLowerCase().trim();
            const normalizedSelectedValue = String(selectedValue).toLowerCase().trim();
            
            // If answer matches skip trigger, hide all following questions in THIS FORM
            if (selectedValue && normalizedSelectedValue === normalizedSkipValue) {
                // Get all questions in form in DOM order
                const allQuestions = Array.from(form.querySelectorAll('.f01-question'));
                const currentIdx = allQuestions.indexOf(question);
                
                console.log(`Skip triggered: Question ${questionId} answered with "${selectedValue}" (skip value: "${skipIfAnswer}")`);
                
                // Hide all questions after this one
                for (let i = currentIdx + 1; i < allQuestions.length; i++) {
                    console.log(`Hiding question: ${allQuestions[i].getAttribute('data-question-id')}`);
                    allQuestions[i].style.display = 'none';
                    allQuestions[i].style.opacity = '0';
                }
            }
        });
    });
}

// Get selected value from any input type
function getSelectedValue(input, form = null) {
    // Use provided form or find parent form
    if (!form) {
        form = input.closest('form') || input.closest('.f01-questions-form');
    }
    
    if (input.type === 'radio' || input.type === 'checkbox') {
        let selector = `input[name="${input.name}"]:checked`;
        let checkedInputs;
        
        if (form) {
            checkedInputs = form.querySelectorAll(selector);
        } else {
            checkedInputs = document.querySelectorAll(selector);
        }
        
        if (checkedInputs.length === 1) {
            const value = checkedInputs[0].value;
            console.log(`getSelectedValue for ${input.name}: "${value}"`);
            return value;
        } else if (checkedInputs.length > 1) {
            return Array.from(checkedInputs).map(i => i.value);
        }
        return '';
    } else if (input.tagName === 'SELECT') {
        return input.value;
    }
    return input.value;
}

function autoSaveData() {
    console.log('\n💾 AUTO-SAVE TRIGGERED (2-second debounce)');
    const pengisianId = {{ $pengisian->id }};
    const formData = new FormData();
    const processedNames = new Set();

    // Collect all form data - HANDLE RADIOS CORRECTLY
    document.querySelectorAll('[name^="jawaban_"], [name^="bukti_"]').forEach(input => {
        // Skip if we already processed this input name  (for radios/checkboxes)
        if (processedNames.has(input.name)) {
            return;
        }
        processedNames.add(input.name);

        if (input.type === 'radio') {
            // For radio: only append the checked one
            const checked = document.querySelector(`input[name="${input.name}"]:checked`);
            if (checked) {
                formData.append(input.name, checked.value);
                console.log(`  Q (radio): ${input.name} = "${checked.value}" ✓`);
            }
        } else if (input.type === 'checkbox') {
            // For checkboxes: collect all checked values (ALWAYS append, even if empty!)
            const checkboxes = Array.from(document.querySelectorAll(`input[name="${input.name}"]:checked`))
                .map(cb => cb.value);
            // Always append - empty array [] means none selected
            formData.append(input.name, JSON.stringify(checkboxes));
            console.log(`  Q (checkbox): ${input.name} = ${JSON.stringify(checkboxes)} ${checkboxes.length > 0 ? '✓' : '(empty)'}`);
        } else {
            // For text/select/etc: simple value
            if (input.value) {
                formData.append(input.name, input.value);
                console.log(`  Q (${input.type}): ${input.name} = "${input.value}" ✓`);
            }
        }
    });

    const autoSaveUrl = "{{ route('f01.auto-save', ['pengision' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', pengisianId);
    
    console.log(`📤 Sending to ${autoSaveUrl}...`);
    fetch(autoSaveUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log(`✅ AUTO-SAVE SUCCESS! Processed ${data.processed} jawaban`);
            showAutosaveIndicator('✓ Tersimpan', 'saved');
            setTimeout(() => hideAutosaveIndicator(), 3000);
        } else {
            console.warn(`⚠️ AUTO-SAVE FAILED: ${data.message}`);
            showAutosaveIndicator('✗ Gagal Simpan', 'error');
            setTimeout(() => hideAutosaveIndicator(), 3000);
        }
    })
    .catch(err => {
        console.error(`❌ AUTO-SAVE ERROR: ${err}`);
        showAutosaveIndicator('✗ Error', 'error');
        setTimeout(() => hideAutosaveIndicator(), 3000);
    });
}

function saveProgress() {
    console.log('\n💾 SAVE PROGRESS BUTTON CLICKED');
    const pengisianId = {{ $pengisian->id }};
    const formData = new FormData();
    const processedNames = new Set();

    // Collect all form data - HANDLE RADIOS CORRECTLY (same fix as autoSaveData)
    document.querySelectorAll('[name^="jawaban_"], [name^="bukti_"]').forEach(input => {
        // Skip if we already processed this input name (for radios/checkboxes)
        if (processedNames.has(input.name)) {
            return;
        }
        processedNames.add(input.name);

        if (input.type === 'radio') {
            // For radio: only append the checked one
            const checked = document.querySelector(`input[name="${input.name}"]:checked`);
            if (checked) {
                formData.append(input.name, checked.value);
                console.log(`  Q (radio): ${input.name} = "${checked.value}" ✓`);
            }
        } else if (input.type === 'checkbox') {
            // For checkboxes: collect all checked values (ALWAYS append, even if empty!)
            const checkboxes = Array.from(document.querySelectorAll(`input[name="${input.name}"]:checked`))
                .map(cb => cb.value);
            // Always append checkbox value - empty array [] means none selected
            formData.append(input.name, JSON.stringify(checkboxes));
            console.log(`  Q (checkbox): ${input.name} = ${JSON.stringify(checkboxes)} ${checkboxes.length > 0 ? '✓' : '(empty)'}`);
        } else {
            // For text/select/etc: simple value
            if (input.value) {
                formData.append(input.name, input.value);
                console.log(`  Q (${input.type}): ${input.name} = "${input.value}" ✓`);
            }
        }
    });

    if (!confirm('Simpan progress pengisian saat ini?')) {
        console.log('User cancelled save');
        return;
    }

    const autoSaveUrl = "{{ route('f01.auto-save', ['pengision' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', pengisianId);
    
    console.log(`📤 Sending to ${autoSaveUrl}...`);
    fetch(autoSaveUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log(`✅ SAVE PROGRESS SUCCESS! Processed ${data.processed} jawaban`);
            alert('✓ Progress berhasil disimpan. Perbarui halaman jika ingin melihat perubahan terbaru.');
        } else {
            console.warn(`⚠️ SAVE PROGRESS FAILED: ${data.message}`);
            alert('Gagal menyimpan: ' + (data.message || 'Error tidak diketahui'));
        }
    })
    .catch(err => {
        console.error(`❌ SAVE PROGRESS ERROR: ${err}`);
        alert('Error: ' + err.message);
    });
}

function showAutosaveIndicator(message, type = 'saving') {
    const indicator = document.getElementById('autosaveIndicator');
    indicator.textContent = message;
    indicator.className = `f01-autosave-indicator ${type}`;
    indicator.style.display = 'flex';
}

function hideAutosaveIndicator() {
    document.getElementById('autosaveIndicator').style.display = 'none';
}

// Initialize conditional questions on page load
document.addEventListener('DOMContentLoaded', initializeConditionalQuestions);

// Handle "Lainnya" checkbox toggle for multiple choice questions
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('f01-lainnya-checkbox')) {
        const questionId = e.target.dataset.questionId;
        const inputWrapper = document.getElementById(`lainnya_input_${questionId}`);
        const inputField = inputWrapper?.querySelector('input[type="text"]');
        
        if (e.target.checked) {
            // Show input field when "Lainnya" is checked
            inputWrapper.style.display = 'block';
            if (inputField) {
                inputField.focus();
            }
        } else {
            // Hide input field and clear value when unchecked
            inputWrapper.style.display = 'none';
            if (inputField) {
                inputField.value = '';
            }
        }
        
        // Trigger autosave if input has value
        if (inputField && inputField.value.trim()) {
            clearTimeout(autosaveTimeout);
            showAutosaveIndicator('Menyimpan...', 'saving');
            autosaveTimeout = setTimeout(() => {
                autoSaveData();
            }, 2000);
        }
    }
});

// Handle "Lainnya" input field changes (trigger autosave)
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('f01-lainnya-input')) {
        clearTimeout(autosaveTimeout);
        showAutosaveIndicator('Menyimpan...', 'saving');
        autosaveTimeout = setTimeout(() => {
            autoSaveData();
        }, 2000);
    }
});
</script>
@endsection
