{{-- Pagination Component --}}
@php
    $prefix = $prefix ?? 'crud';
    $data = $data ?? null;
    $showInfo = $showInfo ?? true;
@endphp

<div class="{{ $prefix }}-table-footer">
    @if($showInfo && $data)
    <div class="pagination-info">
        Menampilkan <strong>{{ $data->count() }}</strong> dari <strong>{{ $data->total() }}</strong> data
    </div>
    @endif
    @if($data && $data->hasPages())
    <div class="pagination">
        {{ $data->links('pagination::simple-bootstrap-4') }}
    </div>
    @endif
</div>
