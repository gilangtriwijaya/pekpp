<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PendataanPengisian;
use App\Models\PendataanAspek;
use App\Models\PendataanPertanyaan;
use App\Models\PendataanJawaban;
use App\Models\Periode;
use App\Models\Upp;
use Illuminate\Support\Facades\DB;

class PendataanController extends Controller
{
    // Langsung redirect ke form pengisian aspek
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil periode yang aktif
        $periode = Periode::where('is_aktif', 1)->first() ?? Periode::latest('tahun')->first();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        // Ambil UPP yang user punya akses
        $uppIds = [];
        try {
            $uppIds = collect($user->getUserUpps())->filter(function($u){
                return (bool) ($u->aktif ?? true);
            })->pluck('upp_id')->unique()->values()->all();
        } catch (\Throwable $e) {
            $uppIds = [];
        }

        if (empty($uppIds)) {
            $upp = Upp::first();
            if (!$upp) return redirect()->back()->with('error', 'Tidak ada UPP');
            $uppIds = [$upp->id];
        }

        $uppId = $uppIds[0];

        $pengisian = PendataanPengisian::firstOrCreate(
            [
                'periode_id' => $periode->id,
                'upp_id' => $uppId,
            ],
            [
                'status' => 'draft',
                'dikirim_oleh' => $user->id,
            ]
        );

        return redirect()->route('pendataan.aspek-list', $pengisian->id);
    }

    public function aspekList(Request $request, $pengisianId)
    {
        $pengisian = PendataanPengisian::with(['periode', 'upp', 'jawaban'])->findOrFail($pengisianId);

        $aspeks = PendataanAspek::where('periode_id', $pengisian->periode_id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->with(['pertanyaan' => function($q) {
                $q->where('aktif', 1)->orderBy('urutan');
            }])
            ->get();

        $aspeks = $aspeks->map(function($aspek) use ($pengisian) {
            $questions = $aspek->pertanyaan ?? collect();
            $totalQuestions = $questions->count();
            
            $answeredQuestions = 0;
            foreach ($questions as $q) {
                $jawaban = $pengisian->jawaban()->where('pendataan_pertanyaan_id', $q->id)->first();
                if ($jawaban && $jawaban->nilai !== null && $jawaban->nilai !== '') {
                    $answeredQuestions++;
                }
            }

            $progress = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;
            
            return [
                'aspek' => $aspek,
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'progress' => $progress,
            ];
        });

        $isAcceptingInput = $pengisian->periode && $pengisian->periode->status_pengisian === 'open';

        return view('pendataan.upp.aspek-list', [
            'pengisian' => $pengisian,
            'aspeks' => $aspeks,
            'isReadOnly' => $pengisian->status !== 'draft' || !$isAcceptingInput,
            'isAcceptingInput' => $isAcceptingInput,
            'periodStatus' => $pengisian->periode?->status_pengisian
        ]);
    }

    public function showAspekDetail(Request $request, $pengisianId, $aspekId)
    {
        $pengisian = PendataanPengisian::with(['periode', 'upp'])->findOrFail($pengisianId);
        $aspek = PendataanAspek::findOrFail($aspekId);

        $pertanyaan = PendataanPertanyaan::where('pendataan_aspek_id', $aspekId)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->get();

        $pertanyaanData = $pertanyaan->map(function($p) use ($pengisian) {
            $jawaban = $pengisian->jawaban()->where('pendataan_pertanyaan_id', $p->id)->first();
            return [
                'pertanyaan' => $p,
                'jawaban' => $jawaban?->nilai,
                'file_path' => $jawaban?->file_path,
                'file_name' => $jawaban?->file_name
            ];
        });

        $aspeks = PendataanAspek::where('periode_id', $pengisian->periode_id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->get();

        return view('pendataan.upp.show', [
            'pengisian' => $pengisian,
            'aspek' => $aspek,
            'aspeks' => $aspeks,
            'pertanyaanData' => $pertanyaanData,
            'isReadOnly' => $pengisian->status !== 'draft'
        ]);
    }

    public function getFormData(Request $request, $id)
    {
        $pengisian = PendataanPengisian::with(['periode', 'upp'])->findOrFail($id);

        $aspeks = PendataanAspek::where('periode_id', $pengisian->periode_id)
            ->orderBy('urutan')
            ->with(['pertanyaan' => function($q) {
                $q->orderBy('urutan');
            }])
            ->get()
            ->map(function($aspek) use ($pengisian) {
                $totalQuestions = $aspek->pertanyaan->count();
                
                $answeredQuestions = $aspek->pertanyaan->filter(function($q) use ($pengisian) {
                    $jawaban = $pengisian->jawaban()->where('pendataan_pertanyaan_id', $q->id)->first();
                    return $jawaban && $jawaban->nilai !== null && $jawaban->nilai !== '';
                })->count();
                
                $progressPercent = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;

                return [
                    'id' => $aspek->id,
                    'nama' => $aspek->nama,
                    'urutan' => $aspek->urutan,
                    'answered' => $answeredQuestions,
                    'total' => $totalQuestions,
                    'progress' => $progressPercent,
                    'status' => 'draft', // Pendataan mungkin cuma draft/final di pengisian level
                    'questions' => $aspek->pertanyaan->map(function($q) use ($pengisian) {
                        $jawaban = $pengisian->jawaban()->where('pendataan_pertanyaan_id', $q->id)->first();
                        
                        $opsiData = $q->opsi_jawaban;
                        if ($q->tipe_input === 'yesno') {
                            $opsiData = json_encode([
                                ['label' => 'Ya', 'value' => 'ya'],
                                ['label' => 'Tidak', 'value' => 'tidak']
                            ]);
                        }
                        
                        $decodedOptions = [];
                        if ($opsiData) {
                            if (is_string($opsiData)) {
                                $decodedOptions = json_decode($opsiData, true) ?: [];
                            } elseif (is_array($opsiData)) {
                                $decodedOptions = $opsiData;
                            }
                        }
                        
                        return [
                            'id' => $q->id,
                            'label' => $q->label,
                            'tipe' => $q->tipe_input,
                            'tipe_input' => $q->tipe_input,
                            'urutan' => $q->urutan,
                            'nilai' => $jawaban?->nilai,
                            'catatan' => $jawaban?->catatan,
                            'file_path' => $jawaban?->file_path,
                            'file_name' => $jawaban?->file_name,
                            'wajib' => $q->wajib,
                            'opsi_jawaban' => is_string($opsiData) ? $opsiData : json_encode($decodedOptions),
                            'options' => $decodedOptions,
                        ];
                    })->toArray(),
                ];
            });

        return response()->json([
            'data' => [
                'pengisianId' => $pengisian->id,
                'periode' => $pengisian->periode,
                'upp' => $pengisian->upp,
                'status' => $pengisian->status,
                'periodeAktif' => $pengisian->periode->is_aktif,
                'aspeks' => $aspeks,
            ]
        ]);
    }

    public function autoSave(Request $request, $pengisianId)
    {
        $request->validate([
            'pertanyaan_id' => 'required|exists:pendataan_pertanyaan,id',
            'nilai' => 'nullable'
        ]);

        $pengisian = PendataanPengisian::findOrFail($pengisianId);

        if ($pengisian->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Pengisian sudah final'], 403);
        }

        $jawaban = PendataanJawaban::updateOrCreate(
            [
                'pendataan_pengisian_id' => $pengisian->id,
                'pendataan_pertanyaan_id' => $request->pertanyaan_id
            ],
            [
                'nilai' => $request->nilai
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Jawaban tersimpan',
            'data' => $jawaban
        ]);
    }

    public function uploadBukti(Request $request, $pengisianId)
    {
        $request->validate([
            'pertanyaan_id' => 'required|exists:pendataan_pertanyaan,id',
            'file' => 'required|file|mimes:pdf|max:10240' // max 10MB PDF
        ]);

        $pengisian = PendataanPengisian::findOrFail($pengisianId);

        if ($pengisian->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Pengisian sudah final'], 403);
        }

        $jawaban = PendataanJawaban::firstOrCreate([
            'pendataan_pengisian_id' => $pengisian->id,
            'pendataan_pertanyaan_id' => $request->pertanyaan_id
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('pendataan_bukti', $fileName, 'public');

        $jawaban->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diunggah',
            'file_name' => $jawaban->file_name,
            'file_url' => asset('storage/' . $path)
        ]);
    }

    public function submit(Request $request, $pengisianId)
    {
        $pengisian = PendataanPengisian::findOrFail($pengisianId);

        if ($pengisian->status !== 'draft') {
            return redirect()->back()->with('error', 'Pengisian tidak dalam status draft.');
        }

        $pengisian->update([
            'status' => 'final',
            'dikirim_oleh' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return redirect()->route('pendataan.aspek-list', $pengisian->id)->with('success', 'Pengisian berhasil disubmit.');
    }
}
