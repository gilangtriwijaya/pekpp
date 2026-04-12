@extends('layouts.public')
@section('title','Kuesioner F03')
@section('content')

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F9FAFB; }
    .f03-public-container { max-width: 900px; margin: 0 auto; padding: 30px 20px; }
    .f03-public-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }
    .f03-public-header h1 { font-size: 28px; margin-bottom: 10px; }
    .f03-public-header p { font-size: 16px; opacity: 0.95; line-height: 1.5; }
    
    .f03-public-form { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
    
    .f03-aspek-section { margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid #E5E7EB; }
    .f03-aspek-section:last-child { border-bottom: none; }
    
    .f03-aspek-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #667eea;
    }
    
    .f03-indikator-item { margin-bottom: 35px; }
    
    .f03-pertanyaan {
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 15px;
        line-height: 1.6;
    }
    
    .f03-score-options {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .f03-score-label { display: block; font-size: 12px; color: #6B7280; font-weight: 500; }
    .f03-score-label-left { margin-right: 10px; }
    .f03-score-label-right { margin-left: 10px; }
    
    .f03-score-radio {
        display: flex;
        gap: 24px;
        align-items: center;
        justify-content: space-around;
        flex: 1;
    }
    
    .f03-score-input-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 1;
        min-width: 50px;
    }
    
    .f03-score-input {
        width: 24px;
        height: 24px;
        cursor: pointer;
        accent-color: #667eea;
    }
    
    .f03-score-center { font-size: 12px; color: #6B7280; font-weight: 500; }
    
    .f03-catatan-text {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        min-height: 60px;
    }
    .f03-catatan-text:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
    
    .f03-submit-section { margin-top: 40px; padding-top: 30px; border-top: 2px solid #E5E7EB; }
    
    .f03-submit-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 40px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
        max-width: 300px;
    }
    .f03-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4); }
    .f03-submit-btn:active { transform: translateY(0); }
    .f03-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    
    .f03-error-message {
        background-color: #FEE2E2;
        border: 1px solid #FECACA;
        color: #991B1B;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .f03-success-message {
        background-color: #DBEAFE;
        border: 1px solid #BAE6FD;
        color: #0C4A6E;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .f03-loading {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    
    .f03-progress-bar {
        width: 100%;
        height: 4px;
        background-color: #E5E7EB;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .f03-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        width: 0%;
        transition: width 0.3s;
    }
    
    /* Responsive styles for Likert Scale - Mobile View */
    .f03-likert-container {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: flex-start;
    }
    
    .f03-likert-header {
        width: 100%;
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        margin-bottom: 12px;
        padding: 0 10px;
    }
    
    .f03-likert-options {
        width: 100%;
        display: flex;
        gap: 6px;
        justify-content: space-between;
    }
    
    .f03-likert-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        min-width: 50px;
    }
    
    .f03-likert-item input[type="radio"] {
        width: 24px;
        height: 24px;
        cursor: pointer;
        accent-color: #667eea;
        margin-bottom: 6px;
    }
    
    .f03-likert-label {
        font-size: 11px;
        color: #6B7280;
        text-align: center;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .f03-public-container { padding: 20px 15px; }
        .f03-public-form { padding: 25px 20px; }
        .f03-public-header { padding: 30px 20px; }
        .f03-public-header h1 { font-size: 22px; }
        .f03-submit-btn { max-width: 100%; }
        
        /* Mobile Likert Scale - Better Layout */
        .f03-score-options {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .f03-likert-header {
            margin-bottom: 15px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .f03-score-radio {
            display: flex;
            flex-direction: row;
            gap: 8px;
            width: 100%;
            justify-content: space-between;
        }
        
        .f03-score-input-wrapper { 
            width: auto;
            height: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 45px;
        }
        
        .f03-score-input {
            width: 28px;
            height: 28px;
        }
        
        .f03-likert-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }
    }
</style>

<div class="f03-public-container">
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="flex-shrink: 0;">
            @if(file_exists(public_path('images/logo-pemda.png')))
            <img src="{{ asset('images/logo-pemda.png') }}" alt="Logo" style="height: 80px; width: auto;">
            @else
            <div style="height: 80px; width: 80px; background: #E5E7EB; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #6B7280; text-align: center; padding: 8px;">Logo Pemda Anambas</div>
            @endif
        </div>
        <div style="flex: 1;">
            <h2 style="font-size: 20px; font-weight: 700; color: #1F2937; margin-bottom: 8px;">Kuesioner Evaluasi Kinerja Pelayanan Publik</h2>
            <p style="font-size: 14px; color: #6B7280; margin-bottom: 8px;">Kabupaten Kepulauan Anambas</p>
            @if($token->upp)
            <div style="margin-top: 12px; padding: 12px; background: #F3F4F6; border-radius: 6px;">
                <p style="font-size: 13px; margin: 4px 0; color: #374151;"><strong>Unit Pelayanan Publik (UPP):</strong> {{ $token->upp->nama }}</p>
                @if($token->periode)
                <p style="font-size: 13px; margin: 4px 0; color: #374151;"><strong>Periode:</strong> {{ $token->periode->tahun }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="f03-public-header">
        <h3 style="font-size: 16px; margin-bottom: 10px;">Petunjuk Pengisian</h3>
        <p>Terima kasih telah meluangkan waktu untuk mengisi kuesioner ini. Pendapat Anda sangat berharga bagi kami untuk meningkatkan kualitas pelayanan publik.</p>
    </div>

    @if(session('error'))
    <div class="f03-error-message" id="errorMessage">
        {{ session('error') }}
        <button onclick="document.getElementById('errorMessage').style.display='none'" style="float:right; background:none; border:none; cursor:pointer; color:#991B1B; font-size:18px;">&times;</button>
    </div>
    @endif

    @if(session('success'))
    <div class="f03-success-message" id="successMessage">
        {{ session('success') }}
    </div>
    @endif

    <form id="f03Form" method="POST" action="{{ route('f03.public.submit', ['token' => $token->token]) }}" class="f03-public-form">
        @csrf

        <div class="f03-progress-bar">
            <div class="f03-progress-fill" id="progressFill"></div>
        </div>

        {{-- DEMOGRAPHIC SECTION --}}
        <div class="f03-aspek-section">
            <h2 class="f03-aspek-title">📋 Data Diri Responden</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">
                
                {{-- GENDER --}}
                <div class="f03-demographic-field">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">
                        Jenis Kelamin <span style="color: #DC2626;">*</span>
                    </label>
                    <select 
                        name="gender" 
                        id="genderSelect" 
                        required
                        style="width: 100%; padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; font-family: inherit; background-color: white; cursor: pointer;"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="M">Pria</option>
                        <option value="F">Wanita</option>
                    </select>
                </div>

                {{-- AGE --}}
                <div class="f03-demographic-field">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">
                        Usia <span style="color: #DC2626;">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="age" 
                        id="ageInput"
                        min="18"
                        max="100"
                        required
                        placeholder="Masukkan usia Anda"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; font-family: inherit;"
                    >
                    <small style="color: #6B7280; margin-top: 4px; display: block;">Usia minimal 18 tahun</small>
                </div>

                {{-- LAST EDUCATION --}}
                <div class="f03-demographic-field">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">
                        Pendidikan Terakhir <span style="color: #DC2626;">*</span>
                    </label>
                    <select 
                        name="last_education" 
                        id="educationSelect"
                        required
                        style="width: 100%; padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; font-family: inherit; background-color: white; cursor: pointer;"
                    >
                        <option value="">-- Pilih Pendidikan Terakhir --</option>
                        <option value="SD">SD / Sederajat</option>
                        <option value="SMP">SMP / Sederajat</option>
                        <option value="SMA">SMA / Sederajat</option>
                        <option value="D1">D1</option>
                        <option value="D2">D2</option>
                        <option value="D3">D3</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                    </select>
                </div>

                {{-- OCCUPATION --}}
                <div class="f03-demographic-field">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">
                        Pekerjaan <span style="color: #DC2626;">*</span>
                    </label>
                    <select 
                        name="occupation" 
                        id="occupationSelect"
                        required
                        style="width: 100%; padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; font-family: inherit; background-color: white; cursor: pointer;"
                    >
                        <option value="">-- Pilih Pekerjaan --</option>
                        <option value="ASN/PNS">ASN</option>
                        <option value="TNI/Polri">TNI/Polri</option>
                        <option value="BUMN/BUMD">BUMN/BUMD</option>
                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                        <option value="Wirausahawan">Wirausahawan</option>
                        <option value="Profesional Independen">Profesional Independen</option>
                        <option value="Petani">Petani</option>
                        <option value="Nelayan">Nelayan</option>
                        <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
                        <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                        <option value="Pensiunan">Pensiunan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

            </div>
        </div>

        @forelse($aspeks as $aspekKey => $aspek)
        <div class="f03-aspek-section">
            <h2 class="f03-aspek-title">{{ $aspekKey + 1 }}. {{ $aspek->nama }}</h2>

            @forelse($aspek->indikator as $indikatorKey => $indikator)
            <div class="f03-indikator-item">
                <div class="f03-pertanyaan">{{ $aspekKey + 1 }}.{{ $indikatorKey + 1 }} {{ $indikator->pertanyaan }}</div>

                {{-- Likert Scale 1-5 --}}
                @if($indikator->tipe_jawaban == 'likert_5')
                <div class="f03-score-options">
                    <div class="f03-likert-header">
                        <span>Sangat Tidak Setuju</span>
                        <span style="margin-right: 15px;">Sangat Setuju</span>
                    </div>
                    <div class="f03-score-radio">
                        @for($score = 1; $score <= 5; $score++)
                        <div class="f03-score-input-wrapper">
                            <input 
                                type="radio" 
                                name="responses[{{ $indikator->id }}]"
                                value="{{ $score }}"
                                class="f03-score-input f03-required-input"
                                onchange="updateProgress()"
                                required
                            >
                            <span class="f03-likert-label">{{ $score }}</span>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Radio Buttons (generic) --}}
                @elseif($indikator->tipe_jawaban == 'radio')
                <div class="f03-score-options" style="flex-direction: column;">
                    @if(is_array($indikator->pilihan_jawaban))
                        @foreach($indikator->pilihan_jawaban as $idx => $pilihan)
                        <div style="margin-bottom: 12px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input 
                                    type="radio" 
                                    name="responses[{{ $indikator->id }}]"
                                    value="{{ $pilihan }}"
                                    class="f03-score-input f03-required-input"
                                    onchange="updateProgress()"
                                    required
                                >
                                <span style="margin-left: 10px; color: #374151;">{{ $pilihan }}</span>
                            </label>
                        </div>
                        @endforeach
                    @endif
                </div>

                {{-- Checkboxes --}}
                @elseif($indikator->tipe_jawaban == 'checkbox')
                <div class="f03-score-options" style="flex-direction: column;">
                    @if(is_array($indikator->pilihan_jawaban))
                        @foreach($indikator->pilihan_jawaban as $idx => $pilihan)
                        <div style="margin-bottom: 12px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input 
                                    type="checkbox" 
                                    name="responses[{{ $indikator->id }}][]"
                                    value="{{ $pilihan }}"
                                    class="f03-score-input f03-required-input-checkbox"
                                    onchange="updateProgress()"
                                >
                                <span style="margin-left: 10px; color: #374151;">{{ $pilihan }}</span>
                            </label>
                        </div>
                        @endforeach
                    @endif
                </div>

                {{-- Dropdown Select --}}
                @elseif($indikator->tipe_jawaban == 'dropdown')
                <select 
                    name="responses[{{ $indikator->id }}]"
                    class="f03-catatan-text f03-required-input"
                    onchange="updateProgress()"
                    required
                    style="padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; font-family: inherit;"
                >
                    <option value="">-- Pilih Opsi --</option>
                    @if(is_array($indikator->pilihan_jawaban))
                        @foreach($indikator->pilihan_jawaban as $pilihan)
                        <option value="{{ $pilihan }}">{{ $pilihan }}</option>
                        @endforeach
                    @endif
                </select>

                {{-- Rating (1-5 stars) --}}
                @elseif($indikator->tipe_jawaban == 'rating')
                <div class="f03-score-options" style="gap: 20px;">
                    @for($score = 1; $score <= 5; $score++)
                    <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                        <input 
                            type="radio" 
                            name="responses[{{ $indikator->id }}]"
                            value="{{ $score }}"
                            class="f03-score-input f03-required-input"
                            onchange="updateProgress()"
                            required
                            style="display: none;"
                        >
                        <span style="font-size: 28px; margin-bottom: 5px;" class="f03-star-rating" data-score="{{ $score }}">⭐</span>
                        <span style="font-size: 12px; color: #6B7280;">{{ $score }}</span>
                    </label>
                    @endfor
                </div>

                {{-- Textarea --}}
                @elseif($indikator->tipe_jawaban == 'textarea')
                <textarea 
                    name="responses[{{ $indikator->id }}]"
                    class="f03-catatan-text f03-required-input"
                    placeholder="Masukkan respons Anda"
                    required
                    style="min-height: 100px;"
                ></textarea>

                {{-- Text Input --}}
                @elseif($indikator->tipe_jawaban == 'text')
                <input 
                    type="text"
                    name="responses[{{ $indikator->id }}]"
                    class="f03-catatan-text f03-required-input"
                    placeholder="Masukkan respons Anda"
                    required
                    style="min-height: 40px; padding: 10px 12px;"
                >
                @endif
            </div>
            @empty
            <p style="color: #9CA3AF; font-size: 14px;">Belum ada indikator untuk aspek ini</p>
            @endforelse
        </div>
        @empty
        <div class="f03-error-message">
            Belum ada aspek kuesioner yang tersedia. Silakan hubungi admin.
        </div>
        @endforelse

        @if($aspeks->count() > 0)
        <div class="f03-submit-section">
            <button type="submit" class="f03-submit-btn" id="submitBtn">
                <span id="submitText">Kirim Respons</span>
                <span id="submitLoader" style="display: none; margin-left: 8px;"><div class="f03-loading"></div></span>
            </button>
            <p style="margin-top: 15px; color: #6B7280; font-size: 13px; text-align: center;">
                Terima kasih telah mengisi kuesioner ini. Data Anda telah kami catat.
            </p>
        </div>
        @endif
    </form>
</div>

<script>
    const f03Token = '{{ $token->token }}';
    const totalIndikators = {{ $aspeks->sum(function($a) { return count($a->indikator); }) }};

    function updateProgress() {
        let answered = 0;
        document.querySelectorAll('[name^="responses["]').forEach((el) => {
            if (el.type === 'checkbox') {
                // For checkboxes, check if at least one is selected
                const name = el.name.replace('[]', '');
                if (document.querySelector(`${name}[]:checked`)) {
                    answered++;
                }
            } else if (el.type === 'text' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
                // For text, textarea, and select
                if (el.value.trim()) {
                    answered++;
                }
            } else if (el.type === 'radio') {
                // For radios, count once per group if any is checked
                const name = el.name;
                if (document.querySelector(`input[name="${name}"]:checked`)) {
                    answered++;
                }
            }
        });
        
        const percentage = totalIndikators > 0 ? Math.round((answered / totalIndikators) * 100) : 0;
        document.getElementById('progressFill').style.width = percentage + '%';
    }

    // Star rating interaction
    document.querySelectorAll('.f03-star-rating').forEach(star => {
        star.style.cursor = 'pointer';
        star.addEventListener('click', function() {
            const score = this.dataset.score;
            const input = this.closest('label').querySelector('input');
            input.checked = true;
            input.dispatchEvent(new Event('change'));
        });
        star.addEventListener('mouseover', function() {
            const score = this.dataset.score;
            this.closest('.f03-score-options').querySelectorAll('.f03-star-rating').forEach(s => {
                s.textContent = s.dataset.score <= score ? '⭐' : '☆';
            });
        });
    });

    document.querySelectorAll('.f03-score-options').forEach(group => {
        if (group.querySelector('.f03-star-rating')) {
            group.addEventListener('mouseout', function() {
                const checked = this.querySelector('input:checked');
                if (checked) {
                    const score = checked.value;
                    this.querySelectorAll('.f03-star-rating').forEach(s => {
                        s.textContent = s.dataset.score <= score ? '⭐' : '☆';
                    });
                } else {
                    this.querySelectorAll('.f03-star-rating').forEach(s => {
                        s.textContent = '☆';
                    });
                }
            });
        }
    });

    document.getElementById('f03Form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate checkboxes
        let hasError = false;
        document.querySelectorAll('[name*="responses["][value]').forEach(el => {
            if (el.type === 'checkbox') {
                const name = el.name.replace('[]', '');
                if (!document.querySelector(`${name}[]:checked`)) {
                    hasError = true;
                }
            }
        });

        if (hasError) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'f03-error-message';
            errorDiv.textContent = 'Pastikan semua pertanyaan telah dijawab dengan lengkap!';
            errorDiv.innerHTML += '<button onclick="this.parentElement.style.display=\'none\'" style="float:right; background:none; border:none; cursor:pointer; color:#991B1B; font-size:18px;">&times;</button>';
            const progressBar = document.querySelector('.f03-progress-bar');
            progressBar.parentElement.insertBefore(errorDiv, progressBar.nextSibling);
            window.scrollTo(0, 0);
            return;
        }

        const formData = new FormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitText').style.display = 'none';
        document.getElementById('submitLoader').style.display = 'inline-block';

        fetch('{{ route("f03.public.submit", ["token" => $token->token]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => {
            if (res.status === 422) {
                return res.json().then(data => {
                    throw new Error(data.error || data.message || 'Validasi gagal');
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.success || data.message) {
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'f03-success-message';
                successDiv.textContent = data.message || 'Respons Anda telah berhasil dikirim. Terima kasih!';
                document.querySelector('.f03-public-form').parentElement.insertBefore(successDiv, document.querySelector('.f03-public-form'));
                
                // Hide form
                document.getElementById('f03Form').style.display = 'none';
                
                // Scroll to top
                window.scrollTo(0, 0);
            }
        })
        .catch(err => {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'f03-error-message';
            errorDiv.textContent = err.message || 'Terjadi kesalahan saat mengirim respons. Silakan coba lagi.';
            errorDiv.innerHTML += '<button onclick="this.parentElement.style.display=\'none\'" style="float:right; background:none; border:none; cursor:pointer; color:#991B1B; font-size:18px;">&times;</button>';
            
            // Insert after progress bar
            const progressBar = document.querySelector('.f03-progress-bar');
            progressBar.parentElement.insertBefore(errorDiv, progressBar.nextSibling);
            
            window.scrollTo(0, 0);
        })
        .finally(() => {
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitText').style.display = 'inline';
            document.getElementById('submitLoader').style.display = 'none';
        });
    });

    // Initialize progress on page load
    updateProgress();
</script>

@endsection
