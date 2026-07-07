<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PEKPPP')</title>

    {{-- x-cloak: hide Alpine elements until Alpine initializes --}}
    <style>[x-cloak] { display: none !important; }</style>

    @vite(['resources/js/app.js'])

        {{-- NOTE: legacy static assets in `public/css` and `public/js` were previously loaded here.
            Automatic inclusion has been disabled to avoid CSS/JS conflicts with the new namespaced UI.
            Legacy files were migrated or removed; keep asset loading explicit if you need a rollback.
        --}}

    <link rel="icon" href="{{ asset('images/logo-pemda.png') }}" type="image/png">

    {{-- Livewire Styles --}}
    @livewireStyles

    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- Alpine.js v3 via CDN (defer ensures DOM is ready before init) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    {{-- Stack for page-specific styles --}}
    @stack('styles')
</head>

<body class="pekppp-ui min-h-screen bg-slate-50 text-slate-800 font-sans">
<div class="pekppp-layout flex min-h-screen">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Main Area --}}
    <div class="pekppp-main flex flex-col flex-1">

        {{-- Topbar --}}
            @include('partials.topbar')


        {{-- Content --}}
        <main class="pekppp-content flex-1 overflow-y-auto">
            <div class="max-w-7xl p-6 pl-3 pr-4 mr-6">
                @yield('content')
            </div>
        </main>

    </div>
</div>

{{-- Modals rendered at body level (for position: fixed) --}}
@stack('modals')

{{-- Session Timeout Warning (only for authenticated users) --}}
@auth
    @include('components.session-timeout-warning')
@endauth

{{-- Impersonate Idle Timeout (only when impersonating) --}}
@include('components.impersonate-idle-timeout')

{{-- Livewire Scripts --}}
@livewireScripts


@stack('scripts')
</body>
</html>

