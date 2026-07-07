@extends('layouts.public')
@section('title','Kuesioner F03 - Error')
@section('content')

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F9FAFB; }
    .f03-error-container { max-width: 500px; margin: 100px auto; padding: 30px 20px; }
    .f03-error-card {
        background: white;
        border-radius: 12px;
        padding: 40px 30px;
        text-align: center;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }
    .f03-error-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }
    .f03-error-title {
        font-size: 24px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 15px;
    }
    .f03-error-message {
        font-size: 15px;
        color: #6B7280;
        line-height: 1.6;
        margin-bottom: 30px;
    }
    .f03-error-btn {
        background-color: #667eea;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s;
    }
    .f03-error-btn:hover { background-color: #5568d3; }
</style>

<div class="f03-error-container">
    <div class="f03-error-card">
        <div class="f03-error-icon">{{ $icon ?? '⚠️' }}</div>
        <h1 class="f03-error-title">{{ $title ?? 'Akses Ditolak' }}</h1>
        <p class="f03-error-message">{{ $message ?? 'Token tidak valid atau telah kadaluarsa. Silakan hubungi penyelenggara untuk mendapatkan tautan kuesioner yang benar.' }}</p>
        @if($action_url ?? null)
        <a href="{{ $action_url }}" class="f03-error-btn">{{ $action_text ?? 'Kembali' }}</a>
        @else
        <button onclick="window.history.back()" class="f03-error-btn">Kembali</button>
        @endif
    </div>
</div>

@endsection
