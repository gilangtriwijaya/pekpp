{{-- Page Header Component --}}
@php
    $prefix = $prefix ?? 'crud';
    $title = $title ?? 'Data';
    $subtitle = $subtitle ?? '';
    $buttonText = $buttonText ?? 'Tambah Data';
    $buttonAction = $buttonAction ?? 'openCreateModal()';
@endphp

<div class="{{ $prefix }}-header">
    <div>
        <h1 class="{{ $prefix }}-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="{{ $prefix }}-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    <button class="{{ $prefix }}-btn {{ $prefix }}-btn-primary" onclick="{{ $buttonAction }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        {{ $buttonText }}
    </button>
</div>
