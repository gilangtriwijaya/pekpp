@extends('layouts.app')

@section('title', 'Pendataan - ' . $aspek->nama)

@section('content')
<div class="f01-container" style="display: flex; gap: 30px; max-width: 1300px; margin: 0 auto; padding: 30px;">
    <style>
        .sidebar {
            width: 300px;
            flex-shrink: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            padding: 20px;
            height: fit-content;
        }
        .main-content {
            flex: 1;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .aspek-link {
            display: block;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            color: #4B5563;
            text-decoration: none;
            transition: all 0.2s;
        }
        .aspek-link:hover {
            background: #F3F4F6;
        }
        .aspek-link.active {
            background: #EFF6FF;
            color: #1D4ED8;
            font-weight: 600;
        }
        .question-card {
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .question-label {
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 1.05rem;
        }
        .form-radio {
            margin-right: 8px;
        }
        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
        }
        .save-indicator {
            font-size: 0.85rem;
            color: #10B981;
            display: none;
            margin-top: 8px;
        }
    </style>

    <div class="sidebar">
        <h3 style="font-weight: bold; margin-bottom: 20px; font-size: 1.1rem;">Daftar Aspek</h3>
        @foreach($aspeks as $a)
            <a href="{{ route('pendataan.aspek.detail', ['pengisianId' => $pengisian->id, 'aspekId' => $a->id]) }}" 
               class="aspek-link {{ $a->id == $aspek->id ? 'active' : '' }}">
                {{ $a->urutan }}. {{ $a->nama }}
            </a>
        @endforeach
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #E5E7EB;">
            <a href="{{ route('pendataan.aspek-list', $pengisian->id) }}" style="color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <span>←</span> Kembali ke Ringkasan
            </a>
        </div>
    </div>

    <div class="main-content">
        <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 30px; border-bottom: 2px solid #E5E7EB; padding-bottom: 15px;">
            {{ $aspek->urutan }}. {{ $aspek->nama }}
        </h2>

        @if($pertanyaanData->isEmpty())
            <div style="padding: 20px; text-align: center; color: #6B7280; background: #F9FAFB; border-radius: 8px;">
                Belum ada pertanyaan untuk aspek ini.
            </div>
        @endif

        @foreach($pertanyaanData as $data)
            <div class="question-card">
                <div class="question-label">
                    {{ $data['pertanyaan']->urutan }}. {{ $data['pertanyaan']->label }}
                    @if($data['pertanyaan']->wajib) <span style="color: red;">*</span> @endif
                </div>

                @php
                    $tipe = $data['pertanyaan']->tipe_input;
                    $opsi = json_decode($data['pertanyaan']->opsi_jawaban, true) ?: [];
                    if ($tipe === 'yesno') {
                        $opsi = [
                            ['value' => 'ya', 'label' => 'Ya'],
                            ['value' => 'tidak', 'label' => 'Tidak']
                        ];
                        $tipe = 'radio';
                    }
                @endphp

                <div class="answer-container">
                    @if($tipe === 'radio')
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($opsi as $op)
                                <label style="display: flex; align-items: center; cursor: pointer; gap: 10px; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 7px; transition: background 0.15s;" 
                                       onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                                    <input type="radio" 
                                           name="p_{{ $data['pertanyaan']->id }}" 
                                           value="{{ $op['value'] ?? $op['label'] }}" 
                                           class="answer-input"
                                           data-id="{{ $data['pertanyaan']->id }}"
                                           {{ $data['jawaban'] === ($op['value'] ?? $op['label']) ? 'checked' : '' }}
                                           {{ $isReadOnly ? 'disabled' : '' }}
                                           style="width:16px; height:16px; cursor:pointer; accent-color:#4F46E5;">
                                    <span>{{ $op['label'] ?? $op['value'] }}</span>
                                </label>
                            @endforeach
                        </div>

                    @elseif($tipe === 'checkbox')
                        @php
                            $savedValues = $data['jawaban'] ? json_decode($data['jawaban'], true) : [];
                            if (!is_array($savedValues)) {
                                $savedValues = $data['jawaban'] ? explode(',', $data['jawaban']) : [];
                            }
                        @endphp
                        <div class="checkbox-group" data-id="{{ $data['pertanyaan']->id }}" style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($opsi as $op)
                                @php $val = $op['value'] ?? $op['label']; @endphp
                                <label style="display: flex; align-items: center; cursor: pointer; gap: 10px; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 7px; transition: background 0.15s;"
                                       onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                                    <input type="checkbox"
                                           name="p_{{ $data['pertanyaan']->id }}[]"
                                           value="{{ $val }}"
                                           class="checkbox-input"
                                           data-id="{{ $data['pertanyaan']->id }}"
                                           {{ in_array($val, $savedValues) ? 'checked' : '' }}
                                           {{ $isReadOnly ? 'disabled' : '' }}
                                           style="width:16px; height:16px; cursor:pointer; accent-color:#4F46E5;">
                                    <span>{{ $op['label'] ?? $op['value'] }}</span>
                                </label>
                            @endforeach
                        </div>

                    @elseif($tipe === 'select')
                        <select class="form-input answer-input" 
                                name="p_{{ $data['pertanyaan']->id }}"
                                data-id="{{ $data['pertanyaan']->id }}"
                                {{ $isReadOnly ? 'disabled' : '' }}
                                style="padding: 9px 12px;">
                            <option value="">-- Pilih --</option>
                            @foreach($opsi as $op)
                                <option value="{{ $op['value'] ?? $op['label'] }}" 
                                        {{ $data['jawaban'] === ($op['value'] ?? $op['label']) ? 'selected' : '' }}>
                                    {{ $op['label'] ?? $op['value'] }}
                                </option>
                            @endforeach
                        </select>

                    @elseif($tipe === 'textarea')
                        <textarea class="form-input answer-input"
                                  name="p_{{ $data['pertanyaan']->id }}"
                                  data-id="{{ $data['pertanyaan']->id }}"
                                  rows="4"
                                  placeholder="Ketik jawaban Anda..."
                                  {{ $isReadOnly ? 'disabled' : '' }}
                                  style="resize: vertical;">{{ $data['jawaban'] }}</textarea>

                    @elseif($tipe === 'number')
                        <input type="number"
                               name="p_{{ $data['pertanyaan']->id }}"
                               class="form-input answer-input"
                               data-id="{{ $data['pertanyaan']->id }}"
                               value="{{ $data['jawaban'] }}"
                               placeholder="Masukkan angka..."
                               {{ $isReadOnly ? 'disabled' : '' }}>

                    @elseif($tipe === 'date')
                        <input type="date"
                               name="p_{{ $data['pertanyaan']->id }}"
                               class="form-input answer-input"
                               data-id="{{ $data['pertanyaan']->id }}"
                               value="{{ $data['jawaban'] }}"
                               {{ $isReadOnly ? 'disabled' : '' }}>

                    @else
                        {{-- Default: text --}}
                        <input type="text" 
                               name="p_{{ $data['pertanyaan']->id }}" 
                               class="form-input answer-input" 
                               data-id="{{ $data['pertanyaan']->id }}"
                               value="{{ $data['jawaban'] }}"
                               placeholder="Ketik jawaban Anda..."
                               {{ $isReadOnly ? 'disabled' : '' }}>
                    @endif
                </div>
                
                {{-- Bukti Dukung Upload --}}
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #E5E7EB;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #4B5563; margin-bottom: 8px;">Bukti Dukung (PDF, Maks 10MB)</label>
                    
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="file" 
                               id="file_{{ $data['pertanyaan']->id }}" 
                               class="file-upload-input" 
                               accept="application/pdf"
                               data-id="{{ $data['pertanyaan']->id }}"
                               style="display: none;"
                               {{ $isReadOnly ? 'disabled' : '' }}>
                               
                        <button type="button" 
                                onclick="document.getElementById('file_{{ $data['pertanyaan']->id }}').click()"
                                class="btn-upload"
                                {{ $isReadOnly ? 'disabled' : '' }}
                                style="padding: 6px 12px; background: #F3F4F6; border: 1px solid #D1D5DB; border-radius: 6px; cursor: {{ $isReadOnly ? 'not-allowed' : 'pointer' }}; font-size: 0.9rem; display: flex; align-items: center; gap: 6px; transition: background 0.2s;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            Pilih File PDF
                        </button>

                        <div id="file_name_{{ $data['pertanyaan']->id }}" style="font-size: 0.85rem; color: #6B7280; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            @if($data['file_path'])
                                <a href="{{ asset('storage/' . $data['file_path']) }}" target="_blank" style="color: #2563EB; text-decoration: underline;">{{ $data['file_name'] }}</a>
                            @else
                                Belum ada file
                            @endif
                        </div>
                    </div>
                    
                    <div class="upload-progress" id="progress_{{ $data['pertanyaan']->id }}" style="display: none; margin-top: 8px; font-size: 0.8rem; color: #3B82F6;">
                        Mengunggah file...
                    </div>
                    <div class="upload-error" id="error_{{ $data['pertanyaan']->id }}" style="display: none; margin-top: 8px; font-size: 0.8rem; color: #EF4444;"></div>
                </div>
                
                <div class="save-indicator" id="saved_{{ $data['pertanyaan']->id }}">✓ Tersimpan</div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isReadOnly = {{ $isReadOnly ? 'true' : 'false' }};
    const autoSaveUrl = '{{ route('pendataan.auto-save', $pengisian->id) }}';
    const csrfToken = '{{ csrf_token() }}';
    let timeoutId = null;

    // Regular inputs (radio, select, date, number)
    document.querySelectorAll('.answer-input').forEach(function(input) {
        input.addEventListener('change', function() {
            saveAnswer(this.dataset.id, this.value);
        });
        // Debounce for text/textarea/number
        if (input.type === 'text' || input.tagName === 'TEXTAREA' || input.type === 'number') {
            input.addEventListener('input', function() {
                clearTimeout(timeoutId);
                var self = this;
                timeoutId = setTimeout(function() {
                    saveAnswer(self.dataset.id, self.value);
                }, 800);
            });
        }
    });

    // Checkbox groups — collect all checked values as JSON array
    document.querySelectorAll('.checkbox-group').forEach(function(group) {
        group.querySelectorAll('.checkbox-input').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var pertanyaanId = group.dataset.id;
                var checkedValues = [];
                group.querySelectorAll('.checkbox-input:checked').forEach(function(checked) {
                    checkedValues.push(checked.value);
                });
                saveAnswer(pertanyaanId, JSON.stringify(checkedValues));
            });
        });
    });

    // File Upload handling
    const uploadUrl = '{{ route('pendataan.upload-bukti', $pengisian->id) }}';
    
    document.querySelectorAll('.file-upload-input').forEach(function(input) {
        input.addEventListener('change', function() {
            if (!this.files || this.files.length === 0) return;
            
            const file = this.files[0];
            const pertanyaanId = this.dataset.id;
            const progressEl = document.getElementById('progress_' + pertanyaanId);
            const errorEl = document.getElementById('error_' + pertanyaanId);
            const nameEl = document.getElementById('file_name_' + pertanyaanId);
            
            // Validation
            if (file.type !== 'application/pdf') {
                errorEl.textContent = 'Hanya file PDF yang diperbolehkan.';
                errorEl.style.display = 'block';
                return;
            }
            if (file.size > 10 * 1024 * 1024) { // 10MB
                errorEl.textContent = 'Ukuran file maksimal 10MB.';
                errorEl.style.display = 'block';
                return;
            }

            errorEl.style.display = 'none';
            progressEl.style.display = 'block';
            
            const formData = new FormData();
            formData.append('pertanyaan_id', pertanyaanId);
            formData.append('file', file);
            
            fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(function(data) {
                progressEl.style.display = 'none';
                if (data.success) {
                    nameEl.innerHTML = '<a href="' + data.file_url + '" target="_blank" style="color: #2563EB; text-decoration: underline;">' + data.file_name + '</a>';
                    // Show saved indicator
                    var indicator = document.getElementById('saved_' + pertanyaanId);
                    if (indicator) {
                        indicator.textContent = '✓ File terunggah';
                        indicator.style.display = 'block';
                        setTimeout(function() { 
                            indicator.style.display = 'none'; 
                            indicator.textContent = '✓ Tersimpan';
                        }, 2000);
                    }
                } else {
                    errorEl.textContent = data.message || 'Gagal mengunggah file.';
                    errorEl.style.display = 'block';
                }
            })
            .catch(function(error) {
                progressEl.style.display = 'none';
                errorEl.textContent = error.message || 'Terjadi kesalahan saat mengunggah.';
                errorEl.style.display = 'block';
                console.error('Upload error:', error);
            });
        });
    });

    function saveAnswer(pertanyaanId, nilai) {
        if (isReadOnly) return;

        fetch(autoSaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                pertanyaan_id: pertanyaanId,
                nilai: nilai
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var indicator = document.getElementById('saved_' + pertanyaanId);
                if (indicator) {
                    indicator.style.display = 'block';
                    setTimeout(function() { indicator.style.display = 'none'; }, 2000);
                }
            }
        })
        .catch(function(error) { console.error('Auto-save error:', error); });
    }
});
</script>
@endsection
