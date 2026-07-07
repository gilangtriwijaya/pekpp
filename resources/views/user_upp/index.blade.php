@extends('layouts.app')
@section('title','Penugasan User UPP')
@section('page_title','Manajemen Penugasan User UPP')

<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="{{ asset('css/admin_user_upp.css') }}?v={{ time() }}">

@include('components.crud-table.css', ['prefix' => 'user_upp'])

<style>
    .user_upp-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    /* Filters */
    .user_upp-filters {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 20px 24px;
        margin-bottom: 32px;
    }
    
    .user_upp-filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        align-items: flex-end;
    }
    
    .user_upp-filters-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .user_upp-filters-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }
    
    .user_upp-filters select,
    .user_upp-filters input {
        padding: 10px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    
    .user_upp-filters select:focus,
    .user_upp-filters input:focus {
        outline: none;
        border-color: #2563eb;
        background: white;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .user_upp-filters input[type="text"] {
        flex: 1;
        min-width: 200px;
    }
    
    /* Table user_upp specific */
    .user_upp-user-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .user_upp-user-name {
        font-size: 14px;
        font-weight: 500;
        color: #0f172a;
    }
    
    .user_upp-user-email {
        font-size: 13px;
        color: #94a3b8;
    }
    
    .user_upp-badge-peran {
        background: #dbeafe;
        color: #0284c7;
    }
    
    @media (max-width: 768px) {
        .user_upp-filters-form {
            grid-template-columns: 1fr;
        }
        
        .user_upp-table th {
            font-size: 11px;
            padding: 10px 12px;
        }
        
        .user_upp-table td {
            padding: 12px;
            font-size: 13px;
        }
    }
</style>

@section('content')

<div class="user_upp-container">
    @include('components.crud-table.header', [
        'prefix' => 'user_upp',
        'title' => 'Penugasan User ke UPP',
        'subtitle' => 'Kelola mapping user SSO sebagai pengguna UPP di PEKPPP',
        'buttonText' => 'Tambah Penugasan',
        'buttonAction' => 'AdminUserUpp.openCreateModal()'
    ])

    @include('components.crud-table.stats', [
        'prefix' => 'user_upp',
        'stats' => [
            ['label' => 'Total Penugasan', 'value' => $items->total()],
            ['label' => 'Penugasan Aktif', 'value' => count(array_filter($items->items(), fn($i) => $i->aktif))]
        ]
    ])

    <!-- Filters -->
    <div class="user_upp-filters">
        <div class="user_upp-filters-title" style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #0f172a;">Filter Data</div>
        <form method="GET" class="user_upp-filters-form">
            <div class="user_upp-filters-group">
                <label class="user_upp-filters-label">UPP</label>
                <select name="upp_id">
                    <option value="">-- Semua UPP --</option>
                    @foreach(($upps ?? []) as $u)
                        <option value="{{ $u->id }}" {{ request('upp_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="user_upp-filters-group">
                <label class="user_upp-filters-label">Peran</label>
                <select name="peran">
                    <option value="">-- Semua Peran --</option>
                    @foreach(\App\Models\UserUpp::allowedPeran() as $r)
                        <option value="{{ $r }}" {{ request('peran') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="user_upp-filters-group">
                <label class="user_upp-filters-label">Cari User</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama atau email..." />
            </div>
            
            <div class="user_upp-filters-group" style="justify-content: flex-end;">
                <button type="submit" class="user_upp-btn user_upp-btn-primary" style="justify-self: start; width: auto;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="user_upp-table-card">
        <div class="user_upp-table-header">
            <h3 class="user_upp-table-title">Daftar Penugasan User</h3>
            <div class="user_upp-table-actions">
                <span style="font-size: 12px; color: #94a3b8;">
                    Total {{ $items->total() }} penugasan
                </span>
            </div>
        </div>
        
        <div class="user_upp-table-wrapper">
            <table class="user_upp-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>UPP</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th style="text-align: center;">Ditetapkan Oleh</th>
                        <th style="text-align: center;">Tanggal</th>
                        <th style="text-align: center; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-upp-rows">
                    @forelse(($items ?? []) as $it)
                        @include('user_upp._row', ['it' => $it])
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px 20px; color: #94a3b8;">
                                Belum ada penugasan user
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @include('components.crud-table.pagination', [
        'prefix' => 'user_upp',
        'data' => $items
    ])
</div>

@push('scripts')
<script>
    window.AdminUserUppConfig = {
        users: @json($users),
        upps: @json($upps),
        roles: @json(\App\Models\UserUpp::allowedPeran()),
        urls: {
            base: @json(url('/user-upp')),
            store: @json(url('/user-upp'))
        }
    };
    
    // Flash messages
    document.addEventListener('DOMContentLoaded', function(){
        @if(session('success'))
            window.__admin_user_upp_flash = {type:'success', message: @json(session('success')) };
        @endif
        @if(session('error'))
            window.__admin_user_upp_flash = {type:'error', message: @json(session('error')) };
        @endif
    });
</script>
<script src="{{ asset('js/admin_user_upp.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){ 
        if (window.__admin_user_upp_flash) { 
            window.AdminUserUpp && window.AdminUserUpp.showToast(window.__admin_user_upp_flash.type, window.__admin_user_upp_flash.message); 
        }
    });
</script>
@endpush

@endsection
