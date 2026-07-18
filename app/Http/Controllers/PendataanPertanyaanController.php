<?php

namespace App\Http\Controllers;

use App\Models\PendataanPertanyaan;
use App\Models\PendataanAspek;
use App\Models\Periode;
use Illuminate\Http\Request;

class PendataanPertanyaanController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $periode_id = $request->input('periode_id', $periodes->firstWhere('is_aktif', 1)?->id ?? $periodes->first()?->id);
        $aspek_id = $request->input('aspek_id');

        $aspeks = PendataanAspek::where('periode_id', $periode_id)->orderBy('urutan', 'asc')->get();
        
        $query = PendataanPertanyaan::with('aspek')->whereHas('aspek', function($q) use ($periode_id) {
            $q->where('periode_id', $periode_id);
        });

        if ($aspek_id) {
            $query->where('pendataan_aspek_id', $aspek_id);
        }

        $pertanyaan = $query->orderBy('pendataan_aspek_id')->orderBy('urutan', 'asc')->get();

        return view('pendataan.admin.pertanyaan.index', compact('pertanyaan', 'periodes', 'periode_id', 'aspeks', 'aspek_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pendataan_aspek_id' => 'required|exists:pendataan_aspek,id',
            'label' => 'required|string',
            'tipe_input' => 'required|string',
            'opsi_jawaban' => 'nullable|json',
            'urutan' => 'nullable|integer',
            'kode' => 'nullable|string|max:20',
        ]);

        PendataanPertanyaan::create([
            'pendataan_aspek_id' => $request->pendataan_aspek_id,
            'label' => $request->label,
            'tipe_input' => $request->tipe_input,
            'opsi_jawaban' => $request->opsi_jawaban,
            'wajib' => $request->has('wajib'),
            'aktif' => $request->has('aktif'),
            'urutan' => $request->urutan ?? 0,
            'kode' => $request->kode,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pertanyaan berhasil ditambahkan']);
        }
        return redirect()->back()->with('success', 'Pertanyaan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $pertanyaan = PendataanPertanyaan::findOrFail($id);
        
        $request->validate([
            'pendataan_aspek_id' => 'required|exists:pendataan_aspek,id',
            'label' => 'required|string',
            'tipe_input' => 'required|string',
            'opsi_jawaban' => 'nullable|json',
            'urutan' => 'nullable|integer',
            'kode' => 'nullable|string|max:20',
        ]);

        $pertanyaan->update([
            'pendataan_aspek_id' => $request->pendataan_aspek_id,
            'label' => $request->label,
            'tipe_input' => $request->tipe_input,
            'opsi_jawaban' => $request->opsi_jawaban,
            'wajib' => $request->has('wajib'),
            'aktif' => $request->has('aktif'),
            'urutan' => $request->urutan ?? 0,
            'kode' => $request->kode,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pertanyaan berhasil diperbarui']);
        }
        return redirect()->back()->with('success', 'Pertanyaan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $pertanyaan = PendataanPertanyaan::findOrFail($id);
        $pertanyaan->delete();
        return response()->json(['message' => 'Pertanyaan berhasil dihapus']);
    }
}
