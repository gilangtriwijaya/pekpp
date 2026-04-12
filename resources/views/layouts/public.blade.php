<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PEKPPP')</title>

    @vite(['resources/js/app.js'])

    <link rel="icon" href="{{ asset('images/logo-pemda.png') }}" type="image/png">
    
    {{-- Stack for page-specific styles --}}
    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 font-sans">
    <div class="min-h-screen flex flex-col">
        {{-- Content (Full Width) --}}
        <main class="flex-1 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    {{-- Stack for page-specific scripts --}}
    @stack('scripts')
</body>
</html>
