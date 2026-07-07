@extends('layouts.app')

@section('title', 'PEKPPP — Dashboard')

@section('content')
    @include('dashboard.partials._header_greeting')
    @include('dashboard.partials._pengumuman_widget')
    @include('dashboard.partials._periode_banner')

    @if($isAdminUPP)
        @include('dashboard.partials._progress_upp')
        @include('dashboard.partials._hasil_penilaian_upp')
        @include('dashboard.partials._radar_chart_upp')
        @include('dashboard.partials._history_card')
    @else
        @include('dashboard.partials._summary_cards_internal')
        @include('dashboard.partials._progress_chart_internal')
        @include('dashboard.partials._deadline_alert')
    @endif
@endsection
