<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periode;
use App\Models\Aspek;
use App\Models\Indikator;
use App\Models\F02Skor;

class F02SkorController extends Controller
{
    /**
     * Index: Show aspek list for score management
     */
    public function index(Request $request)
    {
        $periode = Periode::where('is_aktif', 1)->first();
        
        if (!$periode) {
            return view('f02.skor.index')->with('error', 'Tidak ada periode aktif');
        }

        $aspeks = Aspek::where('periode_id', $periode->id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->with(['indikator' => function($q) {
                $q->where('aktif', 1)->orderBy('urutan');
            }])
            ->get();

        return view('f02.skor.index', compact('periode', 'aspeks'));
    }

    /**
     * Show indikators for selected aspek
     */
    public function show(Aspek $aspek)
    {
        $periode = Periode::where('is_aktif', 1)->first();
        
        $indikators = $aspek->indikator()
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->get();

        // Load skor definitions for each indikator
        $indikators = $indikators->map(function($ind) use ($periode) {
            $skor = F02Skor::where('indikator_id', $ind->id)
                ->where('periode_id', $periode->id)
                ->first();
            return [
                'indikator' => $ind,
                'skor' => $skor
            ];
        });

        return view('f02.skor.show', compact('aspek', 'periode', 'indikators'));
    }

    /**
     * API: Get skor for modal edit
     */
    public function getSkor(Request $request, $indikatorId)
    {
        $periode = Periode::where('is_aktif', 1)->first();
        $skor = F02Skor::where('indikator_id', $indikatorId)
            ->where('periode_id', $periode->id)
            ->first();

        $indikator = Indikator::find($indikatorId);

        return response()->json([
            'success' => true,
            'indikator' => $indikator,
            'skor' => $skor ? [
                'skor_1' => $skor->skor_1,
                'skor_2' => $skor->skor_2,
                'skor_3' => $skor->skor_3,
                'skor_4' => $skor->skor_4,
                'skor_5' => $skor->skor_5,
            ] : [
                'skor_1' => null,
                'skor_2' => null,
                'skor_3' => null,
                'skor_4' => null,
                'skor_5' => null,
            ]
        ]);
    }

    /**
     * API: Save or update skor
     */
    public function saveSkor(Request $request)
    {
        $validated = $request->validate([
            'indikator_id' => 'required|exists:indikator,id',
            'skor_0' => 'nullable|string',
            'skor_1' => 'nullable|string',
            'skor_2' => 'nullable|string',
            'skor_3' => 'nullable|string',
            'skor_4' => 'nullable|string',
            'skor_5' => 'nullable|string',
        ]);

        $periode = Periode::where('is_aktif', 1)->first();

        $skor = F02Skor::updateOrCreate(
            [
                'indikator_id' => $validated['indikator_id'],
                'periode_id' => $periode->id
            ],
            [
                'skor_0' => $validated['skor_0'],
                'skor_1' => $validated['skor_1'],
                'skor_2' => $validated['skor_2'],
                'skor_3' => $validated['skor_3'],
                'skor_4' => $validated['skor_4'],
                'skor_5' => $validated['skor_5'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Skor berhasil disimpan',
            'skor' => $skor
        ]);
    }

    /**
     * API: Delete skor
     */
    public function deleteSkor(Request $request, $indikatorId)
    {
        $periode = Periode::where('is_aktif', 1)->first();
        
        F02Skor::where('indikator_id', $indikatorId)
            ->where('periode_id', $periode->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skor berhasil dihapus'
        ]);
    }
}
