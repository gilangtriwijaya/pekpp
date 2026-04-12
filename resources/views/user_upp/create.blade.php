@extends('layouts.app')

@section('content')
<x-panel>
  <div class="flex items-start justify-between mb-3">
    <div>
      <div class="text-lg font-semibold">Halaman Tidak Digunakan</div>
      <div class="text-sm text-gray-500">Form pembuatan sekarang dilakukan melalui modal di halaman Manajemen user_upp.</div>
    </div>
  </div>

  <div class="text-sm text-gray-700">
    <p>Gunakan tombol "Tambah Penugasan" di <a href="{{ route('user_upp.index') }}" class="text-blue-600 hover:underline">halaman Manajemen user_upp</a>.</p>
  </div>
</x-panel>
@endsection
