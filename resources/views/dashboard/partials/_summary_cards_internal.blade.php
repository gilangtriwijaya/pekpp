<div x-data="{ activeTab: null }" class="mb-6">
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total UPP Card (No Interactive Table Panel) -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total UPP</div>
            <div class="text-3xl font-black text-slate-800 mt-2">{{ $summaryCards['total']['count'] }}</div>
            <p class="text-[10px] text-slate-400 mt-1">Seluruh UPP terdaftar aktif</p>
        </div>

        <!-- Belum Mulai Card -->
        <button @click="activeTab = activeTab === 'belum_mulai' ? null : 'belum_mulai'"
                class="text-left bg-white border rounded-xl p-4 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                :class="activeTab === 'belum_mulai' ? 'border-indigo-600 ring-2 ring-indigo-500/10 bg-indigo-50/10' : 'border-slate-200 hover:border-slate-300'">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider flex justify-between items-center">
                <span>Belum Mulai</span>
                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
            </div>
            <div class="text-3xl font-black text-slate-800 mt-2">{{ $summaryCards['belum_mulai']['count'] }}</div>
            <p class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Klik untuk detail</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform" :class="activeTab === 'belum_mulai' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </p>
        </button>

        <!-- Sedang Mengisi Card -->
        <button @click="activeTab = activeTab === 'sedang_mengisi' ? null : 'sedang_mengisi'"
                class="text-left bg-white border rounded-xl p-4 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                :class="activeTab === 'sedang_mengisi' ? 'border-amber-500 ring-2 ring-amber-500/10 bg-amber-50/10' : 'border-slate-200 hover:border-slate-300'">
            <div class="text-xs font-bold text-amber-600 uppercase tracking-wider flex justify-between items-center">
                <span>Sedang Mengisi</span>
                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
            </div>
            <div class="text-3xl font-black text-slate-800 mt-2">{{ $summaryCards['sedang_mengisi']['count'] }}</div>
            <p class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Klik untuk detail</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform" :class="activeTab === 'sedang_mengisi' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </p>
        </button>

        <!-- Menunggu Validasi Card -->
        <button @click="activeTab = activeTab === 'menunggu_validasi' ? null : 'menunggu_validasi'"
                class="text-left bg-white border rounded-xl p-4 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                :class="activeTab === 'menunggu_validasi' ? 'border-blue-500 ring-2 ring-blue-500/10 bg-blue-50/10' : 'border-slate-200 hover:border-slate-300'">
            <div class="text-xs font-bold text-blue-600 uppercase tracking-wider flex justify-between items-center">
                <span>Menunggu Validasi</span>
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
            </div>
            <div class="text-3xl font-black text-slate-800 mt-2">{{ $summaryCards['menunggu_validasi']['count'] }}</div>
            <p class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Klik untuk detail</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform" :class="activeTab === 'menunggu_validasi' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </p>
        </button>

        <!-- Selesai Card -->
        <button @click="activeTab = activeTab === 'selesai' ? null : 'selesai'"
                class="text-left bg-white border rounded-xl p-4 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                :class="activeTab === 'selesai' ? 'border-emerald-500 ring-2 ring-emerald-500/10 bg-emerald-50/10' : 'border-slate-200 hover:border-slate-300'">
            <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider flex justify-between items-center">
                <span>Selesai</span>
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
            </div>
            <div class="text-3xl font-black text-slate-800 mt-2">{{ $summaryCards['selesai']['count'] }}</div>
            <p class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Klik untuk detail</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform" :class="activeTab === 'selesai' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </p>
        </button>
    </div>

    <!-- Details Table Panel -->
    <div x-show="activeTab !== null"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-y-95 origin-top"
         x-transition:enter-end="opacity-100 transform scale-y-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-y-100"
         x-transition:leave-end="opacity-0 transform scale-y-95 origin-top"
         class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mt-5"
         x-cloak>

        <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full"
                      :class="activeTab === 'belum_mulai' ? 'bg-slate-400' :
                              activeTab === 'sedang_mengisi' ? 'bg-amber-500' :
                              activeTab === 'menunggu_validasi' ? 'bg-blue-500' : 'bg-emerald-500'"></span>
                <span>Daftar UPP — </span>
                <span class="capitalize text-slate-500" x-text="activeTab ? activeTab.replace('_', ' ') : ''"></span>
            </h3>
            <button @click="activeTab = null" class="text-slate-400 hover:text-slate-600 text-xs font-semibold">Tutup Panel</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs font-bold uppercase border-b border-slate-200">
                        <th class="px-4 py-3">Nama UPP</th>
                        <th class="px-4 py-3">Nama Pengelola (Admin UPP)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>

                {{-- ============ BELUM MULAI ============ --}}
                <tbody x-show="activeTab === 'belum_mulai'" class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($summaryCards['belum_mulai']['list'] as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3.5 font-semibold text-slate-800">{{ $entry->nama_upp }}</td>
                            <td class="px-4 py-3.5 text-slate-500">{{ $entry->user_nama }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full border border-slate-200">Belum Mulai</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada UPP dengan status ini.</td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- ============ SEDANG MENGISI ============ --}}
                <tbody x-show="activeTab === 'sedang_mengisi'" class="divide-y divide-slate-100 text-sm text-slate-700" style="display: none;">
                    @forelse($summaryCards['sedang_mengisi']['list'] as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3.5 font-semibold text-slate-800">{{ $entry->nama_upp }}</td>
                            <td class="px-4 py-3.5 text-slate-500">{{ $entry->user_nama }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($entry->f01_status === 'rolled_back')
                                    <span class="px-2.5 py-0.5 bg-rose-50 text-rose-700 text-xs font-semibold rounded-full border border-rose-100">Dikembalikan</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 text-xs font-semibold rounded-full border border-amber-200">Sedang Mengisi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada UPP dengan status ini.</td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- ============ MENUNGGU VALIDASI ============ --}}
                <tbody x-show="activeTab === 'menunggu_validasi'" class="divide-y divide-slate-100 text-sm text-slate-700" style="display: none;">
                    @forelse($summaryCards['menunggu_validasi']['list'] as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3.5 font-semibold text-slate-800">
                                @if($entry->f01)
                                    <a href="{{ route('f02.aspek-list', $entry->f01->id) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">
                                        {{ $entry->nama_upp }}
                                    </a>
                                @else
                                    {{ $entry->nama_upp }}
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-500">{{ $entry->user_nama }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full border border-blue-200">Menunggu Validasi</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada UPP dengan status ini.</td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- ============ SELESAI ============ --}}
                <tbody x-show="activeTab === 'selesai'" class="divide-y divide-slate-100 text-sm text-slate-700" style="display: none;">
                    @forelse($summaryCards['selesai']['list'] as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3.5 font-semibold text-slate-800">{{ $entry->nama_upp }}</td>
                            <td class="px-4 py-3.5 text-slate-500">{{ $entry->user_nama }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-200">Selesai (F02 Tervalidasi)</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada UPP dengan status ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
