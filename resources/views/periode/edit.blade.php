@extends('layouts.app')

@section('content')
<x-panel>
    <div class="py-6">
        <h2 class="text-lg font-semibold">Halaman ini dinonaktifkan</h2>
        <p class="mt-2 text-sm text-gray-600">Form edit Periode sekarang disediakan melalui modal pada halaman daftar Periode. Silakan kembali ke daftar untuk mengedit.</p>
        <div class="mt-4">
            <a href="{{ route('admin.periode.index') }}" class="btn btn-secondary">Kembali ke Daftar Periode</a>
        </div>
    </div>
</x-panel>
@endsection
