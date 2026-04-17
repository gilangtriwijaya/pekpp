@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Analisis — Panel</h1>
    <p>Ini skeleton halaman Analisis. Komponen Livewire `AnalyticsPanel` akan mengisi konten.</p>
    <div>
        @if (class_exists(\Livewire\Livewire::class))
            @livewire('analytics.panel')
        @else
            <p>Livewire tidak terdeteksi. Install Livewire untuk interaktivitas.</p>
        @endif
    </div>
</div>
@endsection
