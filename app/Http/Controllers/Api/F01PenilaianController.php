<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\F01Pengisian;
use App\Models\F01Jawaban;
use App\Models\Periode;
use App\Models\Upp;
use App\Models\Indikator;
use App\Models\Pertanyaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class F01PenilaianController extends Controller
{
    /**
     * GET /api/f01/pengisian/{periodeId}/{uppId}
     * Create or Get draft pengisian untuk periode & UPP tertentu
     */
    public function getPengisian(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $uppId = $request->input('upp_id');
        
        $periode = Periode::findOrFail($periodeId);
        $upp = Upp::findOrFail($uppId);
        
        /**
         * Create or get existing draft pengisian
         */
        $pengisian = F01Pengisian::firstOrCreate(
            [
                'periode_id' => $periodeId,
                'upp_id' => $uppId
            ],
            [
                'status' => 'draft',
                'dikirim_oleh' => auth()->id()
            ]
        );
        
        /**
         * Load structure: Aspeks -> Indikators -> Pertanyaans
         */
        $aspeks = $this->getStructureWithAnswers($pengisian);
        
        return response()->json([
            'success' => true,
            'data' => [
                'pengisian_id' => $pengisian->id,
                'periode' => $periode,
                'upp' => $upp,
                'status' => $pengisian->status,
                'aspeks' => $aspeks,
                'created_at' => $pengisian->created_at,
                'updated_at' => $pengisian->updated_at
            ]
        ]);
    }

    /**
     * GET /api/f01/pengisian/{pengisianId}
     * Get specific pengisian with all its answers (for viewing/editing)
     */
    public function show($pengisianId)
    {
        $pengisian = F01Pengisian::with('periode', 'upp')->findOrFail($pengisianId);
        
        // Force fresh load of jawaban (not from eager load cache)
        // This ensures we get latest saved answers, especially after saveAspek
        $pengisian->load('jawaban.pertanyaan');
        
        $aspeks = $this->getStructureWithAnswers($pengisian);
        
        return response()->json([
            'success' => true,
            'data' => [
                'pengisian_id' => $pengisian->id,
                'periode' => $pengisian->periode,
                'upp' => $pengisian->upp,
                'status' => $pengisian->status,
                'aspeks' => $aspeks,
                'created_at' => $pengisian->created_at,
                'updated_at' => $pengisian->updated_at
            ]
        ]);
    }

    /**
     * POST /api/f01/submit
     * Save all answers from cache to database
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'pengisian_id' => 'required|exists:f01_pengisian,id',
            'answers' => 'required|array',
            'answers.*.pertanyaan_id' => 'required|exists:pertanyaan,id',
            'answers.*.nilai' => 'nullable'
        ]);
        
        $pengisianId = $validated['pengisian_id'];
        $pengisian = F01Pengisian::findOrFail($pengisianId);
        
        /**
         * Validate that all required questions are answered
         */
        $validationResult = $this->validateAnswers($validated['answers']);
        if (!$validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ada pertanyaan yang belum diisi',
                'errors' => $validationResult['errors']
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            /**
             * Bulk update/insert all answers
             */
            foreach ($validated['answers'] as $answer) {
                F01Jawaban::updateOrCreate(
                    [
                        'f01_pengisian_id' => $pengisianId,
                        'pertanyaan_id' => $answer['pertanyaan_id']
                    ],
                    [
                        'nilai' => $answer['nilai'] ?? null
                    ]
                );
            }
            
            /**
             * Update pengisian status to submitted
             */
            $pengisian->update([
                'status' => 'submitted',
                'dikirim_pada' => now(),
                'dikirim_oleh' => auth()->id()
            ]);
            
            /**
             * Calculate indicator values (if method exists)
             */
            $this->calculateIndicatorValues($pengisian);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disubmit',
                'pengisian_id' => $pengisian->id,
                'status' => $pengisian->status
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat submit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/f01/validate
     * Validate answers per aspek (for per-aspek validation)
     */
    public function validateAspek(Request $request)
    {
        $aspekId = $request->input('aspek_id');
        $answers = $request->input('answers', []);
        
        /**
         * Get all indikators for this aspek
         */
        $indikators = Indikator::where('aspek_id', $aspekId)->get();
        
        $errors = [];
        $valid = true;
        
        foreach ($indikators as $indikator) {
            /**
             * Get all questions for this indikator
             */
            $questions = Pertanyaan::where('indikator_id', $indikator->id)
                                   ->where('parent_pertanyaan_id', null)
                                   ->aktif()
                                   ->ordered()
                                   ->get();
            
            foreach ($questions as $question) {
                /**
                 * Check if question should be skipped
                 */
                $skipped = $this->isQuestionSkipped($question, $answers, $indikator->id);
                if ($skipped) {
                    continue;
                }
                
                /**
                 * Check if required and answered
                 */
                if ($question->wajib) {
                    $answer = $answers[strval($question->id)] ?? null;
                    
                    if (empty($answer) || $answer === '') {
                        $errors[$question->id] = "{$question->label} wajib diisi";
                        $valid = false;
                    }
                }
            }
        }
        
        return response()->json([
            'valid' => $valid,
            'errors' => $errors
        ]);
    }

    /**
     * Get structure: Aspeks -> Indikators -> Questions with cached answers
     */
    private function getStructureWithAnswers($pengisian)
    {
        /**
         * Load all answers for this pengisian
         */
        $jawaban = $pengisian->jawaban()
            ->with('pertanyaan')
            ->get()
            ->keyBy('pertanyaan_id');
        
        /**
         * Get all aspeks
         */
        $aspeks = \App\Models\Aspek::orderBy('urutan')->get();
        
        $result = $aspeks->map(function ($aspek) use ($jawaban) {
            $indikators = Indikator::where('aspek_id', $aspek->id)
                                   ->orderBy('urutan')
                                   ->get();
            
            $indikatorData = $indikators->map(function ($indikator) use ($jawaban) {
                $questions = Pertanyaan::where('indikator_id', $indikator->id)
                                       ->where('parent_pertanyaan_id', null)
                                       ->aktif()
                                       ->ordered()
                                       ->get();
                
                $questionData = $questions->map(function ($question) use ($jawaban) {
                    $answer = $jawaban->get($question->id);
                    
                    return [
                        'id' => $question->id,
                        'label' => $question->label,
                        'kode' => $question->kode,
                        'urutan' => $question->urutan,
                        'tipe_input' => $question->tipe_input,
                        'wajib' => $question->wajib,
                        'aktif' => $question->aktif,
                        'nilai' => $answer?->nilai,
                        'answered' => $answer && $answer->nilai !== null && $answer->nilai !== '',
                        'opsi_jawaban' => $question->opsi_jawaban,
                        'min' => $question->min,
                        'max' => $question->max,
                        'skip_if_answer' => $question->skip_if_answer,
                        'parent_pertanyaan_id' => $question->parent_pertanyaan_id,
                        'show_when' => $question->show_when,
                        'conditional_questions' => $this->getConditionalQuestions($question, $jawaban)
                    ];
                });
                
                $answeredCount = $questionData->where('answered', true)->count();
                $totalCount = $questionData->count();
                
                return [
                    'id' => $indikator->id,
                    'nama' => $indikator->nama,
                    'kode' => $indikator->kode,
                    'urutan' => $indikator->urutan,
                    'deskripsi' => $indikator->deskripsi ?? null,
                    'questions' => $questionData,
                    'answered' => $answeredCount,
                    'total' => $totalCount,
                    'progress' => $totalCount > 0 ? round(($answeredCount / $totalCount) * 100) : 0
                ];
            });
            
            $totalAnswered = $indikatorData->sum(function ($ind) {
                return $ind['answered'];
            });
            $totalQuestions = $indikatorData->sum(function ($ind) {
                return $ind['total'];
            });
            
            return [
                'id' => $aspek->id,
                'nama' => $aspek->nama,
                'kode' => $aspek->kode,
                'urutan' => $aspek->urutan,
                'deskripsi' => $aspek->deskripsi ?? null,
                'indikators' => $indikatorData,
                'answered' => $totalAnswered,
                'total' => $totalQuestions,
                'progress' => $totalQuestions > 0 ? round(($totalAnswered / $totalQuestions) * 100) : 0
            ];
        });
        
        return $result;
    }

    /**
     * Get conditional questions for a parent question
     */
    private function getConditionalQuestions($parentQuestion, $jawaban)
    {
        $conditionals = Pertanyaan::where('parent_pertanyaan_id', $parentQuestion->id)
                                  ->aktif()
                                  ->ordered()
                                  ->get();
        
        return $conditionals->map(function ($q) use ($jawaban) {
            $answer = $jawaban->get($q->id);
            return [
                'id' => $q->id,
                'label' => $q->label,
                'tipe_input' => $q->tipe_input,
                'nilai' => $answer?->nilai,
                'answered' => $answer && $answer->nilai !== null && $answer->nilai !== '',
                'show_when' => $q->show_when,
                'opsi_jawaban' => $q->opsi_jawaban
            ];
        });
    }

    /**
     * Check if question should be skipped based on previous answers
     */
    private function isQuestionSkipped($question, $answers, $indikatorId)
    {
        if (!$question->skip_if_answer) {
            return false;
        }
        
        /**
         * Find all questions before this in the same indikator
         */
        $previousQuestions = Pertanyaan::where('indikator_id', $indikatorId)
                                       ->where('parent_pertanyaan_id', null)
                                       ->where('urutan', '<', $question->urutan)
                                       ->aktif()
                                       ->ordered()
                                       ->get();
        
        foreach ($previousQuestions as $prevQ) {
            if ($prevQ->skip_if_answer) {
                $prevAnswer = $answers[strval($prevQ->id)] ?? null;
                if ($prevAnswer && strtolower($prevAnswer) === strtolower($prevQ->skip_if_answer)) {
                    // Previous question triggered skip
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Validate all answers (required fields check)
     */
    private function validateAnswers($answers)
    {
        $errors = [];
        
        foreach ($answers as $answer) {
            $question = Pertanyaan::find($answer['pertanyaan_id']);
            
            if (!$question) continue;
            
            if ($question->wajib && (empty($answer['nilai']) || $answer['nilai'] === '')) {
                $errors[$question->id] = "{$question->label} wajib diisi";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Calculate indicator values (stub for future implementation)
     */
    private function calculateIndicatorValues($pengisian)
    {
        // TODO: Implement scoring logic
        // Will calculate F01IndikatorNilai based on answers
    }
}
