<?php

namespace App\Http\Controllers;

use App\Models\F03Aspek;
use App\Models\F03Indikator;
use App\Models\Periode;
use Illuminate\Http\Request;

class F03IndikatorController extends Controller
{
    protected $adminRoles = ['superadmin', 'admin_organisasi'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user->hasGlobalRole($this->adminRoles)) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $aspekId = $request->query('aspek_id');
        $query = F03Indikator::with(['aspek', 'periode']);

        if ($aspekId) {
            $query->where('f03_aspek_id', $aspekId);
        }

        for ($i = 1; $i <= 3; $i++) {
            $column = $request->query("sort$i");
            $direction = $request->query("dir$i", 'asc');
            
            if ($column && in_array($column, ['kode', 'pertanyaan', 'tipe_jawaban', 'aktif'])) {
                $query->orderBy($column, $direction === 'desc' ? 'desc' : 'asc');
            }
        }

        if (!$request->query('sort1')) {
            $query->orderBy('urutan', 'asc');
        }

        $indikators = $query->paginate(50);
        $aspeks = F03Aspek::with('periode')->get();
        
        return view('f03.indikator.index', compact('indikators', 'aspeks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'f03_aspek_id' => 'required|exists:f03_aspek,id',
            'kode' => 'nullable|string|max:50',
            'pertanyaan' => 'required|string',
            'tipe_jawaban' => 'required|in:text,radio,checkbox,dropdown,likert_5,rating,textarea',
            'pilihan_jawaban' => 'nullable|json',
            'urutan' => 'nullable|integer|min:1',
            'aktif' => 'boolean',
        ]);

        // Untuk tipe_jawaban tertentu, pastikan pilihan_jawaban ada
        if (in_array($validated['tipe_jawaban'], ['radio', 'checkbox', 'dropdown']) && empty($validated['pilihan_jawaban'])) {
            $validated['pilihan_jawaban'] = json_encode([]);
        }

        if (empty($validated['kode'])) {
            // Generate unique kode globally (across all aspeks and periodes)
            $lastIndikator = F03Indikator::orderByRaw("CAST(SUBSTRING(kode, 3) AS UNSIGNED) DESC")->first();
            $number = 1;
            if ($lastIndikator && preg_match('/^FI(\d+)$/', $lastIndikator->kode, $matches)) {
                $number = intval($matches[1]) + 1;
            }
            $validated['kode'] = 'FI' . str_pad($number, 3, '0', STR_PAD_LEFT);
        }

        // If urutan not provided, auto-assign next sequence
        if (empty($validated['urutan'])) {
            $maxUrutan = F03Indikator::where('f03_aspek_id', $validated['f03_aspek_id'])->max('urutan') ?? 0;
            $validated['urutan'] = $maxUrutan + 1;
        }
        
        $validated['aktif'] = $request->has('aktif');

        if ($validated['pilihan_jawaban']) {
            $validated['pilihan_jawaban'] = json_decode($validated['pilihan_jawaban'], true);
        }

        F03Indikator::create($validated);

        return response()->json(['message' => 'Indikator F03 berhasil dibuat']);
    }

    public function update(Request $request, $id)
    {
        $indikator = F03Indikator::findOrFail($id);

        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_jawaban' => 'required|in:text,radio,checkbox,dropdown,likert_5,rating,textarea',
            'pilihan_jawaban' => 'nullable|json',
            'urutan' => 'nullable|integer|min:1',
            'aktif' => 'boolean',
        ]);

        // Untuk tipe_jawaban tertentu, pastikan pilihan_jawaban ada
        if (in_array($validated['tipe_jawaban'], ['radio', 'checkbox', 'dropdown']) && empty($validated['pilihan_jawaban'])) {
            $validated['pilihan_jawaban'] = json_encode([]);
        }

        $validated['aktif'] = $request->has('aktif');

        if ($validated['pilihan_jawaban']) {
            $validated['pilihan_jawaban'] = json_decode($validated['pilihan_jawaban'], true);
        }

        $indikator->update($validated);

        return response()->json(['message' => 'Indikator F03 berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $indikator = F03Indikator::findOrFail($id);
        $indikator->delete();

        return response()->json(['message' => 'Indikator F03 berhasil dihapus']);
    }
}
