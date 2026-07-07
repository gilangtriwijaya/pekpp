@if(count($uppDeadlineAlert) > 0)
@php
    $firstItem = $uppDeadlineAlert->first();
    $sisaHari = $firstItem->sisa_hari;
    $count = count($uppDeadlineAlert);
@endphp
<div class="bg-rose-50 border border-rose-200 rounded-xl p-5 mb-6">
    <div class="flex gap-3 items-start mb-4">
        <div class="bg-rose-100 text-rose-600 p-2 rounded-lg shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <h3 class="font-bold text-rose-900 text-base">
                Peringatan Batas Waktu Pengisian!
            </h3>
            <p class="text-rose-800 text-sm mt-0.5 font-medium">
                {{ $count }} UPP belum menyelesaikan pengisian mandiri F01 — sisa waktu {{ $sisaHari }} hari (Batas Akhir: {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d F Y') }})
            </p>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-rose-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-rose-50/50 text-rose-800 text-xs font-bold uppercase border-b border-rose-100">
                        <th class="px-4 py-2.5">Nama UPP</th>
                        <th class="px-4 py-2.5">Status Pengisian</th>
                        <th class="px-4 py-2.5 text-center">Sisa Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-50 text-sm text-slate-700">
                    @foreach($uppDeadlineAlert as $upp)
                        <tr class="hover:bg-rose-50/20 transition-colors">
                            <td class="px-4 py-2.5 font-semibold">{{ $upp->nama_upp }}</td>
                            <td class="px-4 py-2.5">
                                @if($upp->status === 'belum_mulai')
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-650 text-xs font-semibold rounded border border-slate-200 uppercase">Belum Mulai</span>
                                @elseif($upp->status === 'rolled_back')
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 text-xs font-semibold rounded border border-rose-200 uppercase">Dikembalikan</span>
                                @else
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-semibold rounded border border-amber-200 uppercase">Sedang Mengisi</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center font-bold text-rose-600 font-mono">
                                {{ $upp->sisa_hari }} Hari
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
