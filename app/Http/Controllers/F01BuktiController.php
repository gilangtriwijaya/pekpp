<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\F01Pengisian;
use App\Models\F01IndikatorNilai;
use App\Http\Requests\StoreBuktiRequest;

class F01BuktiController extends Controller
{
    public function store(StoreBuktiRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $pengisian = F01Pengisian::findOrFail($data['pengisian_id']);
        $this->authorize('update', $pengisian);

        if($pengisian->status !== 'draft'){
            return response()->json(['error'=>'Pengisian terkunci'],403);
        }

        return DB::transaction(function() use ($request, $data){
            $file = $request->file('file');
            $path = $file->store('f01/bukti', ['disk' => config('filesystems.default')]);

            // Create relation to indikator nilai
            $indNilai = F01IndikatorNilai::find($data['indikator_nilai_id']);
            if(!$indNilai){
                abort(422,'Indikator nilai tidak ditemukan');
            }

            $bukti = $indNilai->bukti()->create([
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'uploaded_by' => $request->user()->id,
            ]);

            return response()->json(['ok' => true, 'bukti' => $bukti]);
        });
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $bukti = \App\Models\F01IndikatorBukti::findOrFail($id);
        $pengisian = $bukti->indikatorNilai->pengisian;
        $this->authorize('update', $pengisian);

        if($pengisian->status !== 'draft'){
            return response()->json(['error'=>'Pengisian terkunci'],403);
        }

        return DB::transaction(function() use ($bukti){
            Storage::delete($bukti->path);
            $bukti->delete();
            return response()->json(['ok'=>true]);
        });
    }
}
