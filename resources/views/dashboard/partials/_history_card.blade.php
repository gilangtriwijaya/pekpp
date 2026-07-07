@if($periodeSebelumnya)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6" x-data="{ showModal: false, activeTab: 0 }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Riwayat Penilaian Terakhir</h2>
            <p class="text-sm text-slate-500 mt-0.5">Evaluasi kinerja pelayanan publik UPP pada periode sebelumnya</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 p-5 bg-slate-50 border border-slate-200 rounded-xl">
        <div class="flex items-center gap-4">
            <div class="bg-indigo-100 p-3 rounded-xl text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Periode Penilaian</div>
                <div class="text-lg font-bold text-slate-800">{{ $periodeSebelumnya['nama'] }}</div>
            </div>
        </div>

        <div class="flex items-center gap-8">
            <div>
                <div class="text-xs text-slate-500 font-medium">Nilai IPP</div>
                <div class="flex items-baseline gap-1.5 mt-0.5">
                    <span class="text-2xl font-black text-slate-800">{{ number_format($periodeSebelumnya['nilai_ipp'], 2) }}</span>
                    <span class="text-slate-400 text-xs font-medium">/ 5.00</span>
                </div>
            </div>

            <div>
                <div class="text-xs text-slate-500 font-medium">Predikat</div>
                <div class="mt-1">
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-md" style="background-color: {{ $periodeSebelumnya['predikat_bg'] }}; color: {{ $periodeSebelumnya['predikat_color'] }}">
                        {{ $periodeSebelumnya['predikat'] }}
                    </span>
                </div>
            </div>

            @if($deltaNilai !== null)
            <div>
                <div class="text-xs text-slate-500 font-medium">Perubahan</div>
                <div class="flex items-center gap-1 mt-1">
                    @if($deltaNilai > 0)
                        <span class="flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-8.293 8.293a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L7 14.586 14.586 7H12z" clip-rule="evenodd" />
                            </svg>
                            +{{ number_format($deltaNilai, 2) }}
                        </span>
                    @elseif($deltaNilai < 0)
                        <span class="flex items-center text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-8.293-8.293a1 1 0 00-1.414 0l-4 4a1 1 0 001.414 1.414L7 5.414 14.586 13H12z" clip-rule="evenodd" />
                            </svg>
                            {{ number_format($deltaNilai, 2) }}
                        </span>
                    @else
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                            Tetap
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div>
            <button @click="showModal = true" class="inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-lg shadow-sm border border-slate-200 transition-colors text-sm gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Lihat Detail Isian
            </button>
        </div>
    </div>

    <!-- Modal Detail Isian -->
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
         x-show="showModal" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         x-cloak>
        
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col"
             @click.away="showModal = false">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-lg font-bold">Rincian Nilai Periode: {{ $periodeSebelumnya['nama'] }}</h3>
                    <p class="text-indigo-100 text-xs mt-0.5">Nilai per aspek dan indikator pelayanan</p>
                </div>
                <button @click="showModal = false" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content (Tabbed) -->
            <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                <!-- Tabs Left Panel -->
                <div class="w-full md:w-64 bg-slate-50 border-r border-slate-200 overflow-y-auto shrink-0 p-3 space-y-1">
                    @foreach($periodeSebelumnya['aspeks'] as $idx => $aspek)
                        <button @click="activeTab = {{ $idx }}" 
                                class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-semibold transition-all flex justify-between items-center"
                                :class="activeTab === {{ $idx }} ? 'bg-indigo-50 text-indigo-700 shadow-sm border-l-4 border-indigo-600' : 'text-slate-600 hover:bg-slate-100'">
                            <span class="truncate pr-2">{{ $aspek['nama'] }}</span>
                            <span class="text-xs font-bold font-mono px-1.5 py-0.5 bg-slate-200 text-slate-700 rounded select-none shrink-0"
                                  :class="activeTab === {{ $idx }} ? 'bg-indigo-100 text-indigo-800' : ''">
                                {{ number_format($aspek['skor'], 2) }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <!-- Tab Panels Right Panel -->
                <div class="flex-1 overflow-y-auto p-6 bg-white">
                    @foreach($periodeSebelumnya['aspeks'] as $idx => $aspek)
                        <div x-show="activeTab === {{ $idx }}" class="space-y-4">
                            <div class="border-b border-slate-100 pb-3">
                                <h4 class="text-base font-bold text-slate-800">{{ $aspek['nama'] }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-slate-500 font-medium">Skor rata-rata aspek:</span>
                                    <span class="text-sm font-bold text-indigo-600 font-mono">{{ number_format($aspek['skor'], 2) }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $aspek['predikat'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @forelse($aspek['indikators'] as $ind)
                                    <div class="py-3 flex justify-between items-start gap-4">
                                        <div class="text-sm text-slate-700 font-medium">
                                            {{ $ind['nama'] }}
                                        </div>
                                        <div class="text-right shrink-0 flex items-center gap-3">
                                            <span class="text-sm font-bold text-slate-800 font-mono">{{ number_format($ind['skor'], 2) }}</span>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md" style="background-color: {{ App\Http\Controllers\DashboardController::getPredikatData($ind['skor'])['bg'] }}; color: {{ App\Http\Controllers\DashboardController::getPredikatData($ind['skor'])['color'] }}">
                                                {{ $ind['predikat'] }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400 text-center py-6">Tidak ada indikator dalam aspek ini.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
                <button @click="showModal = false" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition-colors">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 text-center">
    <div class="py-6 max-w-md mx-auto">
        <div class="bg-slate-50 text-slate-400 p-3 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-4 border border-slate-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-sm font-bold text-slate-800">Belum Ada Riwayat Penilaian</h3>
        <p class="text-xs text-slate-500 mt-1">Belum ada riwayat penilaian sebelumnya untuk UPP Anda.</p>
    </div>
</div>
@endif
