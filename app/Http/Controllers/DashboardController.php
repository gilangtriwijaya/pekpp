<?php

namespace App\Http\Controllers;

use App\Models\F01Pengisian;
use App\Models\F02Validasi;
use App\Models\F02IndikatorValidasi;
use App\Models\F03Pengisian;
use App\Models\Periode;
use App\Models\Upp;
use App\Models\Aspek;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display main dashboard with F01/F02/F03 data
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isGlobalUser = $user->hasGlobalRole(['superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin']);

        // Get active periode
        $periode = Periode::where('is_aktif', 1)->first();
        if (!$periode) {
            return view('dashboard.index')->with('error', 'Tidak ada periode aktif');
        }

        // Get available UPPs for filtering
        $availableUpps = $this->getAvailableUppsForUser($user);

        // Get available UPPs with IPP scores (sorted for filter modal)
        $availableUppsWithScores = $this->getAvailableUppsWithScores($periode->id, $availableUpps);

        // Get selected UPPs from request
        // If not provided, try to load from user's saved preference
        // Otherwise default to first UPP
        $selectedUppIds = $request->get('upp_ids', []);
        if (empty($selectedUppIds)) {
            // Try to load saved preference
            if ($user->preferred_upp_ids && is_array($user->preferred_upp_ids) && count($user->preferred_upp_ids) > 0) {
                $selectedUppIds = $user->preferred_upp_ids;

                // Filter out UPPs user no longer has access to
                if (!$isGlobalUser) {
                    $userUppIds = $user->getUserUpps()->pluck('upp_id')->toArray();
                    $selectedUppIds = array_intersect($selectedUppIds, $userUppIds);
                }

                // If after filtering we'd have no UPPs, default to first available
                if (empty($selectedUppIds)) {
                    $selectedUppIds = [$availableUpps->first()?->id];
                }
            } else {
                // No saved preference, default to first available UPP
                $selectedUppIds = [$availableUpps->first()?->id];
            }
        }

        // Get dashboard data
        $dashboardData = $this->getDashboardData($periode->id, $selectedUppIds, $isGlobalUser, $user);

        // Calculate F02 and F03 aspek scores for charts
        $f02AspekData = $this->calculateF02AspekScores($periode->id, $selectedUppIds);
        $f03AspekData = $this->calculateF03AspekScores($periode->id, $selectedUppIds);

        return view('dashboard.index', [
            'user' => $user,
            'periode' => $periode,
            'isGlobalUser' => $isGlobalUser,
            'availableUpps' => $availableUpps,
            'availableUppsWithScores' => $availableUppsWithScores,
            'selectedUppIds' => $selectedUppIds,
            'dashboardData' => $dashboardData,
            'f02AspekLabels' => array_keys($f02AspekData['aspek_scores'] ?? []),
            'f02AspekValues' => array_values($f02AspekData['aspek_scores'] ?? []),
            'f02TotalValidasi' => $f02AspekData['total_validasi'] ?? 0,
            'f02AverageScore' => $f02AspekData['average_score'] ?? 0,
            'f03AspekLabels' => array_keys($f03AspekData['aspek_scores'] ?? []),
            'f03AspekValues' => array_values($f03AspekData['aspek_scores'] ?? []),
            'f03TotalResponses' => $f03AspekData['total_responses'] ?? 0,
            'f03AverageScore' => $f03AspekData['average_score'] ?? 0,
        ]);
    }

    /**
     * Get UPPs available to the user
     */
    private function getAvailableUppsForUser($user)
    {
        if ($user->hasGlobalRole(['superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin'])) {
            // Global users see all UPPs
            return Upp::where('aktif', 1)->orderBy('nama', 'asc')->get();
        }

        // UPP users see only their assigned UPPs
        $uppIds = $user->getUserUpps()
            ->where('aktif', 1)
            ->pluck('upp_id')
            ->toArray();

        return Upp::whereIn('id', $uppIds)->orderBy('nama', 'asc')->get();
    }

    /**
     * Get available UPPs with IPP scores sorted highest to lowest
     */
    private function getAvailableUppsWithScores($periodeId, $availableUpps)
    {
        $periode = Periode::find($periodeId);
        $targetResponden = (int) ($periode?->target_responden_f03 ?? 0);

        $uppsWithScores = $availableUpps->map(function($upp) use ($periodeId, $targetResponden) {
            // Get F02 score (latest only)
            $f02 = F02Validasi::where('periode_id', $periodeId)
                ->where('status', 'selesai')
                ->whereHas('f01', function($q) use ($upp) {
                    $q->where('upp_id', $upp->id)
                        ->where('is_latest_version', true);
                })
                ->first();

            $f02Value = $f02 ? ($f02->total_nilai ?? 0) : 0;

            $f03Stats = $this->getEffectiveF03StatsForUpp($periodeId, $upp->id, $targetResponden);

            // Calculate IPP
            $ippScore = ($f02Value * 0.75) + ($f03Stats['effective_average'] * 0.25);

            return [
                'id' => $upp->id,
                'nama' => $upp->nama,
                'ipp_score' => round($ippScore, 2),
            ];
        })
        ->sortByDesc('ipp_score')
        ->values();

        return $uppsWithScores;
    }

    /**
     * Get F03 response count and effective average for a UPP.
     * If responses are below the minimum target, the effective average is forced to 0.
     */
    private function getEffectiveF03StatsForUpp(int $periodeId, int $uppId, int $targetResponden = 0): array
    {
        $responseCount = F03Pengisian::where('periode_id', $periodeId)
            ->where('upp_id', $uppId)
            ->count();

        $rawAverage = \App\Models\F03Jawaban::whereHas('pengisian', function($q) use ($periodeId, $uppId) {
            $q->where('periode_id', $periodeId)
                ->where('upp_id', $uppId);
        })->avg('score') ?? 0;

        $targetMet = $targetResponden <= 0 || $responseCount >= $targetResponden;

        return [
            'response_count' => $responseCount,
            'raw_average' => (float) $rawAverage,
            'effective_average' => $targetMet ? (float) $rawAverage : 0.0,
            'target_responden' => $targetResponden,
            'target_met' => $targetMet,
        ];
    }

    /**
     * Get dashboard data for selected UPPs
     */
    private function getDashboardData($periodeId, $selectedUppIds, $isGlobalUser, $user)
    {
        // Both global and UPP users see per-UPP breakdown
        // Filter only available UPPs for non-global users
        $userUpps = $user->getUserUpps()->pluck('upp_id')->toArray();
        if (!$isGlobalUser) {
            $selectedUppIds = array_intersect($selectedUppIds, $userUpps);
        }

        if (empty($selectedUppIds)) {
            return [];
        }

        $periode = Periode::find($periodeId);
        $targetResponden = (int) ($periode?->target_responden_f03 ?? 0);

        // F02 Data (Validated scores)
        $f02Data = F02Validasi::with(['f01', 'f01.upp'])
            ->where('periode_id', $periodeId)
            ->whereHas('f01', function($q) use ($selectedUppIds) {
                $q->whereIn('upp_id', $selectedUppIds)
                    ->where('is_latest_version', true);
            })
            ->get()
            ->map(function($validasi) {
                return [
                    'upp_id' => $validasi->f01->upp_id,
                    'upp_nama' => $validasi->f01->upp->nama,
                    'status' => $validasi->status,
                    'total_nilai' => $validasi->total_nilai ?? 0,
                ];
            });

        // Aggregate data by UPP
        $aggregatedData = [];
        foreach ($selectedUppIds as $uppId) {
            $upp = Upp::find($uppId);
            if (!$upp) continue;

            $f02 = $f02Data->where('upp_id', $uppId)->first();

            $f03Stats = $this->getEffectiveF03StatsForUpp($periodeId, $uppId, $targetResponden);
            $f03Average = $f03Stats['effective_average'];
            $f03ResponseCount = $f03Stats['response_count'];

            // Calculate final Index (Indeks Pelayanan Publik)
            // F02: 1-5 scale (hasil dari weighted aspek calculation)
            // F03: 1-5 scale (average of survey responses)
            // Formula: (F02 * 75%) + (F03 * 25%)
            $f02Value = $f02 ? ($f02['total_nilai'] ?? 0) : 0;
            $finalIndex = ($f02Value * 0.75) + ($f03Average * 0.25);

            // Get first active user's email for this UPP
            $userEmail = '';
            $userUpp = \App\Models\UserUpp::where('upp_id', $uppId)
                ->where('aktif', 1)
                ->with('user')
                ->first();
            if ($userUpp && $userUpp->user) {
                $userEmail = $userUpp->user->email;
            }

            $aggregatedData[] = [
                'upp_id' => $uppId,
                'upp_nama' => $upp->nama,
                'user_email' => $userEmail,
                'f02_nilai' => round($f02Value, 2),
                'f03_rata_rata' => round($f03Average, 2),
                'f03_jumlah_responden' => $f03ResponseCount,
                'indeks_nilai' => round($finalIndex, 2),
            ];
        }

        // Count F01 submission and F02 validation status (latest version only)
        $totalSubmitted = F01Pengisian::where('periode_id', $periodeId)
            ->whereIn('upp_id', $selectedUppIds)
            ->where('status', '!=', 'draft')
            ->where('is_latest_version', true)
            ->count();

        $totalValidated = F02Validasi::where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->whereHas('f01', function($q) use ($selectedUppIds) {
                $q->whereIn('upp_id', $selectedUppIds)
                    ->where('is_latest_version', true);
            })
            ->count();

        $totalPendingValidation = $totalSubmitted - $totalValidated;

        return [
            'upps' => $aggregatedData,
            'summary' => $this->calculateSummary($aggregatedData, $totalSubmitted, $totalPendingValidation, $totalValidated),
        ];
    }

    /**
     * Get aggregated dashboard data for global users (superadmin, admin_organisasi)
     */
    private function getGlobalDashboardData($periodeId)
    {
        $allUpps = Upp::where('aktif', 1)->get();
        $selectedUppIds = $allUpps->pluck('id')->values()->all();
        $dashboardData = $this->getDashboardData($periodeId, $selectedUppIds, true, request()->user());

        $totalSubmitted = F01Pengisian::where('periode_id', $periodeId)
            ->where('status', '!=', 'draft')
            ->where('is_latest_version', true)
            ->count();

        $totalValidated = F02Validasi::where('periode_id', $periodeId)
            ->where('status', 'selesai')
            ->whereHas('f01', function($q) {
                $q->where('is_latest_version', true);
            })
            ->count();

        $totalPendingValidation = $totalSubmitted - $totalValidated;
        $totalF03Responses = F03Pengisian::where('periode_id', $periodeId)->count();
        $uppCount = $allUpps->count();

        $avgF02 = round(collect($dashboardData['upps'] ?? [])->avg('f02_nilai') ?? 0, 2);
        $avgF03 = round(collect($dashboardData['upps'] ?? [])->avg('f03_rata_rata') ?? 0, 2);
        $globalIndex = round(collect($dashboardData['upps'] ?? [])->avg('indeks_nilai') ?? 0, 2);

        return [
            'upps' => [],  // Empty array - no per-UPP breakdown for global users
            'is_global' => true,
            'summary' => [
                'total_upp' => $uppCount,
                'total_submitted' => $totalSubmitted,
                'total_validated' => $totalValidated,
                'total_pending_validation' => $totalPendingValidation,
                'total_f03_responses' => $totalF03Responses,
                'avg_f02' => round($avgF02, 2),
                'avg_f03' => round($avgF03, 2),
                'avg_indeks' => round($globalIndex, 2),
                'f02_ipp_contribution' => round($avgF02 * 0.75, 2),
                'f03_ipp_contribution' => round($avgF03 * 0.25, 2),
            ],
        ];
    }

    /**
     * Calculate summary statistics for per-UPP dashboard
     */
    private function calculateSummary($data, $totalSubmitted = 0, $totalPendingValidation = 0, $totalValidated = 0)
    {
        if (empty($data)) {
            return [
                'total_upp' => 0,
                'avg_f02' => 0,
                'avg_f03' => 0,
                'avg_indeks' => 0,
                'f02_ipp_contribution' => 0,
                'f03_ipp_contribution' => 0,
                'upp_baik_count' => 0,
                'upp_pembinaan_count' => 0,
                'total_submitted' => $totalSubmitted,
                'total_pending_validation' => $totalPendingValidation,
                'total_validated' => $totalValidated,
            ];
        }

        // Count UPPs with indeks >= 3.01 (minimal Baik/B-) and < 3.01 (perlu pembinaan)
        $collection = collect($data);
        $avg_f02 = round($collection->avg('f02_nilai'), 2);
        $avg_f03 = round($collection->avg('f03_rata_rata'), 2);

        $upp_baik_count = $collection->filter(function($item) {
            return $item['indeks_nilai'] >= 3.01;
        })->count();
        $upp_pembinaan_count = $collection->filter(function($item) {
            return $item['indeks_nilai'] < 3.01;
        })->count();

        return [
            'total_upp' => count($data),
            'avg_f02' => $avg_f02,
            'avg_f03' => $avg_f03,
            'avg_indeks' => round($collection->avg('indeks_nilai'), 2),
            'f02_ipp_contribution' => round($avg_f02 * 0.75, 2),
            'f03_ipp_contribution' => round($avg_f03 * 0.25, 2),
            'upp_baik_count' => $upp_baik_count,
            'upp_pembinaan_count' => $upp_pembinaan_count,
            'total_submitted' => $totalSubmitted,
            'total_pending_validation' => $totalPendingValidation,
            'total_validated' => $totalValidated,
        ];
    }

    /**
     * Get chart data as JSON (for AJAX)
     */
    public function getChartData(Request $request)
    {
        $user = $request->user();
        $isGlobalUser = $user->hasGlobalRole(['superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin']);

        $periode = Periode::where('is_aktif', 1)->first();
        if (!$periode) {
            return response()->json(['error' => 'No active periode'], 404);
        }

        $selectedUppIds = $request->get('upp_ids', []);
        if (empty($selectedUppIds)) {
            $availableUpps = $this->getAvailableUppsForUser($user);
            $selectedUppIds = [$availableUpps->first()?->id];
        }

        $data = $this->getDashboardData($periode->id, $selectedUppIds, $isGlobalUser, $user);

        if (empty($data['upps'])) {
            return response()->json(['error' => 'No data available'], 404);
        }

        return response()->json([
            'labels' => collect($data['upps'])->pluck('upp_nama')->toArray(),
            'f02' => collect($data['upps'])->pluck('f02_nilai')->toArray(),
            'f03' => collect($data['upps'])->pluck('f03_rata_rata')->toArray(),
            'indeks' => collect($data['upps'])->pluck('indeks_nilai')->toArray(),
        ]);
    }

    /**
     * Get fresh dashboard data for chart filtering (AJAX endpoint for chart filters)
     * This returns complete dashboard data including aspek scores for real-time updates
     */
    public function getFilteredDashboardData(Request $request)
    {
        $user = $request->user();
        $isGlobalUser = $user->hasGlobalRole(['superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin']);

        $periode = Periode::where('is_aktif', 1)->first();
        if (!$periode) {
            return response()->json(['error' => 'No active periode'], 404);
        }

        $selectedUppIds = $request->get('upp_ids', []);
        if (empty($selectedUppIds)) {
            return response()->json(['error' => 'No UPP selected'], 400);
        }

        // Validate UPP access
        if (!$isGlobalUser) {
            $userUppIds = $user->getUserUpps()->pluck('upp_id')->toArray();
            $selectedUppIds = array_intersect($selectedUppIds, $userUppIds);
        }

        if (empty($selectedUppIds)) {
            return response()->json(['error' => 'Invalid UPP selection'], 403);
        }

        // Get fresh dashboard data
        $dashboardData = $this->getDashboardData($periode->id, $selectedUppIds, $isGlobalUser, $user);

        // Get fresh F02 aspek data
        $f02AspekData = $this->calculateF02AspekScores($periode->id, $selectedUppIds);

        // Get fresh F03 aspek data
        $f03AspekData = $this->calculateF03AspekScores($periode->id, $selectedUppIds);

        if (empty($dashboardData['upps'])) {
            return response()->json(['error' => 'No data available for selected UPPs'], 404);
        }

        return response()->json([
            'success' => true,
            'upps' => $dashboardData['upps'],
            'summary' => $dashboardData['summary'],
            'f02AspekLabels' => array_keys($f02AspekData['aspek_scores'] ?? []),
            'f02AspekValues' => array_values($f02AspekData['aspek_scores'] ?? []),
            'f02TotalValidasi' => $f02AspekData['total_validasi'] ?? 0,
            'f02AverageScore' => $f02AspekData['average_score'] ?? 0,
            'f03AspekLabels' => array_keys($f03AspekData['aspek_scores'] ?? []),
            'f03AspekValues' => array_values($f03AspekData['aspek_scores'] ?? []),
            'f03TotalResponses' => $f03AspekData['total_responses'] ?? 0,
            'f03AverageScore' => $f03AspekData['average_score'] ?? 0,
        ]);
    }

    /**
     * Save user's preferred UPP selection
     */
    public function savePreferredUpps(Request $request)
    {
        $user = $request->user();
        $isGlobalUser = $user->hasGlobalRole(['superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin']);

        // Get the UPP IDs to save
        $uppIds = $request->get('upp_ids', []);

        if (empty($uppIds)) {
            return response()->json(['error' => 'No UPP selected'], 400);
        }

        // Validate UPP IDs - ensure user has access to them
        if (!$isGlobalUser) {
            $userUppIds = $user->getUserUpps()->pluck('upp_id')->toArray();
            $uppIds = array_intersect($uppIds, $userUppIds);
        }

        if (empty($uppIds)) {
            return response()->json(['error' => 'Invalid UPP selection'], 403);
        }

        // Save preference
        $user->update(['preferred_upp_ids' => array_values($uppIds)]);

        return response()->json([
            'success' => true,
            'message' => 'Preferensi UPP berhasil disimpan',
            'preferred_upp_ids' => $user->preferred_upp_ids
        ]);
    }

    /**
     * Calculate F02 aspek scores for the selected UPPs (or all if empty = aggregated mode)
     */
    private function calculateF02AspekScores($periodeId, $selectedUppIds)
    {
        $aspeks = Aspek::all();
        $f02AspekScores = [];

        // If selectedUppIds is empty, calculate for ALL UPPs (aggregated mode)
        $isAggregated = empty($selectedUppIds);

        // Calculate average score for each aspek
        foreach ($aspeks as $aspek) {
            $indikatorIds = $aspek->indikator()->pluck('id')->toArray();

            // Get average nilai for this aspek across the selected UPPs (or all if aggregated)
            $query = F02IndikatorValidasi::whereIn('indikator_id', $indikatorIds)
                ->whereHas('validasi', function($q) use ($periodeId, $selectedUppIds, $isAggregated) {
                    $q->where('status', 'selesai')
                        ->where('periode_id', $periodeId);

                    // Only filter by UPP if not in aggregated mode
                    if (!$isAggregated) {
                        $q->whereHas('f01Pengisian', function($subq) use ($selectedUppIds) {
                            $subq->whereIn('upp_id', $selectedUppIds)
                                ->where('is_latest_version', true);
                        });
                    }
                });

            $avgScore = $query->avg('nilai') ?? 0;
            $f02AspekScores[$aspek->nama] = round($avgScore, 2);
        }

        // Calculate total validasi and average score
        $totalValidasiQuery = F02Validasi::where('status', 'selesai')
            ->where('periode_id', $periodeId)
            ->whereHas('f01', function($q) {
                $q->where('is_latest_version', true);
            });

        if (!$isAggregated) {
            $totalValidasiQuery->whereHas('f01Pengisian', function($q) use ($selectedUppIds) {
                $q->whereIn('upp_id', $selectedUppIds);
            });
        }

        $totalValidasi = $totalValidasiQuery->count();

        $averageScoreQuery = F02Validasi::where('status', 'selesai')
            ->where('periode_id', $periodeId)
            ->whereHas('f01', function($q) {
                $q->where('is_latest_version', true);
            });

        if (!$isAggregated) {
            $averageScoreQuery->whereHas('f01Pengisian', function($q) use ($selectedUppIds) {
                $q->whereIn('upp_id', $selectedUppIds);
            });
        }

        $averageScore = $averageScoreQuery->avg('total_nilai') ?? 0;

        return [
            'aspek_scores' => $f02AspekScores,
            'total_validasi' => $totalValidasi,
            'average_score' => round($averageScore, 2),
        ];
    }

    /**
     * Calculate F03 aspek scores for the selected UPPs (or all if empty = aggregated mode)
     */
    private function calculateF03AspekScores($periodeId, $selectedUppIds)
    {
        $aspeks = \App\Models\F03Aspek::all();
        $f03AspekScores = [];

        // If selectedUppIds is empty, calculate for ALL UPPs (aggregated mode)
        $isAggregated = empty($selectedUppIds);

        // Calculate average score for each aspek from F03
        foreach ($aspeks as $aspek) {
            $indikatorIds = $aspek->indikator()->pluck('id')->toArray();

            // Get average score for this aspek across the selected UPPs (or all if aggregated)
            $query = \App\Models\F03Jawaban::whereIn('f03_indikator_id', $indikatorIds)
                ->whereHas('pengisian', function($q) use ($periodeId, $selectedUppIds, $isAggregated) {
                    $q->where('periode_id', $periodeId);

                    // Only filter by UPP if not in aggregated mode
                    if (!$isAggregated) {
                        $q->whereIn('upp_id', $selectedUppIds);
                    }
                });

            $avgScore = $query->avg('score') ?? 0;
            $f03AspekScores[$aspek->nama] = round($avgScore, 2);
        }

        // Calculate total responses and average score
        $totalResponsesQuery = \App\Models\F03Pengisian::where('periode_id', $periodeId);
        if (!$isAggregated) {
            $totalResponsesQuery->whereIn('upp_id', $selectedUppIds);
        }
        $totalResponses = $totalResponsesQuery->count();

        // Get F03 jawaban average for overall score
        $averageScoreQuery = \App\Models\F03Jawaban::whereHas('pengisian', function($q) use ($periodeId, $selectedUppIds, $isAggregated) {
            $q->where('periode_id', $periodeId);

            // Only filter by UPP if not in aggregated mode
            if (!$isAggregated) {
                $q->whereIn('upp_id', $selectedUppIds);
            }
        });

        $averageScore = $averageScoreQuery->avg('score') ?? 0;

        return [
            'aspek_scores' => $f03AspekScores,
            'total_responses' => $totalResponses,
            'average_score' => round($averageScore, 2),
        ];
    }
}
