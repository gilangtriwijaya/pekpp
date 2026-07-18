<?php

namespace App\Http\Controllers;

use App\Models\PendataanAspek;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendataanAspekController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $periode_id = $request->input('periode_id', $periodes->firstWhere('is_aktif', 1)?->id ?? $periodes->first()?->id);

        $aspeks = PendataanAspek::where('periode_id', $periode_id)
            ->withCount('pertanyaan')
            ->orderBy('urutan', 'asc')
            ->get();

        return view('pendataan.admin.aspek.index', compact('aspeks', 'periodes', 'periode_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:20',
            'urutan' => 'nullable|integer',
            'aktif' => 'boolean',
            'keterangan' => 'nullable|string'
        ]);

        PendataanAspek::create([
            'periode_id' => $request->periode_id,
            'nama' => $request->nama,
            'kode' => $request->kode,
            'urutan' => $request->urutan ?? 0,
            'aktif' => $request->has('aktif'),
            'keterangan' => $request->keterangan
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Aspek berhasil ditambahkan']);
        }
        return redirect()->back()->with('success', 'Aspek berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $aspek = PendataanAspek::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:20',
            'urutan' => 'nullable|integer',
            'aktif' => 'boolean',
            'keterangan' => 'nullable|string'
        ]);

        $aspek->update([
            'nama' => $request->nama,
            'kode' => $request->kode,
            'urutan' => $request->urutan ?? 0,
            'aktif' => $request->has('aktif'),
            'keterangan' => $request->keterangan
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Aspek berhasil diperbarui']);
        }
        return redirect()->back()->with('success', 'Aspek berhasil diperbarui');
    }

    public function destroy($id)
    {
        $aspek = PendataanAspek::findOrFail($id);
        
        if ($aspek->pertanyaan()->count() > 0) {
            return response()->json(['message' => 'Aspek tidak dapat dihapus karena masih memiliki pertanyaan.'], 422);
        }

        $aspek->delete();

        return response()->json(['message' => 'Aspek berhasil dihapus']);
    }
}
