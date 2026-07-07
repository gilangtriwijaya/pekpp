<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Progres Pengisian Self-Assessment (F01)</h2>
            <p class="text-sm text-slate-500 mt-0.5">Pantau kesiapan data pendukung dan isian evaluasi Anda</p>
        </div>
        <div>
            @if($statusPengisian === 'belum_mulai')
                <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-full border border-slate-200 uppercase">Belum Mulai</span>
            @elseif($statusPengisian === 'sedang_mengisi')
                <span class="px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200 uppercase">Sedang Mengisi</span>
            @elseif($statusPengisian === 'menunggu_validasi')
                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full border border-blue-200 uppercase">Menunggu Validasi</span>
            @elseif($statusPengisian === 'selesai')
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200 uppercase">Selesai</span>
            @endif
        </div>
    </div>

    <div class="space-y-4 mb-6">
        @foreach($progressPerAspek as $aspek)
            @php
                $percent = $aspek->total > 0 ? round(($aspek->terisi / $aspek->total) * 100) : 0;
            @endphp
            <div>
                <div class="flex justify-between text-sm font-medium text-slate-700 mb-1.5">
                    <span class="truncate max-w-[70%]">{{ $aspek->nama }}</span>
                    <span class="text-slate-500 font-mono">{{ $aspek->terisi }}/{{ $aspek->total }} Indikator ({{ $percent }}%)</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $percent === 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}" style="width: {{ $percent }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end">
        @if($statusPengisian === 'belum_mulai')
            <a href="{{ $urlPengisian }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-colors text-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Mulai Pengisian
            </a>
        @elseif($statusPengisian === 'sedang_mengisi')
            <a href="{{ $urlPengisian }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-colors text-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Lanjutkan Pengisian
            </a>
        @elseif($statusPengisian === 'menunggu_validasi')
            <button disabled class="inline-flex items-center justify-center px-5 py-2.5 bg-slate-100 text-slate-400 font-semibold rounded-lg text-sm gap-2 cursor-not-allowed border border-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Menunggu Validasi
            </button>
        @elseif($statusPengisian === 'selesai')
            <div class="flex items-center gap-2 text-emerald-600 bg-emerald-50 px-4 py-2 rounded-lg border border-emerald-100 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Pengisian Selesai & Divalidasi
            </div>
        @endif
    </div>
</div>
