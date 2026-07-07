@extends('layouts.app')

@section('title', 'PEKPPP — Kelola Pengumuman')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Kelola Pengumuman</h1>
            <p class="text-sm text-slate-500 mt-0.5">Buat, perbarui, atau hapus pengumuman untuk dasbor pengguna</p>
        </div>
        <div>
            <a href="{{ route('pengumuman.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-colors text-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pengumuman
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-250 text-emerald-800 rounded-lg p-4 mb-6 flex gap-3 items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <div class="border border-slate-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-550 text-xs font-bold uppercase border-b border-slate-200">
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Tanggal Publish</th>
                        <th class="px-4 py-3">Tanggal Expired</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($pengumuman as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3.5 font-semibold text-slate-850">
                                <div class="max-w-xs truncate" title="{{ $item->judul }}">{{ $item->judul }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 font-mono text-xs">
                                {{ $item->published_at ? $item->published_at->translatedFormat('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 font-mono text-xs">
                                {{ $item->expired_at ? $item->expired_at->translatedFormat('d M Y H:i') : 'Tidak Ada' }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($item->aktif)
                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-250">Aktif</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 text-xs font-bold rounded-full border border-slate-200">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-500">
                                {{ $item->createdBy->nama ?? 'Sistem' }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('pengumuman.edit', $item->id) }}" class="p-1.5 text-slate-500 hover:text-indigo-650 hover:bg-slate-100 rounded-lg transition-all" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('pengumuman.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-650 hover:bg-slate-100 rounded-lg transition-all" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada pengumuman yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pengumuman->hasPages())
        <div class="mt-6">
            {{ $pengumuman->links() }}
        </div>
    @endif
</div>
@endsection
