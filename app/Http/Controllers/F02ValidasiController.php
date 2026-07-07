<?php

namespace App\Http\Controllers;

use App\Models\F01Pengisian;
use App\Models\F01Jawaban;
use App\Models\F01AspekPengisian;
use App\Models\F01BuktiDukung;
use App\Models\F02Validasi;
use App\Models\F02IndikatorValidasi;
use App\Models\F02Skor;
use App\Models\Periode;
use App\Models\Aspek;
use App\Models\Indikator;
use App\Models\Upp;
use App\Services\F01ResubmitService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;

class F02ValidasiController extends Controller
{
    protected $resubmitService;
    
    public function __construct(F01ResubmitService $resubmitService)
    {
        $this->resubmitService = $resubmitService;
    }
    
    /**
     * List semua F01 pengisian yang pending validasi
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get active period first
        $periode = Periode::where('is_aktif', 1)->first();
        $isAcceptingInput = true;
        
        // If no active periode, check if periode_id is in request
        if (!$periode && $request->has('periode_id')) {
            $periode = Periode::find($request->periode_id);
        }
        
        // If still no periode, use latest periode
        if (!$periode) {
            $periode = Periode::latest('tahun')->first();
        }
        
        // Check if periode is accepting input
        if ($periode) {
            $isAcceptingInput = $periode->status_pengisian === 'open';
        }
        
        // Query F01 pengisian that need validation (latest version only, submitted or completed)
        $query = F01Pengisian::with(['periode', 'upp', 'f02.divalidasiOleh:id,nama', 'f02.updatedBy:id,nama'])
            ->where('status', '!=', 'draft')
            ->where('is_latest_version', true)
            ->orderBy('created_at', 'desc');
        
        // Filter by periode if provided
        if ($request->has('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        } elseif ($periode) {
            $query->where('periode_id', $periode->id);
        }
        
        // Load the F02 validasi status too
        $pengisians = $query->get()->map(function($pengisian) {
            $f02 = $pengisian->f02;
            $pengisian->f02_status = $f02?->status ?? 'belum_divalidasi';
            $pengisian->f02_id = $f02?->id;
            $pengisian->f02_nilai = $f02?->total_nilai;
            $pengisian->f02_nilai_mentah = $f02?->nilai_mentah;
            $pengisian->f02_validator = $f02?->divalidasiOleh;
            $pengisian->f02_updated_by = $f02?->updatedBy;
            return $pengisian;
        });
        
        // Get UPP progress - use same logic as export for consistency
        // Gather progress data untuk UPP yang sudah MULAI MENGISI (minimum 1 jawaban)
        $all_upps = Upp::orderBy('nama', 'asc')->get();
        
        $uppDalamProgressDetail = $all_upps->map(function($upp) use ($periode, $request) {
            // Get latest F01 pengisian untuk UPP dan periode ini
            $query = F01Pengisian::where('upp_id', $upp->id);
            
            if ($request->has('periode_id')) {
                $query->where('periode_id', $request->periode_id);
            } elseif ($periode) {
                $query->where('periode_id', $periode->id);
            }
            
            $latestF01 = $query->latest('created_at')->first();
            
            // Skip if no F01 pengisian at all
            if (!$latestF01) {
                return null;
            }
            
            // Skip if F01 status is not draft (already submitted or completed)
            if ($latestF01->status !== 'draft') {
                return null;
            }
            
            // Skip if F02 already completed (selesai)
            $f02 = $latestF01->f02;
            if ($f02 && $f02->status === 'selesai') {
                return null;
            }
            
            // Total indikators in periode
            $totalIndikator = $latestF01->periode->indikator()->count();
            
            // Count distinct indikators that have at least 1 answered pertanyaan
            $answeredIndikators = DB::table('f01_jawaban')
                ->join('pertanyaan', 'f01_jawaban.pertanyaan_id', '=', 'pertanyaan.id')
                ->where('f01_jawaban.f01_pengisian_id', $latestF01->id)
                ->distinct('pertanyaan.indikator_id')
                ->count('pertanyaan.indikator_id');
            
            // Skip if no jawaban at all (tidak ada progres)
            if ($answeredIndikators === 0) {
                return null;
            }
            
            // Calculate progress percentage based on indikators
            $progress = $totalIndikator > 0 ? intval(($answeredIndikators / $totalIndikator) * 100) : 0;
            
            return [
                'upp_id' => $upp->id,
                'upp_nama' => $upp->nama,
                'total_indikator' => $totalIndikator,
                'answered_indikator' => $answeredIndikators,
                'aspek_progress' => $progress,
                'last_update' => $latestF01->updated_at,
                'status' => $latestF01->status,
            ];
        })
        ->filter(fn($item) => $item !== null)
        ->values();
        
        $uppDalamProgress = $uppDalamProgressDetail->count();
        
        // Get list of all periods (not filtered by is_aktif to allow viewing historical data)
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        
        return view('f02.index', [
            'pengisians' => $pengisians,
            'periodes' => $periodes,
            'activePeriode' => $periode,
            'isAcceptingInput' => $isAcceptingInput,
            'uppDalamProgress' => $uppDalamProgress,
            'uppDalamProgressDetail' => $uppDalamProgressDetail,
        ]);
    }

    /**
     * Inisiasi validasi form untuk satu pengisian F01 - redirect ke aspek-list
     */
    public function initValidasi(Request $request, $id)
    {
        $pengisian = F01Pengisian::findOrFail($id);
        
        // Load atau create F02 validasi
        $f02 = F02Validasi::firstOrCreate(
            ['f01_pengisian_id' => $pengisian->id],
            [
                'periode_id' => $pengisian->periode_id,
                'status' => 'draft'
            ]
        );
        
        // Redirect to aspek-list
        return redirect()->route('f02.aspek-list', ['validasi' => $f02->id]);
    }

    /**
     * Show aspek list untuk validasi dengan progress per aspek
     */
    public function aspekList(Request $request, $validasiId)
    {
        $validasi = F02Validasi::with(['periode', 'f01Pengisian.upp'])
            ->findOrFail($validasiId);
        
        $pengisian = $validasi->f01Pengisian;
        
        // Load aspeks dengan indikators
        $aspeks = Aspek::where('periode_id', $pengisian->periode_id)
            ->with(['indikator' => function($q) {
                $q->orderBy('urutan', 'asc');
            }])
            ->orderBy('urutan', 'asc')
            ->get();
        
        // Load F02 indikator validasi untuk progress tracking
        $f02IndikatorValidasi = F02IndikatorValidasi::where('f02_validasi_id', $validasi->id)
            ->get()
            ->keyBy('indikator_id');
        
        // Build aspek data dengan progress
        $aspeksData = $aspeks->map(function($aspek) use ($f02IndikatorValidasi, $pengisian) {
            $indicatorsList = $aspek->indikator;
            $filledCount = 0;
            $skorMentah = 0;
            $changedCount = 0;
            
            $changedIndikators = [];
            if ($pengisian->version_number > 1) {
                $changedIndikators = \App\Models\F01IndikatorNilai::where('f01_pengisian_id', $pengisian->id)
                    ->where('is_changed', true)
                    ->pluck('indikator_id')
                    ->toArray();
            }
            
            foreach ($indicatorsList as $ind) {
                if (in_array($ind->id, $changedIndikators)) {
                    $changedCount++;
                }

                if (isset($f02IndikatorValidasi[$ind->id])) {
                    $nilaiObj = $f02IndikatorValidasi[$ind->id];
                    if (!is_null($nilaiObj->nilai)) {
                        $filledCount++;
                        $skorMentah += $nilaiObj->nilai;
                    }
                }
            }
            
            $total = $indicatorsList->count();
            $progress = $total > 0 ? round(($filledCount / $total) * 100) : 0;
            
            return [
                'aspek' => $aspek,
                'total_indikators' => $total,
                'filled_indikators' => $filledCount,
                'progress' => $progress,
                'skor_mentah' => round($skorMentah, 2),
                'changed_count' => $changedCount,
            ];
        });
        
        return view('f02.aspek-list', [
            'validasi' => $validasi,
            'aspeks' => $aspeksData,
        ]);
    }

    /**
     * Show detail validasi per aspek dan indikator
     */
    public function validasiDetail(Request $request, $validasiId, $aspekId)
    {
        $validasi = F02Validasi::with(['periode', 'f01Pengisian.upp'])
            ->findOrFail($validasiId);
        
        $pengisian = $validasi->f01Pengisian;
        $aspek = Aspek::findOrFail($aspekId);
        
        // Get indikators for this aspek
        $indikators = $aspek->indikator()
            ->orderBy('urutan', 'asc')
            ->get();
        
        // Get first indikator or specified one from query param
        $indikatorId = $request->get('indikator', $indikators->first()?->id);
        $indikator = Indikator::findOrFail($indikatorId);
        
        // Load questions for this indikator
        // IMPORTANT: Load ONLY aktif questions and exclude conditional child questions
        $pertanyaan = Indikator::findOrFail($indikatorId)
            ->pertanyaan()
            ->where('aktif', 1)  // Only active questions
            ->whereNull('parent_pertanyaan_id')  // Only main questions (not conditional children)
            ->orderBy('urutan', 'asc')
            ->with(['jawaban' => function($q) use ($pengisian) {
                $q->where('f01_pengisian_id', $pengisian->id);
            }])
            ->get();
        
        // Load F02 skor untuk periode ini
        $skorData = F02Skor::where('indikator_id', $indikatorId)
            ->where('periode_id', $pengisian->periode_id)
            ->first();
        
        $skors = [];
        if ($skorData) {
            for ($i = 0; $i <= 5; $i++) {
                $fieldName = "skor_$i";
                if ($skorData->$fieldName) {
                    $skors[$i] = $skorData->$fieldName;
                }
            }
        }
        
        // Load existing F02 indikator validasi
        $indikatorValidasi = F02IndikatorValidasi::where('f02_validasi_id', $validasi->id)
            ->where('indikator_id', $indikatorId)
            ->first();
        
        // Load bukti dukung for this indikator
        $buktiDukung = F01BuktiDukung::where('f01_pengisian_id', $pengisian->id)
            ->where('indikator_id', $indikatorId)
            ->get();
            
        // Get status for all indikators in this aspek
        $indikatorStatuses = [];
        if ($pengisian->version_number > 1) {
            $f01NilaiList = \App\Models\F01IndikatorNilai::where('f01_pengisian_id', $pengisian->id)
                ->whereIn('indikator_id', $indikators->pluck('id'))
                ->get()->keyBy('indikator_id');
                
            $f02ValidasiList = \App\Models\F02IndikatorValidasi::where('f02_validasi_id', $validasiId)
                ->whereIn('indikator_id', $indikators->pluck('id'))
                ->get()->keyBy('indikator_id');
                
            foreach ($indikators as $ind) {
                $indikatorStatuses[$ind->id] = [
                    'is_changed' => isset($f01NilaiList[$ind->id]) ? $f01NilaiList[$ind->id]->is_changed : false,
                    'is_carried_over' => isset($f02ValidasiList[$ind->id]) ? $f02ValidasiList[$ind->id]->is_carried_over : false,
                ];
            }
        }
        
        return view('f02.validasi-detail', [
            'validasi' => $validasi,
            'aspek' => $aspek,
            'indikator' => $indikator,
            'indikators' => $indikators,
            'pertanyaan' => $pertanyaan,
            'skors' => $skors,
            'indikatorValidasi' => $indikatorValidasi,
            'buktiDukung' => $buktiDukung,
            'indikatorStatuses' => $indikatorStatuses,
            'isResubmit' => $pengisian->version_number > 1,
        ]);
    }

    /**
     * Auto-save skor dan catatan per indikator (AJAX)
     */
    public function autoSave(Request $request, $validasiId, $indikatorId)
    {
        try {
            $request->validate([
                'nilai' => 'required|integer|min:0|max:5',
                'catatan' => 'nullable|string',
            ]);
            
            $validasi = F02Validasi::findOrFail($validasiId);
            $currentUser = $request->user();
            
            // Save atau update F02 indikator validasi
            F02IndikatorValidasi::updateOrCreate(
                [
                    'f02_validasi_id' => $validasi->id,
                    'indikator_id' => $indikatorId,
                ],
                [
                    'nilai' => $request->nilai,
                    'catatan' => $request->catatan ?? '',
                    'status' => 'draft',
                ]
            );
            
            // Update F02Validasi status to 'dalam_proses' and track who updated
            // Only update if user is authenticated
            if ($currentUser) {
                if ($validasi->status === 'draft') {
                    $validasi->update([
                        'status' => 'dalam_proses',
                        'updated_by' => $currentUser->id,
                    ]);
                } else {
                    $validasi->update([
                        'updated_by' => $currentUser->id,
                    ]);
                }
                \Log::info("F02Validasi {$validasi->id} updated by user {$currentUser->id}");
            } else {
                \Log::warning("F02Validasi {$validasi->id} autoSave: user not authenticated");
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Skor berhasil disimpan',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("F02ValidasiController::autoSave error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan skor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save indikator - handle form submission (POST)
     */
    public function saveIndikator(Request $request, $validasiId, $indikatorId)
    {
        try {
            $request->validate([
                'nilai' => 'required|integer|min:0|max:5',
                'catatan' => 'nullable|string',
            ]);
            
            $validasi = F02Validasi::findOrFail($validasiId);
            $currentUser = $request->user();
            
            // Save F02 indikator validasi
            F02IndikatorValidasi::updateOrCreate(
                [
                    'f02_validasi_id' => $validasi->id,
                    'indikator_id' => $indikatorId,
                ],
                [
                    'nilai' => $request->nilai,
                    'catatan' => $request->catatan ?? '',
                    'status' => 'draft',
                ]
            );
            
            // Update F02Validasi status to 'dalam_proses' and track who updated
            if ($currentUser) {
                if ($validasi->status === 'draft') {
                    $validasi->update([
                        'status' => 'dalam_proses',
                        'updated_by' => $currentUser->id,
                    ]);
                } else {
                    $validasi->update([
                        'updated_by' => $currentUser->id,
                    ]);
                }
                \Log::info("F02Validasi {$validasi->id} updated by user {$currentUser->id}");
            } else {
                \Log::warning("F02Validasi {$validasi->id} saveIndikator: user not authenticated");
            }
            
            // Redirect back
            return redirect()->route('f02.aspek-list', ['validasi' => $validasiId])
                ->with('success', 'Skor indikator berhasil disimpan');
        } catch (\Exception $e) {
            \Log::error("F02ValidasiController::saveIndikator error: " . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Gagal menyimpan skor: ' . $e->getMessage()]);
        }
    }

    /**
     * Finalize validasi F02 - check all indikators have nilai, then submit
     */
    public function finalizeValidation(Request $request, $validasiId)
    {
        try {
            $validasi = F02Validasi::with(['periode', 'f01Pengisian'])
                ->findOrFail($validasiId);
            
            $pengisian = $validasi->f01Pengisian;
            
            // Load aspeks dan indikators
            $aspeks = Aspek::where('periode_id', $pengisian->periode_id)
                ->with('indikator')
                ->get();
            
            // Check all indikators have nilai
            $allIndikatorIds = [];
            foreach ($aspeks as $aspek) {
                $allIndikatorIds = array_merge($allIndikatorIds, $aspek->indikator->pluck('id')->toArray());
            }
            
            $validatedIndikators = F02IndikatorValidasi::where('f02_validasi_id', $validasi->id)
                ->whereIn('indikator_id', $allIndikatorIds)
                ->whereNotNull('nilai')
                ->pluck('indikator_id')
                ->toArray();
            
            if (count($validatedIndikators) !== count($allIndikatorIds)) {
                $missingCount = count($allIndikatorIds) - count($validatedIndikators);
                return response()->json([
                    'success' => false,
                    'message' => "Masih ada $missingCount indikator yang belum divalidasi",
                ], 422);
            }
            
            // Calculate total nilai (weighted by aspek bobot)
            $totalNilai = 0;
            foreach ($aspeks as $aspek) {
                $bobot = $aspek->bobot ?? 0;
                $indikatorIds = $aspek->indikator->pluck('id')->toArray();
                
                $nilaiList = F02IndikatorValidasi::where('f02_validasi_id', $validasi->id)
                    ->whereIn('indikator_id', $indikatorIds)
                    ->whereNotNull('nilai')
                    ->pluck('nilai')
                    ->toArray();
                
                if (!empty($nilaiList)) {
                    $avgNilaiAspek = array_sum($nilaiList) / count($nilaiList);
                    $totalNilai += ($avgNilaiAspek * $bobot) / 100;
                }
            }
            
            // Calculate nilai mentah (raw score - sum of all indikator scores without weighting)
            $allIndikatorNilaiList = F02IndikatorValidasi::where('f02_validasi_id', $validasi->id)
                ->whereNotNull('nilai')
                ->pluck('nilai')
                ->toArray();
            
            $nilaiMentah = !empty($allIndikatorNilaiList) 
                ? array_sum($allIndikatorNilaiList)
                : 0;
            
            // Update F02 status selesai
            $validasi->update([
                'status' => 'selesai',
                'total_nilai' => round($totalNilai, 2),
                'nilai_mentah' => round($nilaiMentah, 2),
                'divalidasi_oleh' => $request->user()?->id,
                'divalidasi_pada' => now(),
            ]);
            
            // Update F01 pengisian status
            $pengisian->update(['status' => 'selesai']);
            
            return response()->json([
                'success' => true,
                'message' => 'Validasi F02 berhasil diselesaikan',
                'data' => [
                    'total_nilai' => round($totalNilai, 2),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan validasi: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get average nilai untuk indikator (dari jawaban pertanyaannya)
     */
    private function getAverageNilaiForIndikator($pengisian, $indikator)
    {
        $pertanyaan = $indikator->pertanyaan()->pluck('id')->toArray();
        
        $jawaban = F01Jawaban::where('f01_pengisian_id', $pengisian->id)
            ->whereIn('pertanyaan_id', $pertanyaan)
            ->get();
        
        if ($jawaban->isEmpty()) {
            return 0;
        }
        
        // Count 'ya' answers
        $yaCount = $jawaban->filter(fn($j) => strtolower($j->nilai) === 'ya')->count();
        $totalCount = $jawaban->count();
        
        // Calculate percentage (0-100) then normalize to 1-5 scale
        $percentage = ($yaCount / $totalCount) * 100;
        return round(($percentage / 100) * 5, 1);
    }
    
    /**
     * Calculate total nilai F02 berdasarkan nilai indikator dan bobot aspek
     */
    private function calculateTotalNilai($aspekData)
    {
        $total = 0;
        
        foreach ($aspekData as $aspek) {
            $bobot = $aspek['bobot'] ?? 0;
            
            // Calculate average nilai for this aspek
            $nilaiList = [];
            foreach ($aspek['indikators'] as $ind) {
                if (!is_null($ind['nilai'])) {
                    $nilaiList[] = $ind['nilai'];
                }
            }
            
            if (!empty($nilaiList)) {
                $avgNilaiAspek = array_sum($nilaiList) / count($nilaiList);
                $total += ($avgNilaiAspek * $bobot) / 100;
            }
        }
        
        return round($total, 2);
    }

    /**
     * Save validasi (draft) dengan nilai indikator 1-5 dan catatan wajib
     */
    public function save(Request $request, $id)
    {
        try {
            $request->validate([
                'nilai' => 'required|array',
                'nilai.*' => 'required|integer|min:1|max:5',
                'catatan' => 'required|array',
                'catatan.*' => 'required|string|min:5',
            ]);
            
            $user = $request->user();
            $pengisian = F01Pengisian::findOrFail($id);
            
            // Get or create F02
            $f02 = F02Validasi::where('f01_pengisian_id', $pengisian->id)->firstOrFail();
            
            DB::beginTransaction();
            
            // Save nilai dan catatan per indikator
            foreach ($request->nilai as $indikatorId => $nilai) {
                F02IndikatorValidasi::updateOrCreate(
                    [
                        'f02_validasi_id' => $f02->id,
                        'indikator_id' => $indikatorId,
                    ],
                    [
                        'nilai' => $nilai,
                        'catatan' => $request->catatan[$indikatorId] ?? '',
                        'status' => 'draft',
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Validasi tersimpan sebagai draft',
                'data' => [
                    'f02_id' => $f02->id,
                    'status' => $f02->status,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Finalize validasi (approve) - hitung total nilai F02
     */
    public function finalize(Request $request, $id)
    {
        if (!$request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        try {
            $request->validate([
                'nilai' => 'required|array',
                'nilai.*' => 'required|integer|min:0|max:5',
                'catatan' => 'required|array',
                'catatan.*' => 'required|string|min:5',
            ]);
            
            $user = $request->user();
            $pengisian = F01Pengisian::findOrFail($id);
            
            // Get F02
            $f02 = F02Validasi::where('f01_pengisian_id', $pengisian->id)->firstOrFail();
            
            DB::beginTransaction();
            
            // Save final nilai dan catatan per indikator
            foreach ($request->nilai as $indikatorId => $nilai) {
                F02IndikatorValidasi::updateOrCreate(
                    [
                        'f02_validasi_id' => $f02->id,
                        'indikator_id' => $indikatorId,
                    ],
                    [
                        'nilai' => $nilai,
                        'catatan' => $request->catatan[$indikatorId] ?? '',
                        'status' => 'final',
                    ]
                );
            }
            
            // Load aspek dengan bobot untuk menghitung total nilai
            $aspeks = Aspek::where('periode_id', $pengisian->periode_id)
                ->with('indikator')
                ->get();
            
            $totalNilai = 0;
            foreach ($aspeks as $aspek) {
                $bobot = $aspek->bobot ?? 0;
                $indikatorIds = $aspek->indikator->pluck('id')->toArray();
                
                // Get nilai untuk indikator di aspek ini
                $nilaiList = F02IndikatorValidasi::where('f02_validasi_id', $f02->id)
                    ->whereIn('indikator_id', $indikatorIds)
                    ->whereNotNull('nilai')
                    ->pluck('nilai')
                    ->toArray();
                
                if (!empty($nilaiList)) {
                    $avgNilaiAspek = array_sum($nilaiList) / count($nilaiList);
                    $totalNilai += ($avgNilaiAspek * $bobot) / 100;
                }
            }
            
            // Update F02 status dan simpan total nilai
            $f02->update([
                'status' => 'selesai',
                'total_nilai' => round($totalNilai, 2),
                'divalidasi_oleh' => $user->id,
                'divalidasi_pada' => now(),
            ]);
            
            // ===== NEW WORKFLOW =====
            // Mark all aspeks as divalidasi (validation complete for all aspeks)
            F01AspekPengisian::where('f01_pengisian_id', $pengisian->id)
                ->update(['status' => 'divalidasi']);
            
            // Update F01 pengisian status to selesai (all aspeks validated)
            $pengisian->update([
                'status' => 'selesai',
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Validasi berhasil diselesaikan',
                'data' => [
                    'f02_id' => $f02->id,
                    'status' => $f02->status,
                    'total_nilai' => $f02->total_nilai,
                    'f01_status' => $pengisian->status,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('F02 Finalize Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject validasi - return ke user untuk perbaikan
     */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'nilai' => 'required|array',
                'nilai.*' => 'required|integer|min:0|max:5',
                'catatan' => 'required|array',
                'catatan.*' => 'required|string|min:5',
            ]);
            
            $user = $request->user();
            $pengisian = F01Pengisian::findOrFail($id);
            
            // Get F02
            $f02 = F02Validasi::where('f01_pengisian_id', $pengisian->id)->firstOrFail();
            
            DB::beginTransaction();
            
            // Save nilai dan catatan untuk rejection
            foreach ($request->nilai as $indikatorId => $nilai) {
                F02IndikatorValidasi::updateOrCreate(
                    [
                        'f02_validasi_id' => $f02->id,
                        'indikator_id' => $indikatorId,
                    ],
                    [
                        'nilai' => $nilai,
                        'catatan' => $request->catatan[$indikatorId] ?? '',
                        'status' => 'rejected',
                    ]
                );
            }
            
            // Update F01 status ke rolled_back untuk perbaikan
            $pengisian->update([
                'status' => 'rolled_back',
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pengisian ditolak dan dikembalikan untuk perbaikan',
                'data' => [
                    'f02_id' => $f02->id,
                    'f01_status' => $pengisian->status,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export progress laporan F02 - gather data untuk semua UPP dengan status mereka
     */
    public function exportProgressReport(Request $request)
    {
        $periode = Periode::where('is_aktif', 1)->first();
        if (!$periode) {
            abort(404, 'Periode aktif tidak ditemukan');
        }

        // Gather progress data untuk semua UPP
        $allUpps = \App\Models\Upp::orderBy('nama', 'asc')->get();
        
        $progressData = $allUpps->map(function($upp) use ($periode) {
            // Get latest F01 pengisian untuk UPP dan periode ini
            $latestF01 = F01Pengisian::where('upp_id', $upp->id)
                ->where('periode_id', $periode->id)
                ->latest('created_at')
                ->first();
            
            // Determine status
            $status = 'belum_memulai';
            
            if ($latestF01) {
                if ($latestF01->status === 'draft') {
                    // Check if has at least 1 jawaban
                    $hasJawaban = DB::table('f01_jawaban')
                        ->where('f01_pengisian_id', $latestF01->id)
                        ->exists();
                    
                    $status = $hasJawaban ? 'dalam_pengisian' : 'belum_memulai';
                } else {
                    // F01 is submitted/completed (status != 'draft')
                    $f02 = F02Validasi::where('f01_pengisian_id', $latestF01->id)->first();
                    $status = $f02?->status === 'selesai' 
                        ? 'selesai_validasi' 
                        : 'submit';
                }
            }
            
            return [
                'upp_id' => $upp->id,
                'upp_nama' => $upp->nama,
                'belum_memulai' => $status === 'belum_memulai' ? 1 : 0,
                'dalam_pengisian' => $status === 'dalam_pengisian' ? 1 : 0,
                'submit' => $status === 'submit' ? 1 : 0,
                'selesai_validasi' => $status === 'selesai_validasi' ? 1 : 0,
                'status' => $status,
            ];
        });

        // Calculate totals
        $totals = [
            'belum_memulai' => $progressData->sum('belum_memulai'),
            'dalam_pengisian' => $progressData->sum('dalam_pengisian'),
            'submit' => $progressData->sum('submit'),
            'selesai_validasi' => $progressData->sum('selesai_validasi'),
            'total_upp' => $progressData->count(),
        ];

        // Determine format dan return
        $format = $request->query('format', 'csv');
        
        if ($format === 'pdf') {
            return $this->exportProgressReportPdf($progressData, $totals, $periode);
        } else {
            return $this->exportProgressReportCsv($progressData, $totals, $periode);
        }
    }

    /**
     * Export progress report ke CSV
     */
    private function exportProgressReportCsv($progressData, $totals, $periode)
    {
        $fileName = 'F02_Progress_' . $periode->tahun . '_' . now()->format('d-m-Y_Hi') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($progressData, $totals, $periode) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fputs($file, "\xEF\xBB\xBF");
            
            // Header
            fputcsv($file, [
                'Laporan Progress Pengisian F02 - ' . $periode->tahun,
                'Tanggal Export: ' . now()->format('d/m/Y H:i:s'),
            ]);
            fputcsv($file, []);
            
            // Column headers
            fputcsv($file, [
                'No.',
                'Unit Pelayanan Publik',
                'Belum Memulai',
                'Dalam Pengisian',
                'Submit',
                'Selesai Validasi',
            ]);
            
            // Data
            foreach ($progressData as $idx => $row) {
                fputcsv($file, [
                    $idx + 1,
                    $row['upp_nama'],
                    $row['belum_memulai'],
                    $row['dalam_pengisian'],
                    $row['submit'],
                    $row['selesai_validasi'],
                ]);
            }
            
            // Empty line
            fputcsv($file, []);
            
            // Summary
            fputcsv($file, [
                'TOTAL',
                '',
                $totals['belum_memulai'],
                $totals['dalam_pengisian'],
                $totals['submit'],
                $totals['selesai_validasi'],
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export progress report ke PDF
     */
    private function exportProgressReportPdf($progressData, $totals, $periode)
    {
        $fileName = 'F02_Progress_' . $periode->tahun . '_' . now()->format('d-m-Y_Hi') . '.pdf';
        
        // Sort data by status priority: selesai_validasi > submit > dalam_pengisian > belum_memulai
        $statusPriority = [
            'selesai_validasi' => 0,
            'submit' => 1,
            'dalam_pengisian' => 2,
            'belum_memulai' => 3,
        ];
        
        $sortedData = $progressData->sort(function($a, $b) use ($statusPriority) {
            $priorityA = $statusPriority[$a['status']] ?? 99;
            $priorityB = $statusPriority[$b['status']] ?? 99;
            return $priorityA <=> $priorityB;
        })->values();
        
        $html = view('f02.export.progress-pdf', [
            'progressData' => $sortedData,
            'totals' => $totals,
            'periode' => $periode,
        ])->render();
        
        // Initialize DomPDF
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Courier');
        $options->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $pdfContent = $dompdf->output();
        
        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Get dashboard overview - basic statistics for validation progress
     * Returns: total_pengisian, pengisian_divalidasi, pengisian_ditolak, rata_nilai
     */
    public function dashboardOverview(Request $request)
    {
        try {
            $periode = Periode::where('is_aktif', 1)->first();
            
            if (!$periode && $request->has('periode_id')) {
                $periode = Periode::find($request->periode_id);
            }
            
            if (!$periode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode tidak ditemukan',
                ], 404);
            }
            
            // Total F01 pengisian submitted/completed
            $totalPengisian = F01Pengisian::where('periode_id', $periode->id)
                ->where('status', '!=', 'draft')
                ->count();
            
            // Pengisian yang sudah selesai divalidasi
            $pengisianDivalidasi = F02Validasi::where('periode_id', $periode->id)
                ->where('status', 'selesai')
                ->count();
            
            // Pengisian yang ditolak
            $pengisianDitolak = F01Pengisian::where('periode_id', $periode->id)
                ->where('status', 'rolled_back')
                ->count();
            
            // Rata-rata nilai dari yang sudah selesai divalidasi
            $rataaNilai = 0;
            if ($pengisianDivalidasi > 0) {
                $rataaNilai = F02Validasi::where('periode_id', $periode->id)
                    ->where('status', 'selesai')
                    ->avg('total_nilai');
            }
            
            // Validasi dalam proses
            $validasiDalamProses = F02Validasi::where('periode_id', $periode->id)
                ->where('status', 'dalam_proses')
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'periode_id' => $periode->id,
                    'periode_tahun' => $periode->tahun,
                    'total_pengisian' => $totalPengisian,
                    'pengisian_divalidasi' => $pengisianDivalidasi,
                    'pengisian_ditolak' => $pengisianDitolak,
                    'validasi_dalam_proses' => $validasiDalamProses,
                    'rata_nilai' => round($rataaNilai, 2),
                    'completion_percentage' => $totalPengisian > 0 
                        ? round(($pengisianDivalidasi / $totalPengisian) * 100, 2)
                        : 0,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('F02ValidasiController::dashboardOverview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil overview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get validasi progress data - for chart showing progress by periode
     * Returns: array of months/periods with their validation counts
     */
    public function dashboardValidasiProgress(Request $request)
    {
        try {
            $periode = Periode::where('is_aktif', 1)->first();
            
            if (!$periode && $request->has('periode_id')) {
                $periode = Periode::find($request->periode_id);
            }
            
            if (!$periode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode tidak ditemukan',
                ], 404);
            }
            
            // Get all F02 validasi for this periode, grouped by week/day
            $validasiPerTanggal = F02Validasi::where('periode_id', $periode->id)
                ->selectRaw('DATE(divalidasi_pada) as tanggal, status, COUNT(*) as count')
                ->where('divalidasi_pada', '!=', null)
                ->groupBy('tanggal', 'status')
                ->orderBy('tanggal', 'asc')
                ->get();
            
            // Reshape data for frontend
            $progressData = [];
            foreach ($validasiPerTanggal as $item) {
                $tanggal = $item->tanggal ?? 'pending';
                if (!isset($progressData[$tanggal])) {
                    $progressData[$tanggal] = [
                        'tanggal' => $tanggal,
                        'selesai' => 0,
                        'draft' => 0,
                        'dalam_proses' => 0,
                        'total' => 0,
                    ];
                }
                
                $status = $item->status ?? 'draft';
                $progressData[$tanggal][$status] = $item->count;
                $progressData[$tanggal]['total'] += $item->count;
            }
            
            // Also get summary by status
            $statusSummary = F02Validasi::where('periode_id', $periode->id)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'periode_id' => $periode->id,
                    'periode_tahun' => $periode->tahun,
                    'progress_per_tanggal' => array_values($progressData),
                    'status_summary' => $statusSummary,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('F02ValidasiController::dashboardValidasiProgress error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get aspek comparison data - average nilai per aspek across all validations
     * Returns: array of aspeks with average nilai
     */
    public function dashboardAspekComparison(Request $request)
    {
        try {
            $periode = Periode::where('is_aktif', 1)->first();
            
            if (!$periode && $request->has('periode_id')) {
                $periode = Periode::find($request->periode_id);
            }
            
            if (!$periode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode tidak ditemukan',
                ], 404);
            }
            
            // Get all aspeks for this periode
            $aspeks = Aspek::where('periode_id', $periode->id)
                ->orderBy('urutan', 'asc')
                ->get();
            
            $aspekData = [];
            foreach ($aspeks as $aspek) {
                // Get all indikators for this aspek
                $indikatorIds = $aspek->indikator()->pluck('id')->toArray();
                
                // Calculate average nilai for these indikators from all F02 validasi
                $avgNilai = F02IndikatorValidasi::whereIn('indikator_id', $indikatorIds)
                    ->whereNotNull('nilai')
                    ->avg('nilai');
                
                // Count how many indikators have been validated
                $validatedCount = F02IndikatorValidasi::whereIn('indikator_id', $indikatorIds)
                    ->whereNotNull('nilai')
                    ->distinct('indikator_id')
                    ->count('indikator_id');
                
                $aspekData[] = [
                    'aspek_id' => $aspek->id,
                    'aspek_nama' => $aspek->nama,
                    'aspek_urutan' => $aspek->urutan,
                    'bobot' => $aspek->bobot ?? 0,
                    'avg_nilai' => round($avgNilai ?? 0, 2),
                    'validated_indikators' => $validatedCount,
                    'total_indikators' => count($indikatorIds),
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'periode_id' => $periode->id,
                    'periode_tahun' => $periode->tahun,
                    'aspek_comparison' => $aspekData,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('F02ValidasiController::dashboardAspekComparison error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil perbandingan aspek: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Allow single UPP to resubmit - create new F01 version
     * POST /f02/{f02ValidasiId}/allow-resubmit
     */
    public function allowResubmit(Request $request, F02Validasi $f02Validasi)
    {
        // Policy: only admin/superadmin
        if (!$request->user()->isAdmin() && !$request->user()->isSuperadmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki hak akses',
            ], 403);
        }
        
        try {
            $f01New = $this->resubmitService->allowResubmit(
                $f02Validasi,
                $request->user(),
                ['catatan' => $request->input('catatan', null)]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'UPP diizinkan mengisi ulang F01',
                'f01_id' => $f01New->id,
                'version_number' => $f01New->version_number,
                'upp_id' => $f01New->upp_id,
                'upp_nama' => $f01New->upp->nama,
            ]);
        } catch (\Exception $e) {
            \Log::error('F02ValidasiController::allowResubmit error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Bulk allow resubmit untuk multiple F02 validasi
     * POST /f02/allow-resubmit-bulk
     */
    public function allowResubmitBulk(Request $request)
    {
        // Policy: only admin/superadmin
        if (!$request->user()->isAdmin() && !$request->user()->isSuperadmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki hak akses',
            ], 403);
        }
        
        $request->validate([
            'f02_ids' => 'required|array',
            'f02_ids.*' => 'required|integer|exists:f02_validasi,id',
        ]);
        
        try {
            $result = $this->resubmitService->bulkAllowResubmit(
                $request->input('f02_ids'),
                $request->user()
            );
            
            return response()->json([
                'success' => $result['failed_count'] === 0,
                'summary' => [
                    'total_processed' => $result['success'] + $result['failed_count'],
                    'success_count' => $result['success'],
                    'failed_count' => $result['failed_count'],
                    'failed_details' => $result['failed_details'],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('F02ValidasiController::allowResubmitBulk error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk resubmit: ' . $e->getMessage(),
            ], 500);
        }
    }
}

