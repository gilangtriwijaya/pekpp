<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\F01Pengisian;
use App\Services\F01ScoringService;

class F01JawabanController extends Controller
{
    protected $scoring;

    public function __construct(F01ScoringService $scoring)
    {
        $this->scoring = $scoring;
    }

    // Store or update single question answer (AJAX autosave)
    public function storeOrUpdate(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'pengisian_id' => 'required|integer|exists:f01_pengisian,id',
            'indikator_id' => 'required|integer',
            'pertanyaan_id' => 'required',
            'jawaban' => 'nullable',
        ]);

        $pengisian = F01Pengisian::findOrFail($data['pengisian_id']);
        $this->authorize('update', $pengisian);

        if($pengisian->status !== 'draft'){
            return response()->json(['error' => 'Pengisian terkunci'], 403);
        }

        return DB::transaction(function() use ($data){
            // Persist raw answer into f01_jawaban
            $jawaban = \App\Models\F01Jawaban::updateOrCreate(
                [
                    'f01_pengisian_id' => $data['pengisian_id'],
                    'pertanyaan_id' => $data['pertanyaan_id'],
                ],
                ['nilai' => json_encode($data['jawaban'] ?? null)]
            );

            return response()->json(['ok' => true, 'jawaban' => $jawaban]);
        });
    }

    // Optional bulk save
    public function bulkSave(Request $request)
    {
        $user = $request->user();
        $payload = $request->validate(['pengisian_id' => 'required|exists:f01_pengisian,id','answers' => 'required|array']);
        $pengisian = F01Pengisian::findOrFail($payload['pengisian_id']);
        $this->authorize('update', $pengisian);

        if($pengisian->status !== 'draft'){
            return response()->json(['error'=>'Pengisian sudah terkunci'],403);
        }

        return DB::transaction(function() use ($payload){
            foreach($payload['answers'] as $a){
                // validate each item minimally and save raw answers to a separate storage if used
            }
            // compute previews per indikator
            return response()->json(['ok'=>true]);
        });
    }
}
