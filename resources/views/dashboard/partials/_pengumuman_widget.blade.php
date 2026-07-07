@if(isset($pengumuman) && $pengumuman)
<div class="bg-blue-50 rounded-xl border border-blue-200 p-5 mb-6 flex gap-4 items-start" x-data="{ expanded: false }">
    <div class="bg-blue-100 p-2.5 rounded-lg text-blue-600 shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
        </svg>
    </div>
    <div class="flex-1">
        <h3 class="font-bold text-blue-900 text-lg mb-1">{{ $pengumuman->judul }}</h3>
        <div class="text-blue-800 text-sm">
            <div x-show="!expanded" class="line-clamp-2">
                {{ $pengumuman->isi }}
            </div>
            <div x-show="expanded" class="whitespace-pre-wrap" x-cloak>
                {{ $pengumuman->isi }}
            </div>
        </div>
        @if(strlen($pengumuman->isi) > 150)
        <button @click="expanded = !expanded" class="text-blue-600 hover:text-blue-800 text-sm font-semibold mt-2 focus:outline-none">
            <span x-show="!expanded">Baca Selengkapnya</span>
            <span x-show="expanded" x-cloak>Tutup</span>
        </button>
        @endif
    </div>
</div>
@endif
