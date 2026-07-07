<?php

namespace App\Http\Controllers;

use App\Models\F01Pengisian;
use App\Models\F02Validasi;
use App\Models\F02IndikatorValidasi;
use App\Models\F03Pengisian;
use App\Models\Periode;
use App\Models\Upp;
use App\Models\Aspek;
use App\Models\UserUpp;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display main dashboard with F01/F02/F03 data
     */
    public function index()
    {
        $user = auth()->user();
        $isAdminUPP = !$user->hasGlobalRole([
            'superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin'
        ]);

        $data = [
            'user'         => $user,
            'isAdminUPP'   => $isAdminUPP,
            'pengumuman'   => $this->getPengumumanAktif(),
            'periodeAktif' => $this->getPeriodeAktif(),
        ];

        if ($isAdminUPP) {
            $data += $this->getDataUPP($user);
        } else {
            $data += $this->getDataInternal();
        }

        return view('dashboard.index', $data);
    }

    /**
     * Get active announcement
     */
    private function getPengumumanAktif()
    {
        try {
            return Pengumuman::aktif()->latest()->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get active period
     */
    private function getPeriodeAktif()
    {
        return Periode::where('is_aktif', 1)->first();
    }

    /**
     * Get predicate and presentation color mapping based on index score
     */
    public static function getPredikatData($skor)
    {
        $skor = (float) $skor;
        if ($skor > 4.50) {
            return [
                'label' => 'Pelayanan Prima',
                'color' => '#7F77DD',
                'bg' => '#f5f3ff',
            ];
        } elseif ($skor >= 4.01) {
            return [
                'label' => 'Sangat Baik',
                'color' => '#185FA5',
                'bg' => '#eef2ff',
            ];
        } elseif ($skor >= 3.51) {
            return [
                'label' => 'Baik',
                'color' => '#0F6E56',
                'bg' => '#ecfdf5',
            ];
        } elseif ($skor >= 3.01) {
            return [
                'label' => 'Baik Dengan Catatan',
                'color' => '#0F6E56',
                'bg' => '#ecfdf5',
            ];
        } elseif ($skor >= 2.51) {
            return [
                'label' => 'Cukup',
                'color' => '#BA7517',
                'bg' => '#fff7e8',
            ];
        } elseif ($skor >= 2.01) {
            return [
                'label' => 'Kurang',
                'color' => '#BA7517',
                'bg' => '#fff7e8',
            ];
        } else {
            return [
                'label' => 'Prioritas Pembinaan',
                'color' => '#A32D2D',
                'bg' => '#fef2f2',
            ];
        }
    }

    /**
     * Get data variables for Admin UPP dashboard
     */
    private function getDataUPP($user)
    {
        $userUpp = UserUpp::where('user_id', $user->id)->where('aktif', 1)->first();
        $upp = $userUpp ? $userUpp->upp : null;
        $uppName = $upp ? $upp->nama : 'Belum Terdaftar';
        $uppTerdaftar = $userUpp !== null;

        $periodeAktif = $this->getPeriodeAktif();
        $progressPerAspek = collect();
        $statusPengisian = 'belum_mulai';
        $urlPengisian = '#';
        $hasilPenilaian = null;
        $radarData = [];
        $periodeList = [];
        $periodeSebelumnya = null;
        $deltaNilai = null;

        if ($periodeAktif) {
            $f01 = null;
            if ($upp) {
                $f01 = F01Pengisian::where('periode_id', $periodeAktif->id)
                    ->where('upp_id', $upp->id)
                    ->where('is_latest_version', true)
                    ->first();
            }

            if ($f01) {
                if (in_array($f01->status, ['draft', 'rolled_back'])) {
                    $statusPengisian = 'sedang_mengisi';
                } elseif ($f01->status === 'submitted') {
                    $statusPengisian = 'menunggu_validasi';
                } elseif ($f01->status === 'selesai') {
                    $statusPengisian = 'selesai';
                }
                $urlPengisian = route('f01.aspek-list', $f01->id);
            } else {
                $statusPengisian = 'belum_mulai';
                $urlPengisian = route('f01.index');
            }

            // Calculate progress per aspek for active period
            $aspeks = Aspek::where('periode_id', $periodeAktif->id)
                ->where('aktif', 1)
                ->orderBy('urutan')
                ->with(['indikator' => function($q) {
                    $q->where('aktif', 1)->orderBy('urutan')->with('pertanyaan');
                }])
                ->get();

            $jawaban = $f01 ? $f01->jawaban : collect();

            $progressPerAspek = $aspeks->map(function ($aspek) use ($jawaban) {
                $total = 0;
                $terisi = 0;
                foreach ($aspek->indikator as $ind) {
                    $total++;
                    $questions = $ind->pertanyaan;
                    if ($questions->isEmpty()) {
                        $terisi++;
                        continue;
                    }
                    $answered = 0;
                    foreach ($questions as $q) {
                        $jaw = $jawaban->firstWhere('pertanyaan_id', $q->id);
                        if ($jaw && $jaw->nilai !== null && $jaw->nilai !== '') {
                            $answered++;
                        }
                    }
                    if ($answered > 0) {
                        $terisi++;
                    }
                }
                return (object) [
                    'nama' => $aspek->nama,
                    'terisi' => $terisi,
                    'total' => $total,
                ];
            });

            // Calculate active period assessment result
            if ($upp) {
                $f02 = F02Validasi::where('periode_id', $periodeAktif->id)
                    ->where('status', 'selesai')
                    ->whereHas('f01', function($q) use ($upp) {
                        $q->where('upp_id', $upp->id)
                            ->where('is_latest_version', true);
                    })
                    ->first();
                $f02Value = $f02 ? $f02->total_nilai : null;

                $targetResponden = (int) ($periodeAktif->target_responden_f03 ?? 0);
                $f03Stats = $this->getEffectiveF03StatsForUpp($periodeAktif->id, $upp->id, $targetResponden);
                $f03Value = $f03Stats['response_count'] > 0 ? $f03Stats['effective_average'] : null;

                if ($f02Value !== null || $f03Value !== null) {
                    $f02Val = $f02Value ?? 0.0;
                    $f03Val = $f03Value ?? 0.0;
                    $ippScore = ($f02Val * 0.75) + ($f03Val * 0.25);
                    $predData = self::getPredikatData($ippScore);

                    $hasilPenilaian = (object) [
                        'nilai_f02' => $f02Value,
                        'nilai_f03' => $f03Value,
                        'nilai_ipp' => $ippScore,
                        'predikat' => $predData['label'],
                        'predikat_color' => $predData['color'],
                        'predikat_bg' => $predData['bg'],
                    ];
                }
            }
        }

        // Radar chart and History logic across all periods for this UPP
        if ($upp) {
            $completedValidations = F02Validasi::where('f02_validasi.status', 'selesai')
                ->whereHas('f01', function($q) use ($upp) {
                    $q->where('upp_id', $upp->id)
                        ->where('is_latest_version', true);
                })
                ->join('periode', 'f02_validasi.periode_id', '=', 'periode.id')
                ->select('f02_validasi.*', 'periode.nama as periode_nama', 'periode.tahun as periode_tahun')
                ->orderBy('periode.tahun', 'desc')
                ->with(['periode', 'f01.jawaban'])
                ->get();

            $historyList = [];
            foreach ($completedValidations as $val) {
                $pId = $val->periode_id;
                $targetRes = $val->periode ? (int)$val->periode->target_responden_f03 : 0;
                
                $f02Score = $val->total_nilai ?? 0.0;
                $f03Stats = $this->getEffectiveF03StatsForUpp($pId, $upp->id, $targetRes);
                $f03Score = $f03Stats['effective_average'] ?? 0.0;
                
                $ipp = round(($f02Score * 0.75) + ($f03Score * 0.25), 2);
                $predData = self::getPredikatData($ipp);

                // Fetch aspects details for modal
                $aspeksDetails = [];
                $aspeks = Aspek::where('periode_id', $pId)
                    ->where('aktif', 1)
                    ->orderBy('urutan')
                    ->with(['indikator' => function($q) {
                        $q->where('aktif', 1)->orderBy('urutan');
                    }])
                    ->get();
                    
                $f02Indikators = F02IndikatorValidasi::where('f02_validasi_id', $val->id)
                    ->get()
                    ->keyBy('indikator_id');
                    
                foreach ($aspeks as $aspek) {
                    $indList = [];
                    $aspekSum = 0;
                    $aspekCount = 0;
                    foreach ($aspek->indikator as $ind) {
                        $indScore = isset($f02Indikators[$ind->id]) ? (float)$f02Indikators[$ind->id]->nilai : 0.0;
                        $indPred = self::getPredikatData($indScore)['label'];
                        $indList[] = [
                            'nama' => $ind->nama,
                            'skor' => $indScore,
                            'predikat' => $indPred,
                        ];
                        $aspekSum += $indScore;
                        $aspekCount++;
                    }
                    $aspekAvg = $aspekCount > 0 ? round($aspekSum / $aspekCount, 2) : 0.0;
                    $aspeksDetails[] = [
                        'nama' => $aspek->nama,
                        'skor' => $aspekAvg,
                        'predikat' => self::getPredikatData($aspekAvg)['label'],
                        'indikators' => $indList,
                    ];
                }
                
                $historyList[] = [
                    'periode_id' => $pId,
                    'nama' => $val->periode_nama,
                    'nilai_ipp' => $ipp,
                    'predikat' => $predData['label'],
                    'predikat_color' => $predData['color'],
                    'predikat_bg' => $predData['bg'],
                    'aspeks' => $aspeksDetails,
                ];

                // Build radar data aspects
                $aspekScores = [];
                foreach ($aspeks as $aspek) {
                    $indikatorIds = $aspek->indikator()->where('aktif', 1)->pluck('id')->toArray();
                    $avgScore = F02IndikatorValidasi::where('f02_validasi_id', $val->id)
                        ->whereIn('indikator_id', $indikatorIds)
                        ->whereNotNull('nilai')
                        ->avg('nilai') ?? 0;
                        
                    $aspekScores[$aspek->nama] = round($avgScore, 2);
                }
                
                $radarData[] = [
                    'periode_id' => $pId,
                    'label' => $val->periode_nama,
                    'nilai_per_aspek' => $aspekScores,
                ];
                
                $periodeList[] = [
                    'id' => $pId,
                    'nama' => $val->periode_nama,
                ];
            }

            // Exclude current active period from history card
            $historyListPast = collect($historyList)->filter(function($h) use ($periodeAktif) {
                return !$periodeAktif || (int)$h['periode_id'] !== (int)$periodeAktif->id;
            })->values()->all();

            $periodeSebelumnya = $historyListPast[0] ?? null;
            if (count($historyListPast) >= 2) {
                $deltaNilai = (float) $historyListPast[0]['nilai_ipp'] - (float) $historyListPast[1]['nilai_ipp'];
            }
        }

        return [
            'uppName' => $uppName,
            'uppTerdaftar' => $uppTerdaftar,
            'progressPerAspek' => $progressPerAspek,
            'statusPengisian' => $statusPengisian,
            'urlPengisian' => $urlPengisian,
            'hasilPenilaian' => $hasilPenilaian,
            'radarData' => $radarData,
            'periodeList' => $periodeList,
            'periodeSebelumnya' => $periodeSebelumnya,
            'deltaNilai' => $deltaNilai,
        ];
    }

    /**
     * Get data variables for Admin Internal dashboard
     */
    private function getDataInternal()
    {
        $periodeAktif = $this->getPeriodeAktif();
        $thresholdHari = 7;

        // Source of truth: UPPs registered in user_upp (aktif, peran admin_upp)
        // Each UPP only counted once even if multiple assignments exist
        $registeredUppIds = UserUpp::where('aktif', 1)
            ->where('peran', UserUpp::PERAN_ADMIN_UPP)
            ->distinct()
            ->pluck('upp_id');

        $totalCount = $registeredUppIds->count();

        // Build list of UPPs with their assigned user name for display
        // One record per UPP (first active admin_upp assignment)
        $uppList = UserUpp::where('aktif', 1)
            ->where('peran', UserUpp::PERAN_ADMIN_UPP)
            ->whereIn('upp_id', $registeredUppIds)
            ->with(['upp', 'user'])
            ->get()
            ->unique('upp_id')   // deduplicate
            ->values();

        // Collections per category
        $belumMulai        = collect();
        $sedangMengisi     = collect();
        $menungguValidasi  = collect();
        $selesai           = collect();
        $progressPerUPP    = collect();
        $uppDeadlineAlert  = collect();

        if ($periodeAktif) {
            $periodeId = $periodeAktif->id;

            // Load all F01 (latest version) for this period, keyed by upp_id
            $f01Map = F01Pengisian::where('periode_id', $periodeId)
                ->where('is_latest_version', true)
                ->get()
                ->keyBy('upp_id');

            // Load all completed F02 validasi, keyed by f01_pengisian_id
            $selesaiUppIds = F02Validasi::where('f02_validasi.status', 'selesai')
                ->whereHas('f01', function ($q) use ($periodeId, $registeredUppIds) {
                    $q->where('periode_id', $periodeId)
                      ->where('is_latest_version', true)
                      ->whereIn('upp_id', $registeredUppIds);
                })
                ->with(['f01'])
                ->get()
                ->pluck('f01.upp_id')
                ->filter()
                ->unique()
                ->values();

            // --- Get total indicators count ---
            $aspeks = Aspek::where('periode_id', $periodeId)
                ->where('aktif', 1)
                ->with(['indikator' => function ($q) {
                    $q->where('aktif', 1)->with('pertanyaan');
                }])
                ->get();

            $totalIndicatorsCount = $aspeks->sum(fn($a) => $a->indikator->count());

            foreach ($uppList as $userUppRecord) {
                $upp      = $userUppRecord->upp;
                $userName = $userUppRecord->user?->nama ?? '—';

                if (!$upp) continue;

                $f01   = $f01Map->get($upp->id);

                // --- Calculate Progress First ---
                $progressPercent = 0;
                if ($f01) {
                    if (!$f01->relationLoaded('jawaban')) {
                        $f01->load('jawaban');
                    }
                    $completed = 0;
                    foreach ($aspeks as $aspek) {
                        foreach ($aspek->indikator as $ind) {
                            $questions = $ind->pertanyaan;
                            if ($questions->isEmpty()) {
                                $completed++;
                                continue;
                            }
                            $answered = $questions->filter(function ($q) use ($f01) {
                                $jaw = $f01->jawaban->firstWhere('pertanyaan_id', $q->id);
                                return $jaw && $jaw->nilai !== null && $jaw->nilai !== '';
                            })->count();
                            if ($answered > 0) $completed++;
                        }
                    }
                    $progressPercent = $totalIndicatorsCount > 0
                        ? round(($completed / $totalIndicatorsCount) * 100)
                        : 0;
                }

                $entry = (object) [
                    'upp_id'    => $upp->id,
                    'nama_upp'  => $upp->nama,
                    'user_nama' => $userName,
                    'f01'       => $f01,
                    'f01_status'=> $f01?->status ?? null,
                ];

                $statusLabel = 'belum_mulai';

                // --- Classify ---
                if ($selesaiUppIds->contains($upp->id)) {
                    // F02 sudah selesai divalidasi
                    $selesai->push($entry);
                    $statusLabel = 'selesai';
                } elseif (!$f01) {
                    // Belum ada aktivitas F01 sama sekali
                    $belumMulai->push($entry);
                    $statusLabel = 'belum_mulai';
                } elseif ($f01->status === 'submitted') {
                    // F01 sudah dikirim, menunggu validasi
                    $menungguValidasi->push($entry);
                    $statusLabel = 'menunggu_validasi';
                } else {
                    // F01 ada tapi draft/rolled_back — sedang mengisi
                    if ($progressPercent == 0) {
                        $belumMulai->push($entry);
                        $statusLabel = 'belum_mulai';
                    } else {
                        $sedangMengisi->push($entry);
                        $statusLabel = 'sedang_mengisi';
                    }
                }

                // --- Progress Chart ---
                $progressPerUPP->push((object) [
                    'nama_upp'       => $upp->nama,
                    'status'         => $statusLabel,
                    'persen_progress'=> $progressPercent,
                ]);
            }

            // --- Deadline Alert ---
            $today    = \Carbon\Carbon::today();
            $endDate  = \Carbon\Carbon::parse($periodeAktif->tanggal_selesai);
            $sisaHari = (int) $today->diffInDays($endDate, false);

            if ($sisaHari <= $thresholdHari) {
                foreach ([$belumMulai, $sedangMengisi, $menungguValidasi] as $group) {
                    foreach ($group as $entry) {
                        $uppDeadlineAlert->push((object) [
                            'nama_upp'  => $entry->nama_upp,
                            'status'    => $entry->f01_status ?? 'belum_mulai',
                            'sisa_hari' => $sisaHari,
                        ]);
                    }
                }
                $uppDeadlineAlert = $uppDeadlineAlert->sortBy('sisa_hari')->values();
            }
        }

        $summaryCards = [
            'total'            => ['count' => $totalCount,                  'list' => collect()],
            'belum_mulai'      => ['count' => $belumMulai->count(),         'list' => $belumMulai],
            'sedang_mengisi'   => ['count' => $sedangMengisi->count(),      'list' => $sedangMengisi],
            'menunggu_validasi'=> ['count' => $menungguValidasi->count(),   'list' => $menungguValidasi],
            'selesai'          => ['count' => $selesai->count(),            'list' => $selesai],
        ];

        return [
            'summaryCards'    => $summaryCards,
            'progressPerUPP'  => $progressPerUPP,
            'uppDeadlineAlert'=> $uppDeadlineAlert,
            'thresholdHari'   => $thresholdHari,
            'uppTerdaftar'    => true,
        ];
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
        $aspeks = Aspek::where('periode_id', $periodeId)->get();
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
        $aspeks = \App\Models\F03Aspek::where('periode_id', $periodeId)->get();
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
