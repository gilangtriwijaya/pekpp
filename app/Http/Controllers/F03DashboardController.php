<?php

namespace App\Http\Controllers;

use App\Models\F03Token;
use App\Models\F03Pengisian;
use App\Models\F03Jawaban;
use App\Models\F03Aspek;
use App\Models\F02Validasi;
use App\Models\F02IndikatorValidasi;
use App\Models\Aspek;
use App\Models\Upp;
use Illuminate\Http\Request;

class F03DashboardController extends Controller
{
    /**
     * Redirect UPP users to their dashboard for active periode
     */
    public function userDashboard(Request $request)
    {
        $user = $request->user();

        // Get active periode
        $periode = \App\Models\Periode::where('is_aktif', 1)->first();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif. Hubungi administrator.');
        }

        // Get UPP for this user
        $uppIds = collect($user->getUserUpps())->filter(function($u){
            return (bool) ($u->aktif ?? true);
        })->pluck('upp_id')->unique()->values()->all();

        if (empty($uppIds)) {
            return redirect()->back()->with('error', 'User tidak memiliki akses ke UPP manapun.');
        }

        $uppId = $uppIds[0];

        // Redirect to UPP dashboard
        return redirect()->route('f03.dashboard.upp', ['uppId' => $uppId, 'periodeId' => $periode->id]);
    }

    /**
     * Dashboard for UPP (their data only)
     */
    public function uppDashboard(Request $request, $uppId, $periodeId)
    {
        $user = $request->user();

        // Check authorization - ensure user has access to this UPP
        if (!$user->hasRole('superadmin')) {
            $hasAccess = $user->getUserUpps()->contains(function ($userUpp) use ($uppId) {
                return (int)$userUpp->upp_id === (int)$uppId && (bool)$userUpp->aktif;
            });

            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke UPP ini.');
            }
        }

        $upp = Upp::findOrFail($uppId);
        $periode = \App\Models\Periode::findOrFail($periodeId);

        $token = F03Token::where('upp_id', $uppId)
            ->where('periode_id', $periodeId)
            ->with(['upp', 'periode'])
            ->firstOrFail();

        // Get responses
        $pengisianQuery = F03Pengisian::where('f03_token_id', $token->id);
        $totalResponses = $pengisianQuery->count();
        $uniqueResponses = F03Pengisian::where('f03_token_id', $token->id)->where('is_duplicate', false)->count();
        $duplicateResponses = F03Pengisian::where('f03_token_id', $token->id)->where('is_duplicate', true)->count();

        // Get pengisian IDs for this token
        $pengisianIds = F03Pengisian::where('f03_token_id', $token->id)->pluck('id');

        // Get aspek scores (only from this token's responses)
        $aspeks = F03Aspek::where('periode_id', $periodeId)->with('indikator')->orderBy('urutan')->get();
        $aspectScores = [];

        foreach ($aspeks as $aspek) {
            $indikatorIds = $aspek->indikator()->pluck('id');
            $avgScore = F03Jawaban::whereIn('f03_indikator_id', $indikatorIds)
                ->whereIn('f03_pengisian_id', $pengisianIds)
                ->avg('score') ?? 0;
            $aspectScores[$aspek->nama] = round($avgScore, 2);
        }

        // Overall score (only from this token's responses)
        $averageScore = F03Jawaban::whereIn('f03_pengisian_id', $pengisianIds)->avg('score') ?? 0;

        // Detailed responses
        $responses = F03Pengisian::where('f03_token_id', $token->id)
            ->with('jawaban.indikator')
            ->orderBy('response_date', 'desc')
            ->paginate(20);

        return view('f03.dashboard.upp', compact(
            'token',
            'upp',
            'periode',
            'totalResponses',
            'uniqueResponses',
            'duplicateResponses',
            'aspectScores',
            'averageScore',
            'responses'
        ));
    }

    /**
     * Global dashboard for admin/superadmin
     */
    public function adminDashboard(Request $request)
    {
        $periodeId = $request->query('periode_id');

        // Get all periodes for filter dropdown
        $periodes = \App\Models\Periode::orderBy('tahun', 'desc')->get();

        // Default to active periode if no filter given
        if (!$periodeId) {
            $aktivePeriode = \App\Models\Periode::where('is_aktif', true)->first()
                ?? $periodes->first();
            if ($aktivePeriode) {
                return redirect()->route('admin.f03.dashboard.admin', ['periode_id' => $aktivePeriode->id]);
            }
        }

        // Resolve target_responden_f03 for selected periode
        $selectedPeriode = $periodeId
            ? \App\Models\Periode::find($periodeId)
            : $periodes->first();
        $targetResponden = (int) ($selectedPeriode?->target_responden_f03 ?? 0);

        // ── Source of truth: ALL active UPPs from upps table ──────────────
        $allUpps = Upp::where('aktif', true)->orderBy('nama')->get();

        // Pre-load tokens for selected periode (or all if no filter)
        $tokensQuery = F03Token::query();
        if ($periodeId) {
            $tokensQuery->where('periode_id', $periodeId);
        }
        // Key by upp_id for O(1) lookup
        $tokensByUpp = $tokensQuery->get()->keyBy('upp_id');

        // Pre-load all pengisian IDs grouped by token
        $tokenIds     = $tokensByUpp->pluck('id')->toArray();
        $totalResponses = F03Pengisian::whereIn('f03_token_id', $tokenIds)->count();

        // Group pengisian by token_id for bulk score query
        $pengisianByToken = F03Pengisian::whereIn('f03_token_id', $tokenIds)
            ->select('id', 'f03_token_id')
            ->get()
            ->groupBy('f03_token_id');

        // Pre-load avg score per token in one query
        $avgScoreByToken = [];
        foreach ($tokenIds as $tid) {
            $pengisianIds = ($pengisianByToken[$tid] ?? collect())->pluck('id')->toArray();
            if (empty($pengisianIds)) {
                $avgScoreByToken[$tid] = 0;
            } else {
                $avgScoreByToken[$tid] = (float) (F03Jawaban::whereIn('f03_pengisian_id', $pengisianIds)->avg('score') ?? 0);
            }
        }

        // Build rankings — every UPP appears
        $rankings       = [];
        $targetMetCount = 0;

        foreach ($allUpps as $upp) {
            $token         = $tokensByUpp->get($upp->id);
            $tokenId       = $token?->id;
            $responseCount = $tokenId ? ($pengisianByToken[$tokenId] ?? collect())->count() : 0;
            $avgScore      = $tokenId ? ($avgScoreByToken[$tokenId] ?? 0) : 0;

            $targetMet      = $targetResponden <= 0 || $responseCount >= $targetResponden;
            $effectiveScore = $targetMet ? round($avgScore, 2) : 0;

            $rankingItem = [
                'upp_id'           => $upp->id,
                'upp_nama'         => $upp->nama,
                'upp_kode'         => $upp->kode ?? '',
                'total_responses'  => $responseCount,
                'average_score'    => $effectiveScore,
                'target_responden' => $targetResponden,
                'target_met'       => $targetMet,
                'has_token'        => !is_null($token),
            ];

            if ($targetMet) {
                $targetMetCount++;
            }

            $rankings[] = $rankingItem;
        }

        // Sort by score desc, then response count desc as tie-breaker
        usort($rankings, function ($a, $b) {
            if ($b['average_score'] != $a['average_score']) {
                return $b['average_score'] <=> $a['average_score'];
            }
            return $b['total_responses'] <=> $a['total_responses'];
        });

        $averageScore = count($rankings) > 0
            ? collect($rankings)->avg('average_score')
            : 0;

        $totalUpps = count($rankings);

        // Split for modal display
        $targetMetUpps    = array_values(array_filter($rankings, fn($r) => $r['target_met']));
        $targetNotMetUpps = array_values(array_filter($rankings, fn($r) => !$r['target_met']));

        $avgScoreMet    = count($targetMetUpps) > 0
            ? round(collect($targetMetUpps)->avg('average_score'), 2)
            : 0;
        $avgScoreNotMet = count($targetNotMetUpps) > 0
            ? round(collect($targetNotMetUpps)->avg('average_score'), 2)
            : 0;

        // ===== F02 DATA =====
        $f02UppFilter = $request->query('f02_upp_id', 'all');

        // Get F02 aspek scores per UPP
        $f02Data = [];

        $f02Query = F02Validasi::where('status', 'selesai')
            ->with(['f01Pengisian.upp', 'periode']);

        if ($periodeId) {
            $f02Query->where('periode_id', $periodeId);
        }

        $f02Validasis = $f02Query->get();

        // Get all aspeks for periods
        $aspeksF02 = Aspek::all();

        // Build F02 UPP scores with aspek breakdown
        $f02UppScores = [];
        $validasiByUpp = [];

        // Group validasi by UPP
        foreach ($f02Validasis as $validasi) {
            $upp = $validasi->f01Pengisian?->upp;
            if (!$upp) continue;

            $uppId = $upp->id;
            if (!isset($validasiByUpp[$uppId])) {
                $validasiByUpp[$uppId] = [
                    'upp' => $upp,
                    'validasis' => []
                ];
            }
            $validasiByUpp[$uppId]['validasis'][] = $validasi;
        }

        // Calculate aspek scores for each UPP
        foreach ($validasiByUpp as $uppId => $data) {
            $upp = $data['upp'];
            $uppValidasis = $data['validasis'];
            $validasiIds = array_map(fn($v) => $v->id, $uppValidasis);

            $f02UppScores[$uppId] = [
                'upp_id' => $uppId,
                'upp_nama' => $upp->nama,
                'upp_kode' => $upp->kode,
                'aspek_scores' => [],
                'total_nilai' => $uppValidasis[0]->total_nilai ?? 0
            ];

            // Calculate aspek scores for this UPP
            foreach ($aspeksF02 as $aspek) {
                $indikatorIds = $aspek->indikator()->pluck('id')->toArray();

                // Get avg nilai for this aspek within this UPP's validasi
                $avgScore = F02IndikatorValidasi::whereIn('indikator_id', $indikatorIds)
                    ->whereIn('f02_validasi_id', $validasiIds)
                    ->avg('nilai') ?? 0;

                $f02UppScores[$uppId]['aspek_scores'][$aspek->nama] = round($avgScore, 2);
            }
        }

        // Get F02 skor per aspek (global average across all UPPs)
        $f02AspekScores = [];
        $allValidasiIds = $f02Validasis->pluck('id')->toArray();

        foreach ($aspeksF02 as $aspek) {
            $indikatorIds = $aspek->indikator()->pluck('id')->toArray();

            // Get avg nilai for this aspek across validasi in this period
            $avgScore = 0;
            if (!empty($allValidasiIds)) {
                $avgScore = F02IndikatorValidasi::whereIn('indikator_id', $indikatorIds)
                    ->whereIn('f02_validasi_id', $allValidasiIds)
                    ->avg('nilai') ?? 0;
            }

            $f02AspekScores[$aspek->nama] = round($avgScore, 2);
        }

        // F02 data for chart
        $f02AverageScore = F02Validasi::where('status', 'selesai')
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->avg('total_nilai') ?? 0;

        $f02TotalValidasi = F02Validasi::where('status', 'selesai')
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->count();

        return view('f03.dashboard.admin', compact(
            'rankings',
            'periodes',
            'totalUpps',
            'totalResponses',
            'averageScore',
            'targetMetCount',
            'periodeId',
            'f02AspekScores',
            'f02AverageScore',
            'f02TotalValidasi',
            'f02UppScores',
            'f02UppFilter',
            'aspeksF02',
            'targetMetUpps',
            'targetNotMetUpps',
            'avgScoreMet',
            'avgScoreNotMet'
        ));
    }

    /**
     * Get detailed response data for modal view
     */
    public function getResponseDetail($pengisianId)
    {
        $pengisian = F03Pengisian::with(['jawaban.indikator.aspek', 'token.upp', 'token.periode'])->findOrFail($pengisianId);

        return response()->json([
            'pengisian' => $pengisian,
            'jawaban' => $pengisian->jawaban->map(function($j) {
                // Determine response value
                $responseValue = '';
                if ($j->score !== null) {
                    $responseValue = $j->score;
                } elseif ($j->response_text !== null) {
                    $decoded = json_decode($j->response_text, true);
                    $responseValue = is_array($decoded) ? implode(', ', $decoded) : $j->response_text;
                }

                return [
                    'indikator' => $j->indikator->pertanyaan,
                    'aspek' => $j->indikator->aspek->nama,
                    'score' => $j->score,
                    'response_text' => $j->response_text,
                    'response_value' => $responseValue,
                    'catatan' => $j->catatan
                ];
            })
        ]);
    }

    /**
     * Export responses to CSV
     */
    public function exportCsv($tokenId)
    {
        $token = F03Token::findOrFail($tokenId);
        $responses = $token->pengisian()->with('jawaban.indikator')->get();

        $csv = "Tanggal,Indikator,Skor,Catatan,Response Text\n";

        foreach ($responses as $response) {
            foreach ($response->jawaban as $jawaban) {
                // Determine the response value
                $responseValue = '';
                if ($jawaban->score !== null) {
                    $responseValue = $jawaban->score;
                } elseif ($jawaban->response_text !== null) {
                    // If response_text is JSON (from checkbox), decode it
                    $decoded = json_decode($jawaban->response_text, true);
                    $responseValue = is_array($decoded) ? implode(', ', $decoded) : $jawaban->response_text;
                }

                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s"\n',
                    $response->response_date->format('Y-m-d H:i:s'),
                    str_replace('"', '""', $jawaban->indikator->pertanyaan ?? ''),
                    $responseValue,
                    str_replace('"', '""', $jawaban->catatan ?? ''),
                    ''
                );
            }
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="f03_responses_' . $token->id . '.csv"');
    }

    /**
     * API endpoint to generate QR code for token
     */
    public function generateQrCodeApi($tokenId)
    {
        try {
            $token = F03Token::findOrFail($tokenId);

            // Generate QR code if not exists
            if (!$token->qr_code) {
                $token->generateQrCode();
            }

            return response()->json([
                'success' => true,
                'qr_code' => $token->qr_code ?: null,
                'message' => $token->qr_code ? 'QR code ready' : 'QR code generating'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
