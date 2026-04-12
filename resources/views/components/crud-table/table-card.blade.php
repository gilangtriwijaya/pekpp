{{-- Table Card Wrapper Component --}}
@php
    $prefix = $prefix ?? 'crud';
    $tableTitle = $tableTitle ?? 'Daftar Data';
    $tableId = $tableId ?? $prefix . 'Table';
@endphp

<div class="{{ $prefix }}-table-card">
    <div class="{{ $prefix }}-table-header">
        <h2 class="{{ $prefix }}-table-title">{{ $tableTitle }}</h2>
    </div>

    <div class="{{ $prefix }}-table-wrapper">
