@if(!$periodeAktif)
<div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center mb-6">
    <p class="text-slate-500 font-medium">Belum ada periode penilaian aktif saat ini.</p>
</div>
@elseif(!$uppTerdaftar && $isAdminUPP)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex gap-3 items-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <p class="text-amber-800 font-medium">UPP Anda belum terdaftar pada periode ini. Hubungi Admin.</p>
</div>
@else
<div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100 rounded-xl p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <div>
            <div class="text-sm text-indigo-600 font-semibold uppercase tracking-wider">Periode Aktif</div>
            <div class="font-bold text-slate-800">{{ $periodeAktif->nama }}</div>
        </div>
    </div>
    <div class="flex gap-4 items-center">
        <div class="text-right hidden md:block">
            <div class="text-xs text-slate-500">Masa Penilaian</div>
            <div class="text-sm font-medium text-slate-700">
                {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d M') }} - 
                {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d M Y') }}
            </div>
        </div>
        <span class="px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full">
            {{ Str::upper($periodeAktif->status) }}
        </span>
    </div>
</div>
@endif
