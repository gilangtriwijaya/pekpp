{{-- Search Box Component --}}
@php
    $prefix = $prefix ?? 'crud';
    $searchInputId = $searchInputId ?? $prefix . 'SearchInput';
    $tableId = $tableId ?? $prefix . 'Table';
@endphp

<div class="{{ $prefix }}-search-box">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/>
        <path d="M21 21l-4.35-4.35"/>
    </svg>
    <input 
        type="text" 
        id="{{ $searchInputId }}" 
        placeholder="Cari data..." 
        onkeyup="filterTable('{{ $searchInputId }}', '{{ $tableId }}')">
</div>
