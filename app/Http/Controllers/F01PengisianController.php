<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\F01Pengisian;
use App\Models\F02Validasi;
use App\Models\F02IndikatorValidasi;
use App\Models\Aspek;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use App\Models\F01Jawaban;
use App\Models\F01BuktiDukung;
use App\Http\Controllers\Controller;
use App\Services\F01ScoringService;
use Illuminate\Support\Facades\DB;

class F01PengisianController extends Controller
{
    protected $scoring;

    public function __construct(F01ScoringService $scoring)
    {
        $this->scoring = $scoring;
        // middleware can check auth; policy checks done in methods
    }

    // Langsung redirect ke form pengisian aspek (tidak ada index daftar)
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil periode yang aktif (is_aktif = 1), or any periode if none active
        $periode = \App\Models\Periode::where('is_aktif', 1)->first();
        
        if (!$periode) {
            // If no active periode, get the latest one
            $periode = \App\Models\Periode::latest('tahun')->first();
        }

        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode. Hubungi administrator untuk mengatur periode.');
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

        // Untuk superadmin/testing, ambil UPP pertama dari DB
        if (empty($uppIds)) {
            $upp = \App\Models\Upp::first();
            if (!$upp) {
                return redirect()->back()->with('error', 'Tidak ada UPP');
            }
            $uppIds = [$upp->id];
        }

        $uppId = $uppIds[0]; // Ambil UPP pertama

        // Cari versi latest (is_latest_version = true) untuk periode + UPP ini
        $pengisian = F01Pengisian::where('periode_id', $periode->id)
            ->where('upp_id', $uppId)
            ->where('is_latest_version', true)
            ->first();

        // Jika belum ada, buat baru
        if (!$pengisian) {
            $pengisian = F01Pengisian::create([
                'periode_id' => $periode->id,
                'upp_id' => $uppId,
                'status' => 'draft',
                'version_number' => 1,
                'is_latest_version' => true,
                'dikirim_oleh' => $user->id,
            ]);
        }

        // Langsung redirect ke aspek list view
        return redirect()->route('f01.aspek-list', $pengisian->id);
    }

    // Baru: Tampilkan list aspek dengan progress
    public function aspekList(Request $request, F01Pengisian $pengisian)
    {
        $user = $request->user();
        $pengisian = $pengisian->load(['periode', 'upp', 'jawaban']);

        // Policy: ensure user has access
        $this->authorize('view', $pengisian);

        // Get all aspek dengan indikators from the periode
        $aspeks = Aspek::where('periode_id', $pengisian->periode_id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->with(['indikator' => function($q) {
                $q->where('aktif', 1)
                  ->orderBy('urutan')
                  ->with(['pertanyaan' => function($pq) {
                      $pq->where('aktif', 1)->orderBy('urutan');
                  }]);
            }])
            ->get();

        // Load F02 validasi data if pengisian is submitted or completed (not just completed)
        $f02Validasi = null;
        $f02IndikatorValidasi = collect();
        if ($pengisian->status !== 'draft') {
            $f02Validasi = F02Validasi::where('f01_pengisian_id', $pengisian->id)->first();
            if ($f02Validasi) {
                $f02IndikatorValidasi = F02IndikatorValidasi::where('f02_validasi_id', $f02Validasi->id)
                    ->get()
                    ->keyBy('indikator_id');
            }
        }

        // Map data dengan progress info atau F02 scores
        $aspeks = $aspeks->map(function($aspek) use ($pengisian, $f02IndikatorValidasi) {
            $indikators = $aspek->indikator ?? collect();
            $totalIndikators = $indikators->count();
            $filledIndikators = 0;
            $skorMentah = 0;
            $hasF02Data = $f02IndikatorValidasi->count() > 0;

            foreach ($indikators as $ind) {
                if ($hasF02Data && isset($f02IndikatorValidasi[$ind->id])) {
                    // If F02 validation exists with data, get F02 validation scores
                    $nilaiObj = $f02IndikatorValidasi[$ind->id];
                    if (!is_null($nilaiObj->nilai)) {
                        $filledIndikators++;
                        $skorMentah += $nilaiObj->nilai;
                    }
                } else {
                    // For draft/submitted forms without F02 data, check if questions are answered
                    $questions = $ind->pertanyaan ?? collect();
                    $totalQuestions = $questions->count();
                    if ($totalQuestions === 0) {
                        $filledIndikators++;
                        continue;
                    }

                    $answeredQuestions = 0;
                    foreach ($questions as $q) {
                        $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $q->id)->first();
                        // Use !== null to allow 0 values (empty() treats "0" as empty!)
                        if ($jawaban && $jawaban->nilai !== null && $jawaban->nilai !== '') {
                            $answeredQuestions++;
                        }
                    }

                    // An indicator is "filled" if ANY question has been answered
                    // This correctly handles skip-type questions
                    if ($answeredQuestions > 0) {
                        $filledIndikators++;
                    }
                }
            }

            $progress = $totalIndikators > 0 ? round(($filledIndikators / $totalIndikators) * 100) : 0;
            
            return [
                'aspek' => $aspek,
                'total_indikators' => $totalIndikators,
                'filled_indikators' => $filledIndikators,
                'progress' => $progress,
                'skor_mentah' => round($skorMentah, 2),
                'has_f02_data' => $hasF02Data && $skorMentah > 0
            ];
        });

        // Check if periodo is accepting input (status_pengisian = 'open')
        $isAcceptingInput = $pengisian->periode && $pengisian->periode->status_pengisian === 'open';

        return view('f01.aspek-list', [
            'pengisian' => $pengisian,
            'aspeks' => $aspeks,
            'isReadOnly' => $pengisian->status !== 'draft' || !$isAcceptingInput,
            'isAcceptingInput' => $isAcceptingInput,
            'periodStatus' => $pengisian->periode?->status_pengisian
        ]);
    }

    // AJAX: Get indikator detail dengan F02 data untuk display read-only
    public function getIndikatorDetail(Request $request, $pengisianId, $indikatorId)
    {
        $user = $request->user();
        
        // Load pengisian dan indikator
        $pengisian = F01Pengisian::findOrFail($pengisianId);
        $indikator = Indikator::findOrFail($indikatorId);
        
        // Policy: ensure user has access
        $this->authorize('view', $pengisian);

        try {
            // Load indikator dengan pertanyaan
            $indikator->load(['pertanyaan' => function($q) {
                $q->where('aktif', 1)->orderBy('urutan');
            }]);

            // Get F02 validation data jika ada
            $f02Validasi = F02Validasi::where('f01_pengisian_id', $pengisian->id)->first();
            $f02IndikatorValidasi = null;
            if ($f02Validasi) {
                $f02IndikatorValidasi = F02IndikatorValidasi::where('f02_validasi_id', $f02Validasi->id)
                    ->where('indikator_id', $indikator->id)
                    ->first();
            }

            // Build pertanyaan data dengan jawaban
            $pertanyaan = $indikator->pertanyaan->map(function($p) use ($pengisian) {
                $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $p->id)->first();
                $jawabanValue = $jawaban?->nilai;

                return [
                    'id' => $p->id,
                    'urutan' => $p->urutan,
                    'label' => $p->label,
                    'tipe' => $p->tipe(),
                    'jawaban' => $jawabanValue,
                    'opsi_jawaban' => $p->opsi_jawaban
                ];
            })->toArray();

            // Get bukti dukung untuk indikator ini
            $buktiDukung = $pengisian->buktiDukung()
                ->where('indikator_id', $indikator->id)
                ->first();

            return response()->json([
                'success' => true,
                'indikator' => [
                    'id' => $indikator->id,
                    'urutan' => $indikator->urutan,
                    'nama' => $indikator->nama,
                    'bukti_dukung' => $indikator->bukti_dukung,
                    'deskripsi' => $indikator->deskripsi
                ],
                'pertanyaan' => $pertanyaan,
                'bukti_dukung_url' => $buktiDukung?->url_bukti,
                'f02_data' => $f02IndikatorValidasi ? [
                    'nilai' => $f02IndikatorValidasi->nilai,
                    'catatan' => $f02IndikatorValidasi->catatan,
                    'status' => $f02IndikatorValidasi->status
                ] : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // AJAX: Get aspek list dengan indikators untuk modal display
    public function getAspekListForModal(Request $request, $pengisianId)
    {
        $user = $request->user();
        
        // Load pengisian
        $pengisian = F01Pengisian::findOrFail($pengisianId);
        
        // Policy: ensure user has access
        $this->authorize('view', $pengisian);

        try {
            // Get aspeks dengan indikators
            $aspeks = Aspek::where('periode_id', $pengisian->periode_id)
                ->where('aktif', 1)
                ->orderBy('urutan')
                ->with(['indikator' => function($q) {
                    $q->where('aktif', 1)->orderBy('urutan');
                }])
                ->get()
                ->map(function($aspek) {
                    return [
                        'id' => $aspek->id,
                        'nama' => $aspek->nama,
                        'urutan' => $aspek->urutan,
                        'indikators' => $aspek->indikator->map(function($ind) {
                            return [
                                'id' => $ind->id,
                                'urutan' => $ind->urutan,
                                'nama' => $ind->nama
                            ];
                        })->toArray()
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $aspeks->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Show aspek detail page (read-only, with F02 validation data)
    public function showAspekDetail(Request $request, $pengisianId, $aspekId)
    {
        $user = $request->user();
        
        // Load pengisian dan aspek
        $pengisian = F01Pengisian::with(['periode', 'upp'])->findOrFail($pengisianId);
        $aspek = Aspek::findOrFail($aspekId);
        
        // Policy: ensure user has access
        $this->authorize('view', $pengisian);

        // Load indikators dengan pertanyaan
        $indikators = Indikator::where('aspek_id', $aspekId)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->with(['pertanyaan' => function($q) {
                $q->where('aktif', 1)->orderBy('urutan');
            }])
            ->get();

        // Get F02 validation data for this F01. If not exists (resubmit draft), fallback ke previous version
        $f02Validasi = F02Validasi::where('f01_pengisian_id', $pengisian->id)->first();
        if (!$f02Validasi && $pengisian->previous_f01_pengisian_id) {
            $prevF01 = $pengisian->previousVersion;
            if ($prevF01) {
                $f02Validasi = F02Validasi::where('f01_pengisian_id', $prevF01->id)->first();
            }
        }

        $f02IndikatorMap = collect();
        if ($f02Validasi) {
            $f02IndikatorMap = F02IndikatorValidasi::where('f02_validasi_id', $f02Validasi->id)
                ->get()
                ->keyBy('indikator_id');
        }

        // Prepare indikator data dengan F02 info
        $indikatorData = $indikators->map(function($ind) use ($pengisian, $f02IndikatorMap) {
            $pertanyaan = $ind->pertanyaan->map(function($p) use ($pengisian) {
                $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $p->id)->first();
                return [
                    'id' => $p->id,
                    'urutan' => $p->urutan,
                    'label' => $p->label,
                    'tipe' => $p->tipe(),
                    'jawaban' => $jawaban?->nilai
                ];
            });

            $buktiDukung = $pengisian->buktiDukung()
                ->where('indikator_id', $ind->id)
                ->first();

            $f02Data = null;
            if ($f02IndikatorMap->has($ind->id)) {
                $val = $f02IndikatorMap->get($ind->id);
                $f02Data = [
                    'nilai' => $val->nilai,
                    'catatan' => $val->catatan,
                    'status' => $val->status
                ];
            }

            return [
                'indikator' => $ind,
                'pertanyaan' => $pertanyaan,
                'bukti_dukung' => $buktiDukung,
                'f02_data' => $f02Data
            ];
        });

        // Get list aspek untuk sidebar/breadcrumb
        $aspeks = Aspek::where('periode_id', $pengisian->periode_id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->get();

        return view('f01.aspek-detail', [
            'pengisian' => $pengisian,
            'aspek' => $aspek,
            'aspeks' => $aspeks,
            'indikatorData' => $indikatorData,
            'isReadOnly' => $pengisian->status !== 'draft'
        ]);
    }

    // Show or create pengisian untuk periode aktif
    public function show(Request $request, $pengisian)
    {
        $user = $request->user();
        $selectedAspekId = $request->query('aspek');

        // Load pengisian with relationships
        if (is_numeric($pengisian)) {
            $pengisian = F01Pengisian::with(['periode', 'upp', 'jawaban', 'indikatorNilai', 'buktiDukung'])->findOrFail($pengisian);
        } else {
            $pengisian->load(['periode', 'upp', 'jawaban', 'indikatorNilai', 'buktiDukung']);
        }

        // Policy: ensure user has access via user_upp
        $this->authorize('view', $pengisian);

        // Load aspeks with indikators and pertanyaannya - HANYA dari periode ini
        $aspeks = Aspek::where('periode_id', $pengisian->periode_id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->with(['indikator' => function($q) {
                $q->where('aktif', 1)
                  ->orderBy('urutan')
                  ->with(['pertanyaan' => function($pq) {
                      $pq->where('aktif', 1)->orderBy('urutan');
                  }]);
            }])
            ->get();

        // If no aspek selected, use the first one
        if (!$selectedAspekId || !$aspeks->contains('id', $selectedAspekId)) {
            $selectedAspekId = $aspeks->first()?->id;
        }

        // Get the selected aspek details
        $selectedAspek = $aspeks->firstWhere('id', $selectedAspekId);

        // Get previous or current F02 validation data (to provide skor + catatan indikator)
        $f02Validasi = $pengisian->f02()->with('indikatorValidasi')->first();
        if (!$f02Validasi && $pengisian->previousVersion) {
            $f02Validasi = $pengisian->previousVersion->f02()->with('indikatorValidasi')->first();
        }

        $f02IndicatorMap = [];
        if ($f02Validasi) {
            foreach ($f02Validasi->indikatorValidasi as $iv) {
                $f02IndicatorMap[$iv->indikator_id] = [
                    'nilai' => $iv->nilai,
                    'catatan' => $iv->catatan,
                    'status' => $iv->status
                ];
            }
        }

        // Calculate progress for each aspek
        $aspeksWithProgress = $aspeks->map(function($aspek) use ($pengisian) {
            $indikators = $aspek->indikator ?? collect();
            $totalIndikators = $indikators->count();
            $filledIndikators = 0;

            foreach ($indikators as $ind) {
                $totalQuestions = $ind->pertanyaan->count();
                if ($totalQuestions === 0) {
                    $filledIndikators++;
                    continue;
                }

                // Count answered questions
                $answeredQuestions = 0;
                foreach ($ind->pertanyaan as $q) {
                    $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $q->id)->first();
                    // Use !== null to allow 0 values (empty() treats "0" as empty!)
                    if ($jawaban && $jawaban->nilai !== null && $jawaban->nilai !== '') {
                        $answeredQuestions++;
                    }
                }

                // An indicator is "filled" if:
                // 1. ALL questions are answered, OR
                // 2. ANY question has been answered (user engagement with indicator)
                // This handles skip-type questions correctly: if Q1 is answered "Tidak",
                // it skips Q2-Q3, but the indicator should still count as progress
                if ($answeredQuestions > 0) {
                    $filledIndikators++;
                }
            }

            $progress = $totalIndikators > 0 ? round(($filledIndikators / $totalIndikators) * 100) : 0;

            return [
                'aspek' => $aspek,
                'total_indikators' => $totalIndikators,
                'filled_indikators' => $filledIndikators,
                'progress' => $progress
            ];
        });

        $isResubmit = $pengisian->version_number > 1;
        $isChangedMap = [];
        if ($isResubmit) {
            $isChangedMap = \App\Models\F01IndikatorNilai::where('f01_pengisian_id', $pengisian->id)
                ->pluck('is_changed', 'indikator_id')
                ->toArray();
        }

        return view('f01.show-new', [
            'pengisian' => $pengisian,
            'selectedAspek' => $selectedAspek,
            'selectedAspekId' => $selectedAspekId,
            'aspeks' => $aspeksWithProgress,
            'f02IndicatorMap' => $f02IndicatorMap,
            'isReadOnly' => $pengisian->status !== 'draft',
            'isResubmit' => $isResubmit,
            'isChangedMap' => $isChangedMap
        ]);
    }

    // API endpoint: Get form data structured for JavaScript
    public function getFormData(Request $request, $id)
    {
        $user = $request->user();
        $pengisian = F01Pengisian::with(['periode', 'upp'])->findOrFail($id);
        
        $this->authorize('view', $pengisian);

        // Load validation data if exists
        $validasiData = $pengisian->f02()  // F02Validasi relationship
            ->with(['indikatorValidasi'])
            ->first();
        
        // Build mapping of indikator_id => validation info for quick lookup
        $validationMap = [];
        if ($validasiData) {
            foreach ($validasiData->indikatorValidasi as $iv) {
                $validationMap[$iv->indikator_id] = [
                    'nilai' => $iv->nilai,
                    'catatan' => $iv->catatan,
                    'status' => $iv->status
                ];
            }
        }

        // Load aspeks with indikators and pertanyaannya - HANYA dari periode ini
        $aspeks = \App\Models\Aspek::where('periode_id', $pengisian->periode_id)
            ->orderBy('urutan')
            ->with(['indikator' => function($q) {
                $q->orderBy('urutan')
                  ->with(['pertanyaan' => function($pq) {
                      $pq->orderBy('urutan');
                  }]);
            }])
            ->get()
            ->map(function($aspek) use ($pengisian, $validationMap) {
                // Calculate aspek progress as: completed indikators / total indikators
                $indikators = $aspek->indikator;
                $totalIndikators = $indikators->count();
                $completedIndikators = 0;
                
                // Count how many indikators have ANY question answered
                foreach ($indikators as $ind) {
                    $totalQuestions = $ind->pertanyaan->count();
                    if ($totalQuestions === 0) continue;
                    
                    $answeredQuestions = $ind->pertanyaan->filter(function($q) use ($pengisian) {
                        $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $q->id)->first();
                        // Use !== null to allow 0 values (empty() treats "0" as empty!)
                        return $jawaban && $jawaban->nilai !== null && $jawaban->nilai !== '';
                    })->count();
                    
                    // An indicator is "completed" if ANY question has been answered
                    // This correctly handles skip-type questions
                    if ($answeredQuestions > 0) {
                        $completedIndikators++;
                    }
                }
                
                $progressPercent = $totalIndikators > 0 ? round(($completedIndikators / $totalIndikators) * 100) : 0;

                // Get aspek status from database (default to draft if not found)
                $aspekStatus = \App\Models\F01AspekPengisian::where([
                    ['f01_pengisian_id', $pengisian->id],
                    ['aspek_id', $aspek->id]
                ])->first();
                
                $status = $aspekStatus?->status ?? 'draft';
                $lastSavedAt = $aspekStatus?->last_saved_at;

                return [
                    'id' => $aspek->id,
                    'nama' => $aspek->nama,
                    'urutan' => $aspek->urutan,
                    'answered' => $completedIndikators,
                    'total' => $totalIndikators,
                    'progress' => $progressPercent,
                    'status' => $status,
                    'last_saved_at' => $lastSavedAt,
                    'indikators' => $aspek->indikator->map(function($indikator) use ($pengisian, $validationMap) {
                        // Calculate answered/total questions for indikator
                        $totalQuestions = $indikator->pertanyaan->count();
                        $answeredQuestions = $indikator->pertanyaan->filter(function($q) use ($pengisian) {
                            $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $q->id)->first();
                            // Use !== null to allow 0 values (empty() treats "0" as empty!)
                            return $jawaban && $jawaban->nilai !== null && $jawaban->nilai !== '';
                        })->count();
                        $progressPercent = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;

                        // Get validation data for this indikator if exists
                        $indikatorValidation = $validationMap[$indikator->id] ?? null;
                        
                        return [
                            'id' => $indikator->id,
                            'nama' => $indikator->nama,
                            'urutan' => $indikator->urutan,
                            'aspek_id' => $indikator->aspek_id,
                            'kode' => null,  // Jika ada kode field
                            'answered' => $answeredQuestions,
                            'total' => $totalQuestions,
                            'progress' => $progressPercent,
                            'validation' => $indikatorValidation,  // Include validation data
                            'questions' => $indikator->pertanyaan->map(function($q) use ($pengisian) {
                                // Get jawaban for this pertanyaan
                                $jawaban = $pengisian->jawaban()
                                    ->where('pertanyaan_id', $q->id)
                                    ->first();
                                
                                // Parse opsi_jawaban or provide defaults
                                $opsiData = $q->opsi_jawaban;
                                
                                // For yesno type, ALWAYS use Ya/Tidak regardless of database
                                if ($q->tipe_input === 'yesno') {
                                    $opsiData = json_encode([
                                        ['label' => 'Ya', 'value' => 'ya'],
                                        ['label' => 'Tidak', 'value' => 'tidak']
                                    ]);
                                }
                                
                                // Ensure options are decoded if string
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
                                    'tipe_input' => $q->tipe_input,  // Keep both for compatibility
                                    'urutan' => $q->urutan,
                                    'nilai' => $jawaban?->nilai,
                                    'catatan' => $jawaban?->catatan,
                                    'show_when' => $q->show_when,
                                    'skip_if_answer' => $q->skip_if_answer,
                                    'parent_pertanyaan_id' => $q->parent_pertanyaan_id,
                                    'wajib' => $q->wajib,
                                    'min' => $q->min,
                                    'max' => $q->max,
                                    'opsi_jawaban' => is_string($opsiData) ? $opsiData : json_encode($decodedOptions),
                                    'options' => $decodedOptions,
                                ];
                            })->toArray(),
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
                'hasValidation' => $validasiData ? true : false,  // Flag to show validation feedback
                'validationStatus' => $validasiData?->status,
            ]
        ]);
    }

    // Finalize and lock pengisian
    public function submit(F01Pengisian $pengisian, \App\Services\F01ScoringService $scoring, \App\Services\F01ResubmitService $resubmitService)
    {
        $this->authorize('submit', $pengisian);

        if ($pengisian->status !== 'draft') {
            abort(403, 'Pengisian tidak dalam status draft.');
        }

        $scoring->finalizePengisian($pengisian);

        $pengisian->update([
            'status' => 'final',
            'dikirim_oleh' => auth()->id(),
            'submitted_at' => now(),
        ]);
        
        // Auto-create F02Validasi for this submission
        $resubmitService->autoCreateF02($pengisian);

        return redirect()->route('f01.show', $pengisian->id)->with('success', 'Pengisian berhasil disubmit.');
    }

    /**
     * Get ringkasan penilaian (summary for display)
     */
    public function getRingkasan(Request $request, $id)
    {
        $user = $request->user();
        $pengisian = F01Pengisian::with(['periode', 'upp'])->findOrFail($id);
        
        $this->authorize('view', $pengisian);

        // Load aspeks with indikators and questions - HANYA dari periode ini
        $aspeks = \App\Models\Aspek::where('periode_id', $pengisian->periode_id)
            ->orderBy('kode', 'asc')
            ->with(['indikator' => function($q) {
                $q->orderBy('kode', 'asc')
                  ->with(['pertanyaan' => function($pq) {
                      $pq->orderBy('kode', 'asc');
                  }]);
            }])
            ->get();

        // Build summary data
        $ringkasan = [];
        $totalIndikators = 0;
        $completedIndikators = 0;

        foreach ($aspeks as $aspek) {
            $aspekData = [
                'id' => $aspek->id,
                'kode' => $aspek->kode ?? '',
                'nama' => $aspek->nama ?? '',
                'indikators' => [],
                'answered' => 0,
                'total' => 0
            ];

            foreach ($aspek->indikator as $indikator) {
                $totalQuestions = $indikator->pertanyaan->count();
                
                // Get answered questions count
                $answeredQuestions = 0;
                foreach ($indikator->pertanyaan as $q) {
                    $jawaban = $pengisian->jawaban()->where('pertanyaan_id', $q->id)->first();
                    // Use !== null to allow 0 values (empty() treats "0" as empty!)
                    if ($jawaban && $jawaban->nilai !== null && $jawaban->nilai !== '') {
                        $answeredQuestions++;
                    }
                }

                // An indicator is "complete" if ANY question has been answered
                // This correctly handles skip-type questions
                $isComplete = ($totalQuestions > 0 && $answeredQuestions > 0);
                
                $aspekData['indikators'][] = [
                    'id' => $indikator->id,
                    'kode' => $indikator->kode ?? '',
                    'nama' => substr($indikator->nama ?? '', 0, 80),
                    'answered' => $answeredQuestions,
                    'total' => $totalQuestions,
                    'completed' => $isComplete
                ];

                $aspekData['total'] += $totalQuestions;
                $aspekData['answered'] += $answeredQuestions;
                
                $totalIndikators++;
                if ($isComplete) {
                    $completedIndikators++;
                }
            }

            if (count($aspekData['indikators']) > 0) {
                $ringkasan[] = $aspekData;
            }
        }

        // Calculate overall progress
        $overallProgress = $totalIndikators > 0 ? round(($completedIndikators / $totalIndikators) * 100) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => $pengisian->periode->tahun ?? '',
                'upp' => $pengisian->upp->nama ?? '',
                'status' => $pengisian->status,
                'total_indikators' => $totalIndikators,
                'completed_indikators' => $completedIndikators,
                'overall_progress' => $overallProgress,
                'aspeks' => $ringkasan
            ]
        ]);
    }

    // API endpoint: Save/update bukti dukung (URL link) for an indikator
    public function saveBukti(Request $request, $pengisianId, $indikatorId)
    {
        try {
            $user = $request->user();
            $pengisian = F01Pengisian::findOrFail($pengisianId);
            
            $this->authorize('view', $pengisian);

            // Validate input
            $validated = $request->validate([
                'path_atau_url' => ['required', 'url', 'max:2000'],
                'nama' => ['nullable', 'string', 'max:255'],
                'keterangan' => ['nullable', 'string', 'max:500']
            ]);

            // Get or create F01IndikatorNilai
            $indikatorNilai = \App\Models\F01IndikatorNilai::firstOrCreate(
                [
                    'f01_pengisian_id' => $pengisianId,
                    'indikator_id' => $indikatorId
                ],
                [
                    'status' => 'draft'
                ]
            );

            // Delete existing bukti if any (to maintain 1 per indikator)
            $indikatorNilai->bukti()->delete();

            // Create new bukti
            $bukti = \App\Models\F01IndikatorBukti::create([
                'f01_indikator_nilai_id' => $indikatorNilai->id,
                'jenis' => 'url',
                'path_atau_url' => $validated['path_atau_url'],
                'nama' => $validated['nama'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti dukung berhasil disimpan',
                'data' => [
                    'id' => $bukti->id,
                    'path_atau_url' => $bukti->path_atau_url,
                    'nama' => $bukti->nama,
                    'keterangan' => $bukti->keterangan,
                    'created_at' => $bukti->created_at
                ]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengakses data ini: ' . $e->getMessage()
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error saving bukti:', [
                'pengisianId' => $pengisianId,
                'indikatorId' => $indikatorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan bukti dukung: ' . $e->getMessage()
            ], 500);
        }
    }

    // API endpoint: Get bukti dukung for an indikator
    public function getBukti($pengisianId, $indikatorId)
    {
        $pengisian = F01Pengisian::findOrFail($pengisianId);
        
        // Get bukti for this indikator in this pengisian
        $buktiData = \App\Models\F01IndikatorNilai::where('f01_pengisian_id', $pengisianId)
            ->where('indikator_id', $indikatorId)
            ->with('bukti')
            ->first();

        if (!$buktiData || !$buktiData->bukti || $buktiData->bukti->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => null
            ]);
        }

        // bukti is a collection - get the first one
        $bukti = $buktiData->bukti->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $bukti->id,
                'path_atau_url' => $bukti->path_atau_url,
                'nama' => $bukti->nama,
                'keterangan' => $bukti->keterangan,
                'created_at' => $bukti->created_at
            ]
        ]);

    }

    // API endpoint: Delete bukti dukung
    public function deleteBukti(Request $request, $pengisianId, $buktiId)
    {
        $user = $request->user();
        $pengisian = F01Pengisian::findOrFail($pengisianId);
        
        $this->authorize('view', $pengisian);

        try {
            $bukti = \App\Models\F01IndikatorBukti::with('nilai')
                ->where('id', $buktiId)
                ->whereHas('nilai', function($q) use ($pengisianId) {
                    $q->where('f01_pengisian_id', $pengisianId);
                })
                ->firstOrFail();

            $indikatorId = $bukti->nilai->indikator_id;
            $bukti->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bukti dukung berhasil dihapus',
                'indikator_id' => $indikatorId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus bukti dukung: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint: Mark indikator as changed by UPP
     */
    public function markIndikatorChanged(Request $request, $pengisianId, $indikatorId)
    {
        $user = $request->user();
        $pengisian = F01Pengisian::findOrFail($pengisianId);
        
        $this->authorize('update', $pengisian);

        try {
            $indikatorNilai = \App\Models\F01IndikatorNilai::firstOrCreate([
                'f01_pengisian_id' => $pengisianId,
                'indikator_id' => $indikatorId,
            ]);

            $indikatorNilai->update([
                'is_changed' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Indikator ditandai sebagai berubah'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status indikator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all bukti dan jawaban for an aspek
     * Performs validation and automatic status transition
     */
    public function saveBuktiDanJawaban(Request $request, $pengisianId, $aspekId)
    {
        // CRITICAL: Log entry point IMMEDIATELY
        $entryLog = "\n\n╔════════════════════════════════════════════╗\n";
        $entryLog .= "║  SAVE ATTEMPT: Pengisian {$pengisianId}, Aspek {$aspekId}\n";
        $entryLog .= "║  Time: " . date('Y-m-d H:i:s') . "\n";
        $entryLog .= "║  Request method: " . $request->method() . "\n";
        $entryLog .= "║  Payload size: " . strlen(json_encode($request->all())) . " bytes\n";
        $entryLog .= "║  User: " . ($request->user() ? $request->user()->name : 'GUEST') . "\n";
        $entryLog .= "╚════════════════════════════════════════════╝\n";
        file_put_contents('/tmp/f01_debug.log', $entryLog, FILE_APPEND);

        try {
            $user = $request->user();
            $pengisian = F01Pengisian::with('periode')->findOrFail($pengisianId);
            
            // Check authorization
            $this->authorize('update', $pengisian);

            // Check if periode is accepting input (status_pengisian must be 'open')
            if (!$pengisian->periode || $pengisian->periode->status_pengisian !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode tidak menerima input baru. Hubungi administrator jika ada pertanyaan.',
                    'errors' => []
                ], 403);
            }

            // Validate input
            $validated = $request->validate([
                'jawaban' => 'required|array',
                'jawaban.*.pertanyaan_id' => 'required|integer',
                'jawaban.*.nilai' => 'nullable|string',
                'bukti' => 'required|array',
                'bukti.*.indikator_id' => 'required|integer',
                'bukti.*.path_atau_url' => 'required|url',
            ]);

            // Log incoming request
            $debugLog = "\n=== F01 INCOMING REQUEST ===\n";
            $debugLog .= "Raw POST data: " . json_encode($request->all(), JSON_PRETTY_PRINT) . "\n";
            $debugLog .= "User: " . $user->id . " (" . $user->name . ")\n";
            $debugLog .= "Pengisian: " . $pengisianId . ", Aspek: " . $aspekId . "\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);

            // Get aspek with indikators
            $aspek = \App\Models\Aspek::with(['indikator' => function($q) {
                $q->with(['pertanyaan']);
            }])->findOrFail($aspekId);

            // Validate all questions are answered
            $answeredQuestions = [];
            $skippedQuestions = [];
            foreach ($validated['jawaban'] as $j) {
                if ($j['nilai'] !== null && $j['nilai'] !== '') {
                    $answeredQuestions[] = $j['pertanyaan_id'];
                }
            }

            $debugLog = "\n=== F01 SAVE DEBUG ===\n";
            $debugLog .= "User: " . $user->id . " (" . $user->name . ")\n";
            $debugLog .= "Pengisian: " . $pengisianId . ", Aspek: " . $aspekId . "\n";
            $debugLog .= "Jawaban received: " . json_encode($validated['jawaban']) . "\n";
            $debugLog .= "Answered questions: " . json_encode($answeredQuestions) . "\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);

            // Check all required questions are answered (with skip logic support)
            $requiredQuestionsInAspek = [];
            $skippedQuestions = [];
            
            // First pass: determine which questions should be skipped
            foreach ($aspek->indikator as $indikator) {
                $pertanyaanOrdered = $indikator->pertanyaan->sortBy('urutan');
                $skipRemaining = false;
                $pertanyaanArray = $pertanyaanOrdered->all();
                
                foreach ($pertanyaanArray as $idx => $pertanyaan) {
                    // 1. Check if already in skip mode from previous question
                    if ($skipRemaining) {
                        $skippedQuestions[] = $pertanyaan->id;
                        $debugLog = "Q{$pertanyaan->id}: SKIPPED (previous question triggered skip)\n";
                        file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
                        continue;
                    }
                    
                    // 2. Get answer for this question
                    $answer = null;
                    foreach ($validated['jawaban'] as $j) {
                        if ($j['pertanyaan_id'] == $pertanyaan->id) {
                            $answer = $j['nilai'];
                            break;
                        }
                    }
                    
                    // 3. Check if THIS question triggers skip (only if it has an answer)
                    if ($pertanyaan->skip_if_answer && $answer !== null && (string)$answer === (string)$pertanyaan->skip_if_answer) {
                        $debugLog = "Q{$pertanyaan->id}: TRIGGERS SKIP (answer='{$answer}' matches skip_if_answer='{$pertanyaan->skip_if_answer}')\n";
                        file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
                        $skipRemaining = true;
                        // Mark FOLLOWING questions as skipped
                        for ($j = $idx + 1; $j < count($pertanyaanArray); $j++) {
                            $skippedQuestions[] = $pertanyaanArray[$j]->id;
                            $debugLog = "  → Q{$pertanyaanArray[$j]->id} marked as skipped\n";
                            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
                        }
                        break;  // Stop processing remaining questions in this indikator
                    }
                    
                    // 4. Only add to required if NOT skipped and is wajib
                    if ($pertanyaan->wajib && !in_array($pertanyaan->id, $skippedQuestions)) {
                        $requiredQuestionsInAspek[] = $pertanyaan->id;
                        $debugLog = "Q{$pertanyaan->id}: REQUIRED (wajib=true, not skipped)\n";
                        file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
                    }
                }
            }

            $debugLog = "\nSUMMARY:\n";
            $debugLog .= "Required questions: " . json_encode($requiredQuestionsInAspek) . "\n";
            $debugLog .= "Skipped questions: " . json_encode($skippedQuestions) . "\n";
            $debugLog .= "Answered questions: " . json_encode($answeredQuestions) . "\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);

            $missingQuestions = array_diff($requiredQuestionsInAspek, $answeredQuestions);
            $debugLog = "Missing questions: " . json_encode($missingQuestions) . "\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);

            if (!empty($missingQuestions)) {
                $debugLog = "❌ VALIDATION FAILED - Missing: " . json_encode($missingQuestions) . "\n\n";
                file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
                return response()->json([
                    'success' => false,
                    'message' => 'Semua pertanyaan wajib harus dijawab sebelum menyimpan aspek ini.',
                    'errors' => ['missing_questions' => $missingQuestions]
                ], 422);
            }
            
            $debugLog = "✅ VALIDATION PASSED - All required questions answered\n\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);

            // Validate all bukti links are provided for indikators in this aspek
            $buktiMap = [];
            foreach ($validated['bukti'] as $b) {
                $buktiMap[$b['indikator_id']] = $b['path_atau_url'];
            }

            $missingBukti = [];
            foreach ($aspek->indikator as $indikator) {
                if (!isset($buktiMap[$indikator->id])) {
                    $missingBukti[] = $indikator->id;
                }
            }

            if (!empty($missingBukti)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Semua indikator harus memiliki bukti dukung masukkan sebelum menyimpan aspek ini.',
                    'errors' => ['missing_bukti' => $missingBukti]
                ], 422);
            }

            // Save jawaban
            DB::beginTransaction();

            // Get all pertanyaan untuk context (untuk normalisasi yesno values)
            $pertanyaanContext = \App\Models\Pertanyaan::pluck('tipe_input', 'id');

            $debugLog = "\n=== SAVING JAWABAN ===\n";
            $debugLog .= "Total jawaban to save: " . count($validated['jawaban']) . "\n";
            $debugLog .= "Jawaban array (first 5): " . json_encode(array_slice($validated['jawaban'], 0, 5), JSON_PRETTY_PRINT) . "\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);

            $savedCount = 0;
            $nullCount = 0;
            foreach ($validated['jawaban'] as $j) {
                $nilai = $j['nilai'] ?? null;
                
                if ($nilai === null) {
                    $nullCount++;
                } else {
                    // Normalize yesno values to lowercase untuk consistency
                    if ($pertanyaanContext[$j['pertanyaan_id']] === 'yesno') {
                        $nilai = strtolower((string)$nilai);
                    }
                }
                
                try {
                    $jawaban = \App\Models\F01Jawaban::updateOrCreate(
                        [
                            'f01_pengisian_id' => $pengisianId,
                            'pertanyaan_id' => $j['pertanyaan_id'],
                        ],
                        [
                            'nilai' => $nilai,
                        ]
                    );
                    $savedCount++;
                } catch (\Exception $e) {
                    $debugLog = "ERROR saving Q{$j['pertanyaan_id']}: " . $e->getMessage() . "\n";
                    file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
                    throw $e;
                }
            }
            
            $debugLog = "Jawaban save summary: {$savedCount} total, {$nullCount} null values\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);

            // Save bukti
            foreach ($validated['bukti'] as $b) {
                // Get or create indikator nilai record
                $indikatorNilai = \App\Models\F01IndikatorNilai::firstOrCreate([
                    'f01_pengisian_id' => $pengisianId,
                    'indikator_id' => $b['indikator_id'],
                ]);

                // Create or update bukti
                \App\Models\F01IndikatorBukti::updateOrCreate(
                    ['f01_indikator_nilai_id' => $indikatorNilai->id],
                    [
                        'jenis' => 'url',
                        'path_atau_url' => $b['path_atau_url'],
                        'nama' => $b['nama'] ?? null,
                        'keterangan' => $b['keterangan'] ?? null
                    ]
                );
            }

            // Update or create aspek pengisian status
            $aspekPengisian = \App\Models\F01AspekPengisian::updateOrCreate(
                [
                    'f01_pengisian_id' => $pengisianId,
                    'aspek_id' => $aspekId,
                ],
                [
                    'status' => 'tersimpan',  // Automatic status transition
                    'last_saved_at' => now(),
                ]
            );

            // ===== NEW WORKFLOW =====
            // Update pengisian status based on aspek statuses
            $this->updatePengisianStatus($pengisianId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aspek berhasil disimpan',
                'data' => [
                    'aspek_id' => $aspekId,
                    'status' => $aspekPengisian->status,
                    'last_saved_at' => $aspekPengisian->last_saved_at
                ]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah penilaian ini. Hubungi admin jika ada pertanyaan.',
                'errors' => []
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $debugLog = "❌ VALIDATION EXCEPTION: " . json_encode($e->errors(), JSON_PRETTY_PRINT) . "\n\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            $debugLog = "❌ FATAL EXCEPTION: " . get_class($e) . "\n";
            $debugLog .= "Message: " . $e->getMessage() . "\n";
            $debugLog .= "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            $debugLog .= "Stack: " . $e->getTraceAsString() . "\n\n";
            file_put_contents('/tmp/f01_debug.log', $debugLog, FILE_APPEND);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan aspek: ' . $e->getMessage(),
                'errors' => []
            ], 500);
        }
    }

    /**
     * Get aspek status for all aspeks in pengisian
     */
    public function getAspekStatus(Request $request, $pengisianId)
    {
        $user = $request->user();
        $pengisian = F01Pengisian::with('periode')->findOrFail($pengisianId);
        
        $this->authorize('view', $pengisian);

        try {
            $statuses = \App\Models\F01AspekPengisian::where('f01_pengisian_id', $pengisianId)
                ->get()
                ->keyBy('aspek_id');

            return response()->json([
                'success' => true,
                'data' => $statuses->map(function($item) {
                    return [
                        'aspek_id' => $item->aspek_id,
                        'status' => $item->status,
                        'last_saved_at' => $item->last_saved_at
                    ];
                })->values()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status aspek: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update pengisian status berdasarkan status aspek-aspeknya
     * 
     * Status pengisian:
     * - draft: tidak ada aspek yang tersimpan
     * - submitted: >= 1 aspek tersimpan, tapi belum semua divalidasi
     * - selesai: semua aspek sudah divalidasi
     */
    private function updatePengisianStatus($pengisianId)
    {
        // Get all aspek statuses for this pengisian
        $aspekStatuses = \App\Models\F01AspekPengisian::where('f01_pengisian_id', $pengisianId)->get();
        
        if ($aspekStatuses->isEmpty()) {
            // No aspeks saved yet, status remains draft
            return;
        }

        // Count aspek by status
        $tersimpanCount = $aspekStatuses->where('status', 'tersimpan')->count();
        $divalidasiCount = $aspekStatuses->where('status', 'divalidasi')->count();
        $totalCount = $aspekStatuses->count();

        // Determine new status
        $newStatus = 'draft';
        
        if ($divalidasiCount === $totalCount) {
            // Semua aspek sudah divalidasi
            $newStatus = 'selesai';
        } else if ($tersimpanCount > 0 || $divalidasiCount > 0) {
            // Ada minimal 1 aspek yang tersimpan atau divalidasi
            $newStatus = 'submitted';
        }

        // Update pengisian status
        F01Pengisian::where('id', $pengisianId)->update([
            'status' => $newStatus
        ]);

        \Log::info('F01 Pengisian status updated', [
            'pengisian_id' => $pengisianId,
            'new_status' => $newStatus,
            'tersimpan_count' => $tersimpanCount,
            'divalidasi_count' => $divalidasiCount,
            'total_aspeks' => $totalCount
        ]);
    }

    /**
     * Finalize pengisian - submit for F02 validation
     * Checks that all questions in all indikators are answered
     */
    public function finalize(F01Pengisian $pengisian)
    {
        try {
            // Check authorization
            $this->authorize('update', $pengisian);

            // Only allow finalization if status is draft
            if ($pengisian->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengisian hanya bisa difinalisasi jika masih dalam status draft'
                ], 422);
            }

            // Check if periode is accepting input
            if ($pengisian->periode && $pengisian->periode->status_pengisian !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode tidak menerima input baru. Hubungi administrator.'
                ], 422);
            }

            // Simply update status to submitted
            $pengisian->update([
                'status' => 'submitted',
                'submitted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengisian berhasil difinalisasi dan masuk ke tahap validasi F02',
                'status' => 'submitted'
            ], 200);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('F01 Finalize Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save pengisian - save jawaban (triggered by frontend)
     */
    public function autoSave(Request $request, $pengision)
    {
        // CRITICAL: Log autoSave entry point
        $log = "\n\n╔════════════════════════════════════════════╗\n";
        $log .= "║  AUTO-SAVE: Pengisian {$pengision}\n";
        $log .= "║  Time: " . date('Y-m-d H:i:s') . "\n";
        $log .= "║  Payload items: " . count($request->all()) . "\n";
        $log .= "║  Jawaban keys: " . implode(", ", array_filter(array_keys($request->all()), fn($k) => strpos($k, 'jawaban_') === 0)) . "\n";
        $log .= "║  User: " . ($request->user() ? $request->user()->name : 'GUEST') . "\n";
        $log .= "╚════════════════════════════════════════════╝\n";
        file_put_contents('/tmp/f01_debug.log', $log, FILE_APPEND);
        
        // Handle parameter name - route has typo 'pengision' instead of 'pengisian'
        $id = $pengision;
        $pengisian = F01Pengisian::findOrFail($id);
        
        $this->authorize('update', $pengisian);

        // Only allow if status is draft
        if ($pengisian->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Pengisian tidak bisa diubah dalam status ' . $pengisian->status
            ], 422);
        }

        try {
            $data = $request->all();
            $lainnyaValues = []; // Collect "Lainnya" values

            // First, extract "jawaban_lainnya_*" values
            foreach ($data as $key => $value) {
                if (strpos($key, 'jawaban_lainnya_') === 0 && !empty($value)) {
                    $pertanyaanId = str_replace('jawaban_lainnya_', '', $key);
                    $lainnyaValues[$pertanyaanId] = $value;
                }
            }

            // Process jawaban inputs
            $processedCount = 0;
            foreach ($data as $key => $value) {
                if (strpos($key, 'jawaban_') === 0 && strpos($key, 'jawaban_lainnya_') !== 0) {
                    // Extract ID and remove trailing [] from checkbox names
                    $pertanyaanId = str_replace('jawaban_', '', $key);
                    $pertanyaanId = str_replace('[]', '', $pertanyaanId);
                    
                    // Get existing jawaban
                    $existingJawaban = \App\Models\F01Jawaban::where([
                        'f01_pengisian_id' => $pengisian->id,
                        'pertanyaan_id' => $pertanyaanId
                    ])->first();
                    
                    // Parse nilai - handle arrays (checkboxes) and single values
                    $nilai = $value;
                    $isArray = is_array($nilai);
                    
                    // Handle case where FormData with [] suffix creates an array container
                    if (is_array($nilai) && count($nilai) === 1 && is_string($nilai[0])) {
                        // Check if the single element is a JSON string
                        $singleValue = $nilai[0];
                        if (!empty($singleValue) && $singleValue[0] === '[') {
                            $decoded = @json_decode($singleValue, true);
                            if (is_array($decoded)) {
                                // It's a JSON array wrapped in a container array - unwrap and use the JSON string
                                file_put_contents('/tmp/f01_debug.log', "  > Q{$pertanyaanId}: Unwrapping container array\n", FILE_APPEND);
                                $nilai = $singleValue; // Store the JSON string directly
                                $isArray = true;
                            }
                        }
                    }
                    
                    // If value is already a JSON string from FormData (checkboxes), validate it
                    if (is_string($nilai) && !empty($nilai) && $nilai[0] === '[') {
                        // This is a JSON string from FormData, validate it
                        $decoded = @json_decode($nilai, true);
                        if (is_array($decoded)) {
                            // Valid JSON string - store as-is without re-encoding
                            file_put_contents('/tmp/f01_debug.log', "  > Q{$pertanyaanId}: Valid JSON string, storing as-is\n", FILE_APPEND);
                            // $nilai is already the final JSON string, don't encode again
                            $isArray = true;
                        }
                    } elseif (is_array($nilai)) {
                        // Array from traditional form submission - encode to JSON
                        
                        // If it's checkbox array with "Lainnya" value, merge with custom text
                        if (isset($lainnyaValues[$pertanyaanId])) {
                            // Create special structure for "Lainnya"
                            $nilai = array_merge(
                                array_filter($nilai, fn($v) => $v !== '__lainnya__'),
                                ['lainnya' => $lainnyaValues[$pertanyaanId]]
                            );
                        }
                        
                        // Convert array to JSON for storage
                        $nilai = json_encode($nilai, JSON_UNESCAPED_UNICODE);
                    }
                    
                    // Check if this is an empty checkbox array
                    $isEmptyCheckbox = ($nilai === '[]' || (is_array($nilai) && count($nilai) === 0));
                    
                    if ($isEmptyCheckbox) {
                        // For empty checkboxes: delete the jawaban record
                        \App\Models\F01Jawaban::where([
                            'f01_pengisian_id' => $pengisian->id,
                            'pertanyaan_id' => $pertanyaanId
                        ])->delete();
                        
                        $logJawaban = "  Q{$pertanyaanId}: DELETED (empty checkbox) ✓\n";
                        file_put_contents('/tmp/f01_debug.log', $logJawaban, FILE_APPEND);
                    } elseif ($value !== '' && $value !== null) {
                        // Save or update jawaban (non-empty values)
                        // Note: $value !== '' and $value !== null allows 0 to be saved!
                        $result = \App\Models\F01Jawaban::updateOrCreate(
                            [
                                'f01_pengisian_id' => $pengisian->id,
                                'pertanyaan_id' => $pertanyaanId
                            ],
                            [
                                'nilai' => $nilai
                            ]
                        );
                        $processedCount++;
                        
                        // Log individual jawaban save with actual stored value
                        $logJawaban = "  Q{$pertanyaanId}: " . ($isArray ? "ARRAY" : "single") . " = " . substr($nilai, 0, 80) . (strlen($nilai) > 80 ? "..." : "") . " [STORED: " . substr($result->nilai ?? 'NULL', 0, 50) . "] ✓\n";
                        file_put_contents('/tmp/f01_debug.log', $logJawaban, FILE_APPEND);
                    }
                }
            }

            // Process bukti_dukung_url inputs
            foreach ($data as $key => $value) {
                if (strpos($key, 'bukti_dukung_url_') === 0) {
                    $indikatorId = str_replace('bukti_dukung_url_', '', $key);
                    
                    // Save or update bukti_dukung URL
                    if (!empty($value)) {
                        \App\Models\F01BuktiDukung::updateOrCreate(
                            [
                                'f01_pengisian_id' => $pengisian->id,
                                'indikator_id' => $indikatorId
                            ],
                            [
                                'url_bukti' => $value
                            ]
                        );
                    } else {
                        // Delete if URL is empty
                        \App\Models\F01BuktiDukung::where([
                            'f01_pengisian_id' => $pengisian->id,
                            'indikator_id' => $indikatorId
                        ])->delete();
                    }
                }
            }

            // Log summary
            $logSummary = "║  Processed: {$processedCount} jawaban\n";
            $logSummary .= "╚════════════════════════════════════════════╝\n";
            file_put_contents('/tmp/f01_debug.log', $logSummary, FILE_APPEND);

            return response()->json([
                'success' => true,
                'message' => 'Data tersimpan',
                'processed' => $processedCount
            ], 200);

        } catch (\Exception $e) {
            // Log error
            file_put_contents('/tmp/f01_debug.log', "║  ERROR: " . $e->getMessage() . "\n╚════════════════════════════════════════════╝\n", FILE_APPEND);
            
            \Log::error('Auto-save error', [
                'pengisian_id' => $pengisian->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
}
