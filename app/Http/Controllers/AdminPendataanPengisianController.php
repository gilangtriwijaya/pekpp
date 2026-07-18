<?php

namespace App\Http\Controllers;

use App\Models\PendataanPengisian;
use App\Models\Periode;
use Illuminate\Http\Request;

class AdminPendataanPengisianController extends Controller
{
    public function index(Request $request)
    {
        // Get active period first
        $periode = Periode::where('is_aktif', 1)->first();
        
        // If no active periode, check if periode_id is in request
        if (!$periode && $request->has('periode_id')) {
            $periode = Periode::find($request->periode_id);
        }
        
        // If still no periode, use latest periode
        if (!$periode) {
            $periode = Periode::latest('tahun')->first();
        }

        // Query pengisian
        $query = PendataanPengisian::with(['periode', 'upp', 'pengirim:id,nama'])
            ->withCount('jawaban')
            ->orderBy('created_at', 'desc');
        
        if ($periode) {
            $query->where('periode_id', $periode->id);
        }
        
        $pengisians = $query->get();
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        
        // Hitung statistik
        $total_upp = \App\Models\Upp::count();
        $sudah_submit = $pengisians->filter(fn($p) => $p->status === 'submitted')->count();
        $draft = $pengisians->filter(fn($p) => $p->status === 'draft' && $p->jawaban_count > 0)->count();

        // Hanya tampilkan yang sudah submit di tabel
        $tablePengisians = $pengisians->filter(fn($p) => $p->status === 'submitted')->values();

        return view('pendataan.admin.pengisian.index', compact(
            'tablePengisians', 
            'periodes', 
            'periode', 
            'total_upp', 
            'sudah_submit', 
            'draft'
        ));
    }

    public function show($id)
    {
        $pengisian = PendataanPengisian::with(['periode', 'upp', 'pengirim', 'jawaban'])->findOrFail($id);
        
        $aspeks = \App\Models\PendataanAspek::where('periode_id', $pengisian->periode_id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->get();
            
        $pertanyaans = \App\Models\PendataanPertanyaan::whereIn('pendataan_aspek_id', $aspeks->pluck('id'))
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->get();
            
        return view('pendataan.admin.pengisian.show', compact('pengisian', 'aspeks', 'pertanyaans'));
    }
}
