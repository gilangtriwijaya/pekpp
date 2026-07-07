@php
    $hour = now()->format('H');
    if ($hour < 12) {
        $greeting = 'Selamat Pagi';
    } elseif ($hour < 15) {
        $greeting = 'Selamat Siang';
    } elseif ($hour < 18) {
        $greeting = 'Selamat Sore';
    } else {
        $greeting = 'Selamat Malam';
    }
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $greeting }}, {{ $user->nama ?? 'Pengguna' }}!
            </h1>
            <p class="text-slate-500 mt-1">
                {{ $uppName ?? 'Administrator' }}
            </p>
        </div>
        <div class="text-left md:text-right">
            <div class="text-sm font-medium text-slate-500">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>
</div>
