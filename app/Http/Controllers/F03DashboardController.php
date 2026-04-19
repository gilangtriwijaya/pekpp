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

        // Get all periodes for filter
        $periodes = \App\Models\Periode::all();

        // Build rankings per UPP
        $rankings = [];
        $totalResponses = F03Pengisian::count();
        $averageScore = F03Jawaban::avg('score') ?? 0;
        $targetMetCount = 0;
        $allTokens = [];

        // Get tokens based on periode filter
        $tokensQuery = F03Token::with(['upp', 'periode']);
        if ($periodeId) {
            $tokensQuery->where('periode_id', $periodeId);
        }
        $allTokens = $tokensQuery->get();

        // Build UPP rankings
        $uppScoreMap = [];
        foreach ($allTokens as $token) {
            $uppId = $token->upp_id;
            $upp = $token->upp;
            $periode = $token->periode;

            if (!isset($uppScoreMap[$uppId])) {
                $uppScoreMap[$uppId] = [
                    'upp_id' => $uppId,
                    'upp_nama' => $upp->nama ?? 'Unknown',
                    'upp_kode' => $upp->kode ?? '',
                    'total_responses' => 0,
                    'total_score' => 0,
                    'response_count' => 0,
                    'target_responden' => $periode->target_responden_f03 ?? 0,
                    'target_met' => false
                ];
            }

            // Get responses for this token
            $tokenResponses = F03Pengisian::where('f03_token_id', $token->id)->get();
            $responseCount = $tokenResponses->count();
            $uppScoreMap[$uppId]['total_responses'] += $responseCount;
            $uppScoreMap[$uppId]['response_count']++;

            // Get average score for this token
            $tokenJawabanScores = F03Jawaban::whereIn('f03_pengisian_id', $tokenResponses->pluck('id'))->avg('score') ?? 0;
            $uppScoreMap[$uppId]['total_score'] += $tokenJawabanScores;
        }

        // Calculate averages and build rankings
        foreach ($uppScoreMap as $data) {
            $avgScore = $data['response_count'] > 0 ? $data['total_score'] / $data['response_count'] : 0;
            $targetResponden = (int) ($data['target_responden'] ?? 0);
            $targetMet = $targetResponden <= 0 || (int) $data['total_responses'] >= $targetResponden;
            $effectiveScore = $targetMet ? $avgScore : 0;

            $rankingItem = [
                'upp_id' => $data['upp_id'],
                'upp_nama' => $data['upp_nama'],
                'upp_kode' => $data['upp_kode'],
                'total_responses' => $data['total_responses'],
                'average_score' => round($effectiveScore, 2),
                'target_responden' => $targetResponden,
                'target_met' => $targetMet
            ];

            if ($rankingItem['target_met']) {
                $targetMetCount++;
            }

            $rankings[] = $rankingItem;
        }

        // Sort by score desc, then by response count desc as tie-breaker
        usort($rankings, function($a, $b) {
            // Primary: Sort by average score descending
            if ($b['average_score'] != $a['average_score']) {
                return $b['average_score'] <=> $a['average_score'];
            }
            // Secondary: If scores equal, sort by response count descending (more responses = higher rank)
            return $b['total_responses'] <=> $a['total_responses'];
        });

        $averageScore = count($rankings) > 0
            ? collect($rankings)->avg('average_score')
            : 0;

        $totalUpps = count($rankings);

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
        foreach ($aspeksF02 as $aspek) {
            $indikatorIds = $aspek->indikator()->pluck('id')->toArray();

            // Get avg nilai for this aspek across all validasi
            $avgScore = F02IndikatorValidasi::whereIn('indikator_id', $indikatorIds)
                ->avg('nilai') ?? 0;

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
            'aspeksF02'
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
