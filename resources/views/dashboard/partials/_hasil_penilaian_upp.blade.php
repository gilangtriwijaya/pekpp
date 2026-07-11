@if($hasilPenilaian)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="mb-6">
        <h2 class="text-lg font-bold text-slate-800">Hasil Penilaian Periode Ini</h2>
        <p class="text-sm text-slate-500 mt-0.5">Nilai Indeks Pelayanan Publik (IPP) UPP Anda berdasarkan validasi dan kuesioner</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- F02 Score Card -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-slate-500">Skor F02 (Validasi)</span>
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-md border border-indigo-100 uppercase">Bobot 75%</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">
                    {{ $hasilPenilaian->nilai_f02 !== null ? number_format($hasilPenilaian->nilai_f02, 2) : '-' }}
                </span>
                <span class="text-slate-400 text-sm font-medium">/ 5.00</span>
            </div>
            @if(isset($hasilPenilaian->nilai_f02_sebelumnya) && $hasilPenilaian->nilai_f02_sebelumnya !== null)
            <div class="mt-1 flex items-center gap-1 text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 w-max">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                <span>Skor Terakhir : <strong>{{ number_format($hasilPenilaian->nilai_f02_sebelumnya, 2) }}</strong></span>
            </div>
            @endif
            <p class="text-xs text-slate-400 mt-2">Skor rata-rata aspek terbobot dari validator</p>
        </div>

        <!-- F03 Score Card -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-slate-500">Skor F03 (Kuesioner)</span>
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-md border border-indigo-100 uppercase">Bobot 25%</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">
                    {{ $hasilPenilaian->nilai_f03 !== null ? number_format($hasilPenilaian->nilai_f03, 2) : '-' }}
                </span>
                <span class="text-slate-400 text-sm font-medium">/ 5.00</span>
            </div>
            <p class="text-xs text-slate-400 mt-2">Rata-rata penilaian survei kepuasan masyarakat</p>
        </div>

        <!-- Combined IPP Score Card -->
        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold text-indigo-950">Nilai IPP</span>
                <span class="px-2.5 py-0.5 bg-indigo-600 text-white text-xs font-bold rounded-md uppercase">Indeks Akhir</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-indigo-900">
                    {{ number_format($hasilPenilaian->nilai_ipp, 2) }}
                </span>
                <span class="text-indigo-600 text-sm font-semibold">/ 5.00</span>
            </div>
            <div class="mt-2.5">
                <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-md" style="background-color: {{ $hasilPenilaian->predikat_bg }}; color: {{ $hasilPenilaian->predikat_color }}; border: 1px solid {{ $hasilPenilaian->predikat_color }}20">
                    {{ $hasilPenilaian->predikat }}
                </span>
            </div>
            
            <div class="mt-4 pt-3 border-t border-indigo-200/60 flex flex-col gap-1.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-indigo-800/80">Kontribusi F02 (75%)</span>
                    <span class="font-bold text-indigo-900">{{ number_format(($hasilPenilaian->nilai_f02 ?? 0) * 0.75, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-indigo-800/80">Kontribusi F03 (25%)</span>
                    <span class="font-bold text-indigo-900">{{ number_format(($hasilPenilaian->nilai_f03 ?? 0) * 0.25, 2) }}</span>
                </div>
            </div>
            @if(empty($hasilPenilaian->nilai_f02) || empty($hasilPenilaian->nilai_f03))
            <div class="mt-3 text-indigo-800/70 leading-tight italic" style="font-size: 10px;">
                *Nilai IPP saat ini belum final karena skor F02 dan/atau F03 masih kosong/belum tersedia.
            </div>
            @endif
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-100 rounded-lg p-4 flex gap-3 items-start">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-xs text-amber-800 leading-relaxed">
            <strong>Catatan:</strong> Pengisian mandiri F01 tidak memiliki kontribusi skor langsung ke nilai IPP. Nilai IPP Anda dihitung dari kontribusi validasi F02 (75%) oleh validator internal dan kuesioner kepuasan F03 (25%).
        </p>
    </div>
</div>
@endif
