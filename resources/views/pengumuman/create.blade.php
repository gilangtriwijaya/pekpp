@extends('layouts.app')

@section('title', 'PEKPPP — Tambah Pengumuman')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Buat Pengumuman Baru</h1>
        <p class="text-sm text-slate-500 mt-0.5">Lengkapi formulir di bawah ini untuk memublikasikan informasi baru di dashboard</p>
    </div>

    <form action="{{ route('pengumuman.store') }}" method="POST">
        @csrf

        <div class="space-y-5">
            <!-- Judul -->
            <div>
                <label for="judul" class="block text-sm font-bold text-slate-700 mb-2">Judul Pengumuman</label>
                <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-350 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-slate-800 transition-all @error('judul') border-rose-500 focus:border-rose-500 @enderror"
                       placeholder="Contoh: Jadwal Pengisian Form Mandiri F01 2026">
                @error('judul')
                    <p class="text-rose-600 text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Isi -->
            <div>
                <label for="isi" class="block text-sm font-bold text-slate-700 mb-2">Isi Pengumuman</label>
                <textarea name="isi" id="isi" rows="6" required
                          class="w-full px-4 py-2.5 rounded-lg border border-slate-350 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-slate-800 transition-all @error('isi') border-rose-500 focus:border-rose-500 @enderror"
                          placeholder="Masukkan teks pengumuman secara detail...">{{ old('isi') }}</textarea>
                @error('isi')
                    <p class="text-rose-600 text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Published At -->
                <div>
                    <label for="published_at" class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai Tampil (Publish)</label>
                    <input type="datetime-local" name="published_at" id="published_at" 
                           value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-350 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-slate-800 transition-all @error('published_at') border-rose-500 focus:border-rose-500 @enderror">
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan/biarkan default jika ingin langsung ditampilkan</p>
                    @error('published_at')
                        <p class="text-rose-600 text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expired At -->
                <div>
                    <label for="expired_at" class="block text-sm font-bold text-slate-700 mb-2">Tanggal Berakhir (Expired)</label>
                    <input type="datetime-local" name="expired_at" id="expired_at" 
                           value="{{ old('expired_at') }}"
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-350 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none text-slate-800 transition-all @error('expired_at') border-rose-500 focus:border-rose-500 @enderror">
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika pengumuman tidak memiliki batas kadaluwarsa</p>
                    @error('expired_at')
                        <p class="text-rose-600 text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-lg p-4">
                <input type="checkbox" name="aktif" id="aktif" value="1" checked
                       class="form-checkbox h-5 w-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer">
                <div>
                    <label for="aktif" class="block text-sm font-bold text-slate-800 cursor-pointer select-none">Aktifkan Pengumuman</label>
                    <span class="text-xs text-slate-500">Centang agar pengumuman langsung aktif di dasbor jika sudah memasuki masa tampil</span>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
            <a href="{{ route('pengumuman.index') }}" class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-lg border border-slate-200 transition-colors text-sm">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-colors text-sm">
                Simpan Pengumuman
            </button>
        </div>
    </form>
</div>
@endsection
