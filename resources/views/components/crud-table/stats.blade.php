{{-- Stats Cards Component --}}
@php
    $prefix = $prefix ?? 'crud';
    $stats = $stats ?? [];
@endphp

@if(count($stats) > 0)
<div class="{{ $prefix }}-stats-grid">
    @foreach($stats as $stat)
    <div class="{{ $prefix }}-stat-card">
        <div class="{{ $prefix }}-stat-value">{{ $stat['value'] ?? 0 }}</div>
        <div class="{{ $prefix }}-stat-label">{{ $stat['label'] ?? 'Stat' }}</div>
    </div>
    @endforeach
</div>
@endif
