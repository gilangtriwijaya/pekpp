@extends('layouts.auth')

@section('title', 'Sesi Anda Telah Berakhir')

@section('content')
    {{-- Icon --}}
    <div style="font-size: 64px; margin-bottom: 20px; text-align: center;">⏱️</div>

    {{-- Heading --}}
    <h1 style="font-size: 28px; font-weight: 700; color: #1f2937; margin-bottom: 10px; text-align: center;">
        Sesi Anda Telah Berakhir
    </h1>

    {{-- Subtitle --}}
    <p style="color: #6b7280; font-size: 16px; line-height: 1.6; margin-bottom: 30px; text-align: center;">
        Karena tidak ada aktivitas selama {{ config('session.lifetime') }} menit, sesi Anda otomatis berakhir untuk keamanan.
    </p>

    {{-- Info Box --}}
    <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px; margin-bottom: 30px;">
        <p style="color: #92400e; font-size: 14px; margin: 0;">
            <strong>💡 Informasi:</strong> Ini adalah fitur keamanan untuk melindungi data Anda. Silakan login kembali untuk melanjutkan.
        </p>
    </div>

    {{-- Countdown Timer --}}
    <div style="background: #e0f2fe; border-left: 4px solid #0284c7; padding: 16px; border-radius: 8px; margin-bottom: 30px; text-align: center;">
        <p style="color: #0c4a6e; font-size: 14px; margin: 0;">
            ⏳ Halaman akan dialihkan ke dashboard SSO dalam <span id="countdown" style="font-weight: 700;">5</span> detik...
        </p>
    </div>

    {{-- Buttons --}}
    <div style="display: flex; gap: 12px; flex-direction: column;">
        <button id="loginBtn" type="button" style="
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            🔐 Login Ulang Sekarang
        </button>

        <a href="{{ config('services.sso.home_url', '') ?: config('services.sso.base_url', '') }}" style="
            display: block;
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
        " onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
            🏠 Kembali ke Beranda SSO
        </a>
    </div>

    {{-- Footer --}}
    <p style="color: #9ca3af; font-size: 12px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px; text-align: center;">
        Butuh bantuan? <a href="mailto:support@example.com" style="color: #667eea; text-decoration: none;">Hubungi Tim Dukungan</a>
    </p>
@endsection

@section('scripts')
    <script>
        // Auto-redirect configuration
        const SSO_BASE_URL = "{{ config('services.sso.home_url', '') ?: config('services.sso.base_url', '') }}";
        let countdownSeconds = 5;

        // Countdown timer
        const countdownElement = document.getElementById('countdown');
        const loginBtn = document.getElementById('loginBtn');

        const countdownInterval = setInterval(() => {
            countdownSeconds--;
            countdownElement.textContent = countdownSeconds;

            if (countdownSeconds <= 0) {
                clearInterval(countdownInterval);
                // Auto-redirect to SSO
                if (SSO_BASE_URL) {
                    window.location.href = SSO_BASE_URL;
                } else {
                    // Fallback to sistagor dashboard
                    window.location.href = 'https://sistagor.anambaskab.go.id';
                }
            }
        }, 1000);

        // Manual login button
        loginBtn.addEventListener('click', function() {
            clearInterval(countdownInterval);
            window.location.href = "{{ route('sso.login') }}";
        });
    </script>
@endsection

