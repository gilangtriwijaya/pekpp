<?php

namespace App\Livewire\Analytics;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use ZipArchive;

class Panel extends Component
{
    use WithPagination;

    // Filters
    public $periode_id = null;

    // Selected UPP IDs. Supports single or multiple selections.
    public $upp_id = [];

    // Chart Data - F02
    public $f02_labels = [];
    public $f02_data = [];

    // Chart Data - F03
    public $f03_labels = [];
    public $f03_data = [];

    // Chart Data - F03 Indikator (Rata-rata skor per indikator)
    public $f03_indikator_ids = [];
    public $f03_indikator_labels = [];
    public $f03_indikator_values = [];

    // Backward-compatible aliases used by the existing render pipeline.
    public $f03_aspek_ids = [];
    public $f03_aspek_labels = [];
    public $f03_aspek_values = [];

    // Chart Data - IPP
    public $ipp_labels = [];
    public $ipp_data = [];

    // Chart Data - Aspek (Agregasi Total F02 per Aspek)
    public $aspek_ids = [];
    public $aspek_labels = [];
    public $aspek_values = [];
    public $aspek_tabs = [];
    public $aspek_indikator_scores = [];

    // Chart Data - Aspek Detail (deprecated, keeping for reference)
    public $aspek_chart_data = [];
    public $selected_aspek_id = null;

    // Filter Options
    public $periode_options = [];
    public $upp_options = [];
    public $summary_cards = [];
    public $summary_card_details = [];

    // Indicator Detail Modal
    public $indicator_detail = null;
    public $indicator_detail_visible = false;
    public $selected_score_for_upp = null;

    // Aspek Detail Modal
    public $aspek_detail = null;
    public $aspek_detail_visible = false;

    public function mount()
    {
        if (empty($this->periode_id)) {
            $activePeriode = DB::table('periode')->whereNull('deleted_at')->where('is_aktif', 1)->first();
            if ($activePeriode) {
                $this->periode_id = $activePeriode->id;
            }
        }
        
        $this->upp_id = $this->normalizeUppIds($this->upp_id);
        $this->loadFilterOptions();
        $this->loadAllChartData();
    }

    // Reactive update when upp_id changes (from query parameter or user action)
    public function updatedUppId()
    {
        $this->upp_id = $this->normalizeUppIds($this->upp_id);
        $this->closeAspekDetail();
        $this->loadAllChartData();
        $this->dispatchChartDataUpdated();
    }

    // Reactive update when periode_id changes
    public function updatedPeriodeId()
    {
        $this->closeAspekDetail();
        $this->loadFilterOptions();
        $this->loadAllChartData();
        $this->dispatchChartDataUpdated();
    }

    public function loadFilterOptions()
    {
        try {
            // Load periode options
            $this->periode_options = DB::table('periode')
                ->select('id', 'tahun', 'nama')
                ->whereNull('deleted_at')
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'label' => $p->tahun . ' - ' . $p->nama])
                ->toArray();

            // Base UPP list with display labels taken from user_upp
            $uppBase = DB::table('user_upp as uu')
                ->join('upps as u', 'uu.upp_id', '=', 'u.id')
                ->join('users as usr', 'uu.user_id', '=', 'usr.id')
                ->select(
                    'u.id',
                    'u.kode',
                    'u.nama',
                    'usr.email'
                )
                ->where('u.aktif', 1)
                ->where('uu.aktif', 1) // Only active user assignments
                ->get()
                ->unique('id') // Ensure unique UPP if there are multiple users
                ->map(function($u) {
                    $label = $u->nama;
                    if (!empty($u->email)) {
                        $emailParts = explode('@', $u->email);
                        $label = $emailParts[0]; // Take username part before @
                    }

                    return [
                        'id' => (int) $u->id,
                        'label' => strtoupper($label),
                    ];
                })
                ->values();

            // F02 averages per UPP (latest version, non-draft)
            $f02Rows = DB::table('f02_validasi as fv')
                ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
                ->select('fp.upp_id', DB::raw('AVG(COALESCE(fv.total_nilai, 0)) as f02_avg'))
                ->where('fp.is_latest_version', 1)
                ->where('fv.status', '!=', 'draft')
                ->when(!empty($this->periode_id), function ($query) {
                    $query->where('fv.periode_id', $this->periode_id);
                })
                ->groupBy('fp.upp_id')
                ->get()
                ->keyBy('upp_id');

            // F03 averages per UPP
            $f03Rows = DB::table('f03_jawaban as fj')
                ->join('f03_pengisian as fp', 'fj.f03_pengisian_id', '=', 'fp.id')
                ->select('fp.upp_id', DB::raw('AVG(COALESCE(fj.score, 0)) as f03_avg'))
                ->whereNull('fp.deleted_at')
                ->when(!empty($this->periode_id), function ($query) {
                    $query->where('fp.periode_id', $this->periode_id);
                })
                ->groupBy('fp.upp_id')
                ->get()
                ->keyBy('upp_id');

            $f03ResponseRows = DB::table('f03_pengisian as fp')
                ->select('fp.upp_id', DB::raw('COUNT(*) as total_responses'))
                ->whereNull('fp.deleted_at')
                ->when(!empty($this->periode_id), function ($query) {
                    $query->where('fp.periode_id', $this->periode_id);
                })
                ->groupBy('fp.upp_id')
                ->get()
                ->keyBy('upp_id');

            $minimumResponses = $this->getF03MinimumResponsesForCurrentScope();

            // UPP ready-to-export if has latest F02 validation with status selesai.
            $readyRows = DB::table('f02_validasi as fv')
                ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
                ->select('fp.upp_id', DB::raw('COUNT(*) as total'))
                ->where('fp.is_latest_version', 1)
                ->where('fv.status', '=', 'selesai')
                ->when(!empty($this->periode_id), function ($query) {
                    $query->where('fv.periode_id', $this->periode_id);
                })
                ->groupBy('fp.upp_id')
                ->get()
                ->keyBy('upp_id');

            // F01 submitted (status != draft, meaning dikirim/diverifikasi/dikembalikan)
            $f01SubmittedRows = DB::table('f01_pengisian as fp')
                ->select('upp_id', DB::raw('COUNT(*) as total'))
                ->where('is_latest_version', 1)
                ->whereNotNull('dikirim_pada')
                ->whereNull('deleted_at')
                ->when(!empty($this->periode_id), function ($query) {
                    $query->where('periode_id', $this->periode_id);
                })
                ->groupBy('upp_id')
                ->get()
                ->keyBy('upp_id');

            // F02 validation (any status, to check if belum validasi)
            $f02ValidationRows = DB::table('f02_validasi as fv')
                ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
                ->select('fp.upp_id', DB::raw('COUNT(*) as total'))
                ->where('fp.is_latest_version', 1)
                ->whereNull('fp.deleted_at')
                ->when(!empty($this->periode_id), function ($query) {
                    $query->where('fv.periode_id', $this->periode_id);
                })
                ->groupBy('fp.upp_id')
                ->get()
                ->keyBy('upp_id');

            $this->upp_options = $uppBase
                ->map(function ($upp) use ($f02Rows, $f03Rows, $f03ResponseRows, $readyRows, $f01SubmittedRows, $f02ValidationRows, $minimumResponses) {
                    $f02Avg = isset($f02Rows[$upp['id']]) ? (float) $f02Rows[$upp['id']]->f02_avg : 0.0;
                    $f03Avg = isset($f03Rows[$upp['id']]) ? (float) $f03Rows[$upp['id']]->f03_avg : 0.0;
                    $f03Count = isset($f03ResponseRows[$upp['id']]) ? (int) $f03ResponseRows[$upp['id']]->total_responses : 0;
                    $effectiveF03 = $minimumResponses <= 0 || $f03Count >= $minimumResponses ? $f03Avg : 0.0;
                    $ippValue = ($f02Avg * 0.75) + ($effectiveF03 * 0.25);

                    $upp['ipp_value'] = round($ippValue, 4);
                    $upp['is_export_ready'] = isset($readyRows[$upp['id']]) && (int) $readyRows[$upp['id']]->total > 0;

                    // Determine validation status
                    $hasF01Submitted = isset($f01SubmittedRows[$upp['id']]) && (int) $f01SubmittedRows[$upp['id']]->total > 0;
                    $hasF02Validation = isset($f02ValidationRows[$upp['id']]) && (int) $f02ValidationRows[$upp['id']]->total > 0;
                    $hasF02Complete = isset($readyRows[$upp['id']]) && (int) $readyRows[$upp['id']]->total > 0;

                    // Status: Sudah Validasi, Belum Validasi (Sudah Submit), or Belum Submit
                    if ($hasF02Complete) {
                        $upp['validation_status'] = 'sudah_validasi';
                        $upp['validation_label'] = 'Sudah Validasi';
                        $upp['validation_color'] = '#166534';
                        $upp['validation_bg'] = '#bbf7d0';
                    } elseif ($hasF01Submitted) {
                        $upp['validation_status'] = 'belum_validasi';
                        $upp['validation_label'] = 'Belum Validasi (Sudah Submit)';
                        $upp['validation_color'] = '#b45309';
                        $upp['validation_bg'] = '#fef3c7';
                    } else {
                        $upp['validation_status'] = 'belum_submit';
                        $upp['validation_label'] = 'Belum Submit';
                        $upp['validation_color'] = '#dc2626';
                        $upp['validation_bg'] = '#fee2e2';
                    }

                    return $upp;
                })
                ->sortByDesc('ipp_value')
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading filter options', ['error' => $e->getMessage()]);
        }
    }

    private function getF03MinimumResponsesForCurrentScope(): int
    {
        $periodeId = !empty($this->periode_id)
            ? (int) $this->periode_id
            : (int) DB::table('periode')->where('is_aktif', 1)->value('id');

        if (empty($periodeId)) {
            return 0;
        }

        return (int) (DB::table('periode')->where('id', $periodeId)->value('target_responden_f03') ?? 0);
    }

    public function loadAllChartData()
    {
        $loaders = [
            'loadF02ChartData',
            'loadF03ChartData',
            'loadF03AspekChartData',
            'loadIPPChartData',
            'loadAspekChartData',
            'loadSummaryCards',
        ];

        foreach ($loaders as $loader) {
            try {
                $this->{$loader}();
            } catch (\Throwable $e) {
                Log::error('Error loading analytics segment', [
                    'loader' => $loader,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getScopedUppIds(): array
    {
        $selectedUppIds = $this->normalizeUppIds($this->upp_id);

        if (!empty($selectedUppIds)) {
            return $selectedUppIds;
        }

        return collect($this->upp_options)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->values()
            ->all();
    }

    private function getIppCategoryMeta(float $value): array
    {
        if ($value <= 1.00) {
            return ['kategori' => 'F', 'makna' => 'Pembinaan Intensif'];
        }
        if ($value <= 1.50) {
            return ['kategori' => 'E', 'makna' => 'Prioritas Pembinaan'];
        }
        if ($value <= 2.00) {
            return ['kategori' => 'D', 'makna' => 'Pembinaan'];
        }
        if ($value <= 2.50) {
            return ['kategori' => 'C-', 'makna' => 'Cukup (DC)'];
        }
        if ($value <= 3.00) {
            return ['kategori' => 'C', 'makna' => 'Cukup'];
        }
        if ($value <= 3.50) {
            return ['kategori' => 'B-', 'makna' => 'Baik (DC)'];
        }
        if ($value <= 4.00) {
            return ['kategori' => 'B', 'makna' => 'Baik'];
        }
        if ($value <= 4.50) {
            return ['kategori' => 'A-', 'makna' => 'Sangat Baik'];
        }

        return ['kategori' => 'A', 'makna' => 'Pelayanan Prima'];
    }

    private function loadSummaryCards(): void
    {
        $scopedUppIds = $this->getScopedUppIds();
        $totalUpp = count($scopedUppIds);
        $minimumResponses = $this->getF03MinimumResponsesForCurrentScope();

        $labelByUppId = collect($this->upp_options)
            ->pluck('label', 'id')
            ->mapWithKeys(fn($label, $id) => [(int) $id => (string) $label]);

        $f02ByUpp = DB::table('f02_validasi as fv')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->select('fp.upp_id', DB::raw('SUM(COALESCE(fv.total_nilai, 0)) as f02_value'))
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '!=', 'draft')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fv.periode_id', $this->periode_id);
            })
            ->when(!empty($scopedUppIds), function ($query) use ($scopedUppIds) {
                $query->whereIn('fp.upp_id', $scopedUppIds);
            })
            ->groupBy('fp.upp_id')
            ->get()
            ->mapWithKeys(fn($row) => [(int) $row->upp_id => (float) $row->f02_value]);

        $f03ByUpp = DB::table('f03_jawaban as fj')
            ->join('f03_pengisian as fp', 'fj.f03_pengisian_id', '=', 'fp.id')
            ->select('fp.upp_id', DB::raw('AVG(COALESCE(fj.score, 0)) as f03_value'))
            ->whereNull('fp.deleted_at')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fp.periode_id', $this->periode_id);
            })
            ->when(!empty($scopedUppIds), function ($query) use ($scopedUppIds) {
                $query->whereIn('fp.upp_id', $scopedUppIds);
            })
            ->groupBy('fp.upp_id')
            ->get()
            ->mapWithKeys(fn($row) => [(int) $row->upp_id => (float) $row->f03_value]);

        $f03ResponseCountByUpp = DB::table('f03_pengisian as fp')
            ->select('fp.upp_id', DB::raw('COUNT(*) as total_responses'))
            ->whereNull('fp.deleted_at')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fp.periode_id', $this->periode_id);
            })
            ->when(!empty($scopedUppIds), function ($query) use ($scopedUppIds) {
                $query->whereIn('fp.upp_id', $scopedUppIds);
            })
            ->groupBy('fp.upp_id')
            ->get()
            ->mapWithKeys(fn($row) => [(int) $row->upp_id => (int) $row->total_responses]);

        $ippByUpp = collect($this->upp_options)
            ->filter(fn($upp) => in_array((int) ($upp['id'] ?? 0), $scopedUppIds, true))
            ->mapWithKeys(fn($upp) => [(int) $upp['id'] => (float) ($upp['ipp_value'] ?? 0)]);

        $submittedUpps = DB::table('f01_pengisian as fp')
            ->select('fp.upp_id', DB::raw('MAX(fp.updated_at) as last_submit_at'))
            ->where('fp.is_latest_version', 1)
            ->where('fp.status', '!=', 'draft')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fp.periode_id', $this->periode_id);
            })
            ->when(!empty($scopedUppIds), function ($query) use ($scopedUppIds) {
                $query->whereIn('fp.upp_id', $scopedUppIds);
            })
            ->groupBy('fp.upp_id')
            ->get()
            ->mapWithKeys(fn($row) => [(int) $row->upp_id => $row->last_submit_at]);

        $validatedUpps = DB::table('f02_validasi as fv')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->select('fp.upp_id', DB::raw('MAX(fv.divalidasi_pada) as last_validated_at'))
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '=', 'selesai')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fv.periode_id', $this->periode_id);
            })
            ->when(!empty($scopedUppIds), function ($query) use ($scopedUppIds) {
                $query->whereIn('fp.upp_id', $scopedUppIds);
            })
            ->groupBy('fp.upp_id')
            ->get()
            ->mapWithKeys(fn($row) => [(int) $row->upp_id => $row->last_validated_at]);

        $scopedRows = collect($scopedUppIds)->map(function ($uppId) use ($labelByUppId, $f02ByUpp, $f03ByUpp, $ippByUpp, $submittedUpps, $validatedUpps, $f03ResponseCountByUpp, $minimumResponses) {
            $f02 = (float) ($f02ByUpp->get((int) $uppId, 0));
            $f03Raw = (float) ($f03ByUpp->get((int) $uppId, 0));
            $responseCount = (int) ($f03ResponseCountByUpp->get((int) $uppId, 0));
            $isUnderMinimum = $minimumResponses > 0 && $responseCount < $minimumResponses;
            $f03Effective = $isUnderMinimum ? 0.0 : $f03Raw;
            $ippCalculated = ($f02 * 0.75) + ($f03Effective * 0.25);

            return [
                'upp_id' => (int) $uppId,
                'upp_label' => $labelByUppId->get((int) $uppId, 'UPP-' . $uppId),
                'f02' => $f02,
                'f03' => $f03Raw,
                'f03_effective' => $f03Effective,
                'f03_response_count' => $responseCount,
                'f03_under_minimum' => $isUnderMinimum,
                'ipp' => (float) ($ippByUpp->get((int) $uppId, $ippCalculated)),
                'submitted_at' => $submittedUpps->get((int) $uppId),
                'validated_at' => $validatedUpps->get((int) $uppId),
            ];
        });

        $avgF02 = $scopedRows->avg('f02') ?? 0;
        $avgF03 = $scopedRows->avg('f03_effective') ?? 0;
        $avgIpp = $scopedRows->avg('ipp') ?? 0;

        $uppBaik = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) >= 3.01)->count();
        $uppPerluPembinaan = max($totalUpp - $uppBaik, 0);
        $totalF03Responses = (int) $f03ResponseCountByUpp->sum();
        $underMinimumUppCount = $scopedRows->filter(fn($row) => !empty($row['f03_under_minimum']))->count();

        $submittedQuery = DB::table('f01_pengisian as fp')
            ->select('fp.upp_id')
            ->where('fp.is_latest_version', 1)
            ->where('fp.status', '!=', 'draft');

        if (!empty($this->periode_id)) {
            $submittedQuery->where('fp.periode_id', $this->periode_id);
        }

        if (!empty($scopedUppIds)) {
            $submittedQuery->whereIn('fp.upp_id', $scopedUppIds);
        }

        $submittedUppCount = (int) $submittedQuery->distinct('fp.upp_id')->count('fp.upp_id');

        $validatedQuery = DB::table('f02_validasi as fv')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->select('fp.upp_id')
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '=', 'selesai');

        if (!empty($this->periode_id)) {
            $validatedQuery->where('fv.periode_id', $this->periode_id);
        }

        if (!empty($scopedUppIds)) {
            $validatedQuery->whereIn('fp.upp_id', $scopedUppIds);
        }

        $validatedUppCount = (int) $validatedQuery->distinct('fp.upp_id')->count('fp.upp_id');
        $pendingValidationCount = max($submittedUppCount - $validatedUppCount, 0);
        $notSubmittedUppCount = max($totalUpp - $submittedUppCount, 0);

        $ippCategory = $this->getIppCategoryMeta((float) $avgIpp);

        $this->summary_cards = [
            'total_upp' => $totalUpp,
            'avg_f02' => round((float) $avgF02, 2),
            'avg_f03' => round((float) $avgF03, 2),
            'avg_ipp' => round((float) $avgIpp, 2),
            'f02_contribution' => round((float) $avgF02 * 0.75, 2),
            'f03_contribution' => round((float) $avgF03 * 0.25, 2),
            'ipp_status' => $ippCategory['makna'],
            'ipp_category' => $ippCategory['kategori'],
            'ipp_category_label' => $ippCategory['kategori'] . ' - ' . $ippCategory['makna'],
            'upp_baik' => $uppBaik,
            'upp_perlu_pembinaan' => $uppPerluPembinaan,
            'sudah_submit' => $submittedUppCount,
            'belum_submit' => $notSubmittedUppCount,
            'belum_validasi' => $pendingValidationCount,
            'sudah_selesai' => $validatedUppCount,
            'f03_response_count' => $totalF03Responses,
            'f03_minimum_target' => $minimumResponses,
            'f03_under_minimum_upp_count' => $underMinimumUppCount,
        ];

        $toRows = function ($rows, $metricKey, $metricLabel, $extraBuilder = null) {
            return collect($rows)->values()->map(function ($row, $index) use ($metricKey, $metricLabel, $extraBuilder) {
                $metricValue = (float) ($row[$metricKey] ?? 0);
                $extra = is_callable($extraBuilder) ? (string) $extraBuilder($row) : '';

                return [
                    'no' => $index + 1,
                    'upp' => $row['upp_label'] ?? '-',
                    'metric_label' => $metricLabel,
                    'metric_value' => number_format($metricValue, 2),
                    'extra' => $extra,
                ];
            })->all();
        };

        $sortedByIppDesc = $scopedRows->sortByDesc('ipp')->values();
        $sortedByF02Desc = $scopedRows->sortByDesc('f02')->values();
        $sortedByF03EffectiveDesc = $scopedRows->sortByDesc('f03_effective')->values();

        $uppBaikRows = $scopedRows
            ->filter(fn($row) => (float) ($row['ipp'] ?? 0) >= 3.01)
            ->sortByDesc('ipp')
            ->values();

        $uppPerluPembinaanRows = $scopedRows
            ->filter(fn($row) => (float) ($row['ipp'] ?? 0) < 3.01)
            ->sortByDesc('ipp')
            ->values();

        $submittedRows = $scopedRows
            ->filter(fn($row) => !empty($row['submitted_at']))
            ->sortByDesc('submitted_at')
            ->values();

        $validatedRows = $scopedRows
            ->filter(fn($row) => !empty($row['validated_at']))
            ->sortByDesc('validated_at')
            ->values();

        $pendingRows = $submittedRows
            ->filter(fn($row) => empty($row['validated_at']))
            ->sortByDesc('ipp')
            ->values();

        $notSubmittedRows = $scopedRows
            ->filter(fn($row) => empty($row['submitted_at']))
            ->sortByDesc('ipp')
            ->values();

        $this->summary_card_details = [
            'total_upp' => [
                'title' => 'Daftar UPP dalam Cakupan Filter',
                'subtitle' => 'Urutan berdasarkan nilai IPP tertinggi',
                'rows' => $toRows($sortedByIppDesc, 'ipp', 'Nilai IPP', function ($row) {
                    return 'F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03'] ?? 0), 2);
                }),
            ],
            'avg_f02' => [
                'title' => 'Daftar UPP - Skor F02',
                'subtitle' => 'Urutan skor F02 tertinggi ke terendah',
                'rows' => $toRows($sortedByF02Desc, 'f02', 'Skor F02', function ($row) {
                    return 'IPP: ' . number_format((float) ($row['ipp'] ?? 0), 2);
                }),
            ],
            'avg_f03' => [
                'title' => 'Daftar UPP - Skor F03',
                'subtitle' => 'Urutan skor F03 efektif (setelah aturan minimum responden)',
                'rows' => $toRows($sortedByF03EffectiveDesc, 'f03_effective', 'Skor F03', function ($row) use ($minimumResponses) {
                    $resp = (int) ($row['f03_response_count'] ?? 0);
                    $note = ($minimumResponses > 0 && !empty($row['f03_under_minimum']))
                        ? ' | Di bawah batas minimal (' . $minimumResponses . ') -> skor efektif 0'
                        : '';
                    return 'Responden: ' . $resp . $note;
                }),
            ],
            'avg_ipp' => [
                'title' => 'Daftar UPP - Indeks Pelayanan Publik',
                'subtitle' => 'Urutan nilai IPP tertinggi ke terendah',
                'rows' => $toRows($sortedByIppDesc, 'ipp', 'Nilai IPP', function ($row) {
                    $ippMeta = $this->getIppCategoryMeta((float) ($row['ipp'] ?? 0));
                    return 'Predikat: ' . $ippMeta['kategori'] . ' - ' . $ippMeta['makna']
                        . ' | F02: ' . number_format((float) ($row['f02'] ?? 0), 2)
                        . ' | F03 Efektif: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
                }),
            ],
            'upp_baik' => [
                'title' => 'Daftar UPP Baik',
                'subtitle' => 'Kategori baik (IPP >= 3.01), urut tertinggi ke terendah',
                'rows' => $toRows($uppBaikRows, 'ipp', 'Nilai IPP', function ($row) {
                    $ippMeta = $this->getIppCategoryMeta((float) ($row['ipp'] ?? 0));
                    return 'Predikat: ' . $ippMeta['kategori'] . ' - ' . $ippMeta['makna'];
                }),
            ],
            'upp_perlu_pembinaan' => [
                'title' => 'Daftar UPP Perlu Pembinaan',
                'subtitle' => 'Kategori pembinaan (IPP < 3.01), urut nilai tertinggi ke terendah',
                'rows' => $toRows($uppPerluPembinaanRows, 'ipp', 'Nilai IPP', function ($row) {
                    $ippMeta = $this->getIppCategoryMeta((float) ($row['ipp'] ?? 0));
                    return 'Predikat: ' . $ippMeta['kategori'] . ' - ' . $ippMeta['makna'];
                }),
            ],
            'sudah_submit' => [
                'title' => 'Daftar UPP Sudah Submit F01',
                'subtitle' => 'Urutan berdasarkan waktu submit terbaru',
                'rows' => $toRows($submittedRows, 'ipp', 'Nilai IPP', function ($row) {
                    $submitAt = !empty($row['submitted_at']) ? date('d/m/Y H:i', strtotime((string) $row['submitted_at'])) : '-';
                    return 'Submit: ' . $submitAt;
                }),
            ],
            'belum_submit' => [
                'title' => 'Daftar UPP Belum Submit F02/F01',
                'subtitle' => 'UPP yang belum mengirim pengisian pada filter aktif',
                'rows' => $toRows($notSubmittedRows, 'ipp', 'Nilai IPP', function ($row) {
                    return 'Status: Belum Submit';
                }),
            ],
            'belum_validasi' => [
                'title' => 'Daftar UPP Belum Validasi F02',
                'subtitle' => 'UPP yang sudah submit namun belum selesai divalidasi',
                'rows' => $toRows($pendingRows, 'ipp', 'Nilai IPP', function ($row) {
                    $submitAt = !empty($row['submitted_at']) ? date('d/m/Y H:i', strtotime((string) $row['submitted_at'])) : '-';
                    return 'Submit: ' . $submitAt . ' | Status: Belum Validasi';
                }),
            ],
            'sudah_selesai' => [
                'title' => 'Daftar UPP Sudah Selesai Validasi',
                'subtitle' => 'UPP dengan validasi F02 selesai',
                'rows' => $toRows($validatedRows, 'f02', 'Skor F02', function ($row) {
                    $validAt = !empty($row['validated_at']) ? date('d/m/Y H:i', strtotime((string) $row['validated_at'])) : '-';
                    return 'Validasi: ' . $validAt;
                }),
            ],
        ];

        // Add IPP Category breakdowns
        $ippCategoryA = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) > 4.50)->sortByDesc('ipp')->values();
        $ippCategoryAMinus = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) > 4.00 && (float) ($row['ipp'] ?? 0) <= 4.50)->sortByDesc('ipp')->values();
        $ippCategoryB = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) > 3.50 && (float) ($row['ipp'] ?? 0) <= 4.00)->sortByDesc('ipp')->values();
        $ippCategoryBMinus = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) > 3.00 && (float) ($row['ipp'] ?? 0) <= 3.50)->sortByDesc('ipp')->values();
        $ippCategoryC = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) > 2.50 && (float) ($row['ipp'] ?? 0) <= 3.00)->sortByDesc('ipp')->values();
        $ippCategoryCMinus = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) > 2.00 && (float) ($row['ipp'] ?? 0) <= 2.50)->sortByDesc('ipp')->values();
        $ippPrioritasPembinaan = $scopedRows->filter(fn($row) => (float) ($row['ipp'] ?? 0) <= 2.00)->sortByDesc('ipp')->values();

        // Add counts to summary_cards
        $this->summary_cards['ipp_a_prima'] = $ippCategoryA->count();
        $this->summary_cards['ipp_a_minus'] = $ippCategoryAMinus->count();
        $this->summary_cards['ipp_b'] = $ippCategoryB->count();
        $this->summary_cards['ipp_b_minus'] = $ippCategoryBMinus->count();
        $this->summary_cards['ipp_c'] = $ippCategoryC->count();
        $this->summary_cards['ipp_c_minus'] = $ippCategoryCMinus->count();
        $this->summary_cards['ipp_prioritas_pembinaan'] = $ippPrioritasPembinaan->count();

        // Add IPP category details
        $this->summary_card_details['ipp_a_prima'] = [
            'title' => 'Daftar UPP - Pelayanan Prima (A)',
            'subtitle' => 'IPP > 4.50, urut tertinggi ke terendah',
            'rows' => $toRows($ippCategoryA, 'ipp', 'Nilai IPP', function ($row) {
                return 'F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
            }),
        ];
        $this->summary_card_details['ipp_a_minus'] = [
            'title' => 'Daftar UPP - Sangat Baik (A-)',
            'subtitle' => 'IPP 4.01-4.50, urut tertinggi ke terendah',
            'rows' => $toRows($ippCategoryAMinus, 'ipp', 'Nilai IPP', function ($row) {
                return 'F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
            }),
        ];
        $this->summary_card_details['ipp_b'] = [
            'title' => 'Daftar UPP - Baik (B)',
            'subtitle' => 'IPP 3.51-4.00, urut tertinggi ke terendah',
            'rows' => $toRows($ippCategoryB, 'ipp', 'Nilai IPP', function ($row) {
                return 'F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
            }),
        ];
        $this->summary_card_details['ipp_b_minus'] = [
            'title' => 'Daftar UPP - Baik Dengan Catatan (B-)',
            'subtitle' => 'IPP 3.01-3.50, urut tertinggi ke terendah',
            'rows' => $toRows($ippCategoryBMinus, 'ipp', 'Nilai IPP', function ($row) {
                return 'F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
            }),
        ];
        $this->summary_card_details['ipp_c'] = [
            'title' => 'Daftar UPP - Cukup (C)',
            'subtitle' => 'IPP 2.51-3.00, urut tertinggi ke terendah',
            'rows' => $toRows($ippCategoryC, 'ipp', 'Nilai IPP', function ($row) {
                return 'F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
            }),
        ];
        $this->summary_card_details['ipp_c_minus'] = [
            'title' => 'Daftar UPP - Cukup Dengan Catatan (C-)',
            'subtitle' => 'IPP 2.01-2.50, urut tertinggi ke terendah',
            'rows' => $toRows($ippCategoryCMinus, 'ipp', 'Nilai IPP', function ($row) {
                return 'F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
            }),
        ];
        $this->summary_card_details['ipp_prioritas_pembinaan'] = [
            'title' => 'Daftar UPP - Prioritas Pembinaan',
            'subtitle' => 'IPP <= 2.00 (Kategori D, E, F), urut tertinggi ke terendah',
            'rows' => $toRows($ippPrioritasPembinaan, 'ipp', 'Nilai IPP', function ($row) {
                $ippMeta = $this->getIppCategoryMeta((float) ($row['ipp'] ?? 0));
                return 'Kategori: ' . $ippMeta['kategori'] . ' | F02: ' . number_format((float) ($row['f02'] ?? 0), 2) . ' | F03: ' . number_format((float) ($row['f03_effective'] ?? 0), 2);
            }),
        ];
    }

    public function loadF02ChartData()
    {
        // Query F02 nilai (sudah dihitung dengan bobot aspek) per UPP
        // Menggunakan total_nilai dari f02_validasi yang sudah diolah
        $query = DB::table('f02_validasi as fv')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->join('upps as u', 'fp.upp_id', '=', 'u.id')
            ->select(
                'fp.upp_id',
                'u.nama as upp_nama',
                DB::raw('SUM(COALESCE(fv.total_nilai, 0)) as total_skor')
            )
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '!=', 'draft')
            ->groupBy('fp.upp_id', 'u.nama');

        if (!empty($this->periode_id)) {
            $query->where('fv.periode_id', '=', $this->periode_id);
        }

        $selectedUppIds = $this->normalizeUppIds($this->upp_id);
        if (!empty($selectedUppIds)) {
            $query->whereIn('fp.upp_id', $selectedUppIds);
        }

        $data = $query->orderByDesc('total_skor')->get();

        $data = $data->filter(fn($row) => $row->total_skor > 0);

        $labelByUppId = collect($this->upp_options)->pluck('label', 'id');
        $this->f02_labels = $data->map(function($row) use ($labelByUppId) {
            return $labelByUppId->get((int) $row->upp_id, strtoupper($row->upp_nama));
        })->toArray();
        $this->f02_data = $data->map(fn($row) => (float)$row->total_skor)->toArray();
    }

    public function loadF03ChartData()
    {
        // Query F03 score per UPP (rata-rata score F03 tiap UPP)
        $query = DB::table('f03_jawaban as fj')
            ->join('f03_pengisian as fp', 'fj.f03_pengisian_id', '=', 'fp.id')
            ->join('upps as u', 'fp.upp_id', '=', 'u.id')
            ->select(
                'fp.upp_id',
                'u.nama as upp_nama',
                DB::raw('AVG(COALESCE(fj.score, 0)) as total_skor')
            )
            ->where('fp.deleted_at', null)
            ->groupBy('fp.upp_id', 'u.nama');

        if (!empty($this->periode_id)) {
            $query->where('fp.periode_id', '=', $this->periode_id);
        }

        $selectedUppIds = $this->normalizeUppIds($this->upp_id);
        if (!empty($selectedUppIds)) {
            $query->whereIn('fp.upp_id', $selectedUppIds);
        }

        $data = $query->orderByDesc('total_skor')->get();

        // Filter out zero scores
        $data = $data->filter(fn($row) => $row->total_skor > 0);

        $labelByUppId = collect($this->upp_options)->pluck('label', 'id');
        $this->f03_labels = $data->map(function($row) use ($labelByUppId) {
            return $labelByUppId->get((int) $row->upp_id, strtoupper($row->upp_nama));
        })->toArray();
        $this->f03_data = $data->map(fn($row) => (float)$row->total_skor)->toArray();
    }

    public function loadIPPChartData()
    {
        // IPP = (75% * AVG(F02)) + (25% * AVG(F03))
        // Gunakan dua query terpisah kemudian combine di PHP

        // Query F02 (latest version only)
        $f02Query = DB::table('f02_validasi as fv')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->join('upps as u', 'fp.upp_id', '=', 'u.id')
            ->select(
                'fp.upp_id',
                'u.nama as upp_nama',
                DB::raw('AVG(COALESCE(fv.total_nilai, 0)) as f02_avg')
            )
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '!=', 'draft')
            ->groupBy('fp.upp_id', 'u.nama');

        if (!empty($this->periode_id)) {
            $f02Query->where('fv.periode_id', '=', $this->periode_id);
        }

        $selectedUppIds = $this->normalizeUppIds($this->upp_id);
        if (!empty($selectedUppIds)) {
            $f02Query->whereIn('fp.upp_id', $selectedUppIds);
        }

        $f02Data = $f02Query->get();

        // Query F03
        $f03Query = DB::table('f03_jawaban as fj')
            ->join('f03_pengisian as fp', 'fj.f03_pengisian_id', '=', 'fp.id')
            ->select(
                'fp.upp_id',
                DB::raw('AVG(COALESCE(fj.score, 0)) as f03_avg')
            )
            ->where('fp.deleted_at', null)
            ->groupBy('fp.upp_id');

        if (!empty($this->periode_id)) {
            $f03Query->where('fp.periode_id', '=', $this->periode_id);
        }

        $selectedUppIds = $this->normalizeUppIds($this->upp_id);
        if (!empty($selectedUppIds)) {
            $f03Query->whereIn('fp.upp_id', $selectedUppIds);
        }

        $f03Data = $f03Query->get()->keyBy('upp_id')->toArray();

        $f03ResponseRows = DB::table('f03_pengisian as fp')
            ->select('fp.upp_id', DB::raw('COUNT(*) as total_responses'))
            ->whereNull('fp.deleted_at')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fp.periode_id', $this->periode_id);
            })
            ->groupBy('fp.upp_id')
            ->get()
            ->keyBy('upp_id')
            ->toArray();

        $minimumResponses = $this->getF03MinimumResponsesForCurrentScope();

        // Combine F02 dan F03 dengan rumus IPP
        $combinedData = $f02Data->map(function($row) use ($f03Data, $f03ResponseRows, $minimumResponses) {
            $f02_avg = (float)$row->f02_avg;
            $f03_avg = isset($f03Data[$row->upp_id]) ? (float)$f03Data[$row->upp_id]->f03_avg : 0;
            $f03_count = isset($f03ResponseRows[$row->upp_id]) ? (int) $f03ResponseRows[$row->upp_id]->total_responses : 0;
            $effective_f03 = $minimumResponses <= 0 || $f03_count >= $minimumResponses ? $f03_avg : 0;
            $ipp_value = ($f02_avg * 0.75) + ($effective_f03 * 0.25);

            $row->ipp_value = $ipp_value;
            return $row;
        });

        // Filter out zero values dan sort
        $combinedData = $combinedData
            ->filter(fn($row) => $row->ipp_value > 0)
            ->sortByDesc('ipp_value');

        $labelByUppId = collect($this->upp_options)->pluck('label', 'id');
        $this->ipp_labels = $combinedData->map(function($row) use ($labelByUppId) {
            return $labelByUppId->get((int) $row->upp_id, strtoupper($row->upp_nama));
        })->values()->toArray();
        $this->ipp_data = $combinedData->map(fn($row) => (float)$row->ipp_value)->values()->toArray();
    }

    public function loadF03AspekChartData()
    {
        $scopedUppIds = $this->getScopedUppIds();

        // Aggregate F03 scores per UPP and Aspek
        $perUppAspekScores = DB::table('f03_jawaban as fj')
            ->join('f03_pengisian as fp', 'fj.f03_pengisian_id', '=', 'fp.id')
            ->join('f03_indikator as fi', 'fj.f03_indikator_id', '=', 'fi.id')
            ->join('f03_aspek as asp', 'fi.f03_aspek_id', '=', 'asp.id')
            ->select(
                'fp.upp_id',
                'fi.f03_aspek_id as aspek_id',
                DB::raw('AVG(COALESCE(fj.score, 0)) as upp_avg_score')
            )
            ->whereNull('fp.deleted_at')
            ->whereNull('fi.deleted_at')
            ->whereNull('asp.deleted_at')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fp.periode_id', '=', $this->periode_id);
            })
            ->when(!empty($scopedUppIds), function ($query) use ($scopedUppIds) {
                $query->whereIn('fp.upp_id', $scopedUppIds);
            })
            ->groupBy('fp.upp_id', 'fi.f03_aspek_id');

        // Average across all UPP per Aspek
        $data = DB::query()
            ->fromSub($perUppAspekScores, 'aspek_scores')
            ->join('aspek as asp', 'aspek_scores.aspek_id', '=', 'asp.id')
            ->select(
                'asp.id as aspek_id',
                'asp.kode as aspek_kode',
                'asp.nama as aspek_nama',
                'asp.urutan as aspek_urutan',
                DB::raw('AVG(aspek_scores.upp_avg_score) as avg_score')
            )
            ->groupBy('asp.id', 'asp.kode', 'asp.nama', 'asp.urutan')
            ->orderBy('asp.urutan')
            ->orderBy('asp.id')
            ->get();

        $labels = $data->map(function ($row) {
            $kode = trim((string) ($row->aspek_kode ?? ''));
            $nama = trim((string) ($row->aspek_nama ?? ''));

            if ($kode !== '' && $nama !== '') {
                return $kode . ' - ' . Str::limit($nama, 70);
            }

            return $kode !== '' ? $kode : $nama;
        })->toArray();

        $values = $data->map(fn($row) => round((float) $row->avg_score, 4))->toArray();
        $ids = $data->map(fn($row) => (int) $row->aspek_id)->toArray();

        $this->f03_aspek_ids = $ids;
        $this->f03_aspek_labels = $labels;
        $this->f03_aspek_values = $values;

        // Keep backward-compatibility aliases
        $this->f03_indikator_ids = $ids;
        $this->f03_indikator_labels = $labels;
        $this->f03_indikator_values = $values;
    }

    public function loadAspekChartData()
    {
        // Formula: Skor Aspek = (Total Skor / Total UPP) * Bobot Aspek / Jumlah Indikator
        // Breakdown:
        // 1. SUM(nilai_indikator) = total skor mentah per aspek
        // 2. COUNT(DISTINCT upp_id) = jumlah UPP yang berkontribusi pada aspek
        // 3. Bobot Aspek = bobot dari tabel aspek (dalam persen)
        // 4. COUNT(indikator_unik) = jumlah indikator dalam aspek

        $scopedUppIds = $this->getScopedUppIds();
        $totalScopedUpp = count($scopedUppIds);

        $query = DB::table('f02_indikator_validasi as fiv')
            ->join('f02_validasi as fv', 'fiv.f02_validasi_id', '=', 'fv.id')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->join('indikator as ind', 'fiv.indikator_id', '=', 'ind.id')
            ->join('aspek as asp', 'ind.aspek_id', '=', 'asp.id')
            ->select(
                'asp.id as aspek_id',
                'asp.nama as aspek_nama',
                'asp.bobot as bobot_aspek',
                DB::raw('SUM(COALESCE(fiv.nilai, 0)) as total_nilai'),
                DB::raw('COUNT(DISTINCT fp.upp_id) as total_upp'),
                DB::raw('COUNT(DISTINCT ind.id) as jumlah_indikator'),
                DB::raw('(SUM(COALESCE(fiv.nilai, 0)) / COUNT(DISTINCT fp.upp_id)) * (asp.bobot / 100) / COUNT(DISTINCT ind.id) as skor_aspek')
            )
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '!=', 'draft')
            ->groupBy('asp.id', 'asp.nama', 'asp.bobot');

        if (!empty($this->periode_id)) {
            $query->where('fv.periode_id', '=', $this->periode_id);
        }

        if (!empty($scopedUppIds)) {
            $query->whereIn('fp.upp_id', $scopedUppIds);
        }

        $data = $query->orderBy('asp.id')->get();
        $radarData = $data->reject(function ($row) {
            return trim((string) ($row->aspek_nama ?? '')) === 'Informasi Tambahan';
        })->values();

        // Build simple chart data for bar chart
        // Use simple average of indicators per aspek (total_nilai / total_upp / jumlah_indikator)
        // instead of weighted score (skor_aspek)
        $this->aspek_ids = $radarData->map(fn($row) => (int) $row->aspek_id)->toArray();
        $this->aspek_labels = $radarData->map(fn($row) => $row->aspek_nama)->toArray();
        $this->aspek_values = $radarData->map(function ($row) use ($totalScopedUpp) {
            $jumlahIndikator = (int) ($row->jumlah_indikator ?? 0);
            return ($totalScopedUpp > 0 && $jumlahIndikator > 0)
                ? round((float) $row->total_nilai / $totalScopedUpp / $jumlahIndikator, 4)
                : 0.0;
        })->toArray();

        $this->aspek_tabs = $data->map(function ($row) use ($totalScopedUpp) {
            $jumlahIndikator = (int) ($row->jumlah_indikator ?? 0);
            $rawRataRata = ($totalScopedUpp > 0 && $jumlahIndikator > 0)
                ? ((float) $row->total_nilai / $totalScopedUpp / $jumlahIndikator)
                : 0.0;

            $bobotAspek = (float) ($row->bobot_aspek ?? 0);
            $skorSetelahBobot = ($rawRataRata * $bobotAspek) / 100;

            return [
                'id' => (int) $row->aspek_id,
                'nama' => $row->aspek_nama,
                'bobot_aspek' => $bobotAspek,
                'rata_rata_indikator' => round($rawRataRata, 4),
                'skor_setelah_bobot' => round($skorSetelahBobot, 4),
            ];
        })->values()->toArray();

        $aspekIds = collect($this->aspek_tabs)->pluck('id')->all();
        if (empty($aspekIds)) {
            $this->aspek_indikator_scores = [];
            $this->selected_aspek_id = null;
            $this->closeAspekDetail();
            return;
        }

        $indikatorScoreSubquery = DB::table('f02_indikator_validasi as fiv')
            ->join('f02_validasi as fv', 'fiv.f02_validasi_id', '=', 'fv.id')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->select('fiv.indikator_id', DB::raw('SUM(COALESCE(fiv.nilai, 0)) as total_nilai'))
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '!=', 'draft')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fv.periode_id', '=', $this->periode_id);
            });

        if (!empty($scopedUppIds)) {
            $indikatorScoreSubquery->whereIn('fp.upp_id', $scopedUppIds);
        }

        $indikatorScoreSubquery->groupBy('fiv.indikator_id');

        $indikatorRows = DB::table('indikator as ind')
            ->join('aspek as asp', 'ind.aspek_id', '=', 'asp.id')
            ->leftJoinSub($indikatorScoreSubquery, 'score_map', function ($join) {
                $join->on('score_map.indikator_id', '=', 'ind.id');
            })
            ->select(
                'asp.id as aspek_id',
                'asp.nama as aspek_nama',
                'ind.id as indikator_id',
                'ind.kode as indikator_kode',
                'ind.nama as indikator_nama',
                'ind.urutan as indikator_urutan',
                DB::raw('COALESCE(score_map.total_nilai, 0) as indikator_total_nilai')
            )
            ->whereIn('asp.id', $aspekIds)
            ->where('ind.aktif', 1)
            ->orderBy('asp.id')
            ->orderBy('ind.urutan')
            ->orderBy('ind.id')
            ->get();

        $this->aspek_indikator_scores = $indikatorRows
            ->groupBy('aspek_id')
            ->map(function ($rows) use ($totalScopedUpp) {
                return $rows->values()->map(function ($row, $index) use ($totalScopedUpp) {
                    $indikatorTotalNilai = (float) ($row->indikator_total_nilai ?? 0);
                    $indikatorSkor = $totalScopedUpp > 0
                        ? ($indikatorTotalNilai / $totalScopedUpp)
                        : 0.0;

                    return [
                        'no' => $index + 1,
                        'indikator_id' => (int) $row->indikator_id,
                        'indikator_kode' => $row->indikator_kode,
                        'indikator_nama' => $row->indikator_nama,
                        'indikator_skor' => round($indikatorSkor, 4),
                    ];
                })->toArray();
            })
            ->toArray();

        $validTabIds = collect($this->aspek_tabs)->pluck('id')->all();
        if (empty($this->selected_aspek_id) || !in_array((int) $this->selected_aspek_id, $validTabIds, true)) {
            $this->selected_aspek_id = $validTabIds[0];
        }
    }

    public function updateFilters()
    {
        $this->resetPage();
        $this->closeAspekDetail();
        $this->loadAllChartData();
        $this->dispatchChartDataUpdated();
    }

    public function resetFilters()
    {
        $this->periode_id = null;
        $this->upp_id = [];
        $this->resetPage();
        $this->closeAspekDetail();
        $this->loadAllChartData();
        $this->dispatchChartDataUpdated();
    }

    public function exportF02ValidationExcelZip()
    {
        $selectedUppIds = $this->normalizeUppIds($this->upp_id);
        $requestedCount = count($selectedUppIds);
        if (empty($selectedUppIds)) {
            $selectedUppIds = collect($this->upp_options)->pluck('id')->map(fn($id) => (int) $id)->all();
            $requestedCount = count($selectedUppIds);
        }

        if (empty($selectedUppIds)) {
            $this->dispatch('analytics-export-failed', message: 'Export Excel dibatalkan: tidak ada UPP aktif yang bisa diexport.');
            return null;
        }

        $tempZipPath = tempnam(sys_get_temp_dir(), 'f02_export_excel_');
        $zip = new ZipArchive();

        if ($zip->open($tempZipPath, ZipArchive::OVERWRITE) !== true) {
            $this->dispatch('analytics-export-failed', message: 'Export Excel gagal: tidak bisa membuat file ZIP sementara.');
            return null;
        }

        $exportedCount = 0;
        $skippedUppNames = [];
        $uppLabelById = collect($this->upp_options)
            ->pluck('label', 'id')
            ->mapWithKeys(fn($label, $id) => [(int) $id => (string) $label]);
        $usedFileNames = [];

        foreach ($selectedUppIds as $uppId) {
            $validasi = $this->getLatestF02ValidasiForUpp($uppId);
            if (!$validasi) {
                $uppName = collect($this->upp_options)->firstWhere('id', (int) $uppId)['label'] ?? ('UPP-' . $uppId);
                $skippedUppNames[] = $uppName;
                continue;
            }

            $excelContent = $this->buildF02ValidationExcelContent($validasi);
            $displayLabel = $uppLabelById->get((int) $uppId, $validasi->upp_nama ?: ('UPP-' . $uppId));
            $baseName = Str::slug($displayLabel);
            if ($baseName === '') {
                $baseName = 'upp-' . $uppId;
            }

            $baseName = Str::limit($baseName, 40, '');
            $fileName = $baseName . '.xlsx';

            if (isset($usedFileNames[$fileName])) {
                $usedFileNames[$fileName]++;
                $fileName = sprintf('%s-%d.xlsx', $baseName, $usedFileNames[$fileName]);
            } else {
                $usedFileNames[$fileName] = 1;
            }

            $zip->addFromString($fileName, $excelContent);
            $exportedCount++;
        }

        $zip->close();

        if ($exportedCount === 0) {
            @unlink($tempZipPath);
            $this->dispatch('analytics-export-failed', message: 'Tidak ada data validasi F02 berstatus selesai untuk filter aktif.');
            return null;
        }

        $periodeLabel = !empty($this->periode_id)
            ? DB::table('periode')->where('id', $this->periode_id)->value(DB::raw("CONCAT(tahun, '-', nama)"))
            : 'multi-periode';
        $safePeriodeLabel = Str::slug($periodeLabel ?: 'multi-periode');
        $zipFileName = 'f02_validasi_export_excel_' . $safePeriodeLabel . '_' . now()->format('Ymd_His') . '.zip';

        $message = "Export Excel berhasil: {$exportedCount} dari {$requestedCount} UPP masuk ke ZIP.";
        if (!empty($skippedUppNames)) {
            $preview = implode(', ', array_slice($skippedUppNames, 0, 3));
            $remaining = count($skippedUppNames) - min(count($skippedUppNames), 3);
            $suffix = $remaining > 0 ? " (+{$remaining} lainnya)" : '';
            $message .= " UPP dilewati (belum selesai validasi): {$preview}{$suffix}.";
        }

        $this->dispatch('analytics-export-success', message: $message);

        return response()
            ->download($tempZipPath, $zipFileName, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    #[On('setUppFilter')]
    public function setUppFilter($upp_id)
    {
        $normalizedUppIds = $this->normalizeUppIds($upp_id);

        // Guard against stale localStorage IDs: keep only active UPP IDs from current options.
        $validUppIds = collect($this->upp_options)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->values()
            ->all();

        if (!empty($normalizedUppIds)) {
            $normalizedUppIds = array_values(array_intersect($normalizedUppIds, $validUppIds));
        }

        $this->upp_id = $normalizedUppIds;

        if (!empty($upp_id) && empty($this->upp_id)) {
            Log::warning('UPP filter payload is invalid for current scope; falling back to all UPP', ['payload' => $upp_id]);
        }

        Log::info('Applying analytics UPP filter', ['upp_ids' => $this->upp_id]);
        $this->updateFilters();
    }

    private function normalizeUppIds($uppIds): array
    {
        if (empty($uppIds)) {
            return [];
        }

        $normalized = is_array($uppIds) ? $uppIds : [$uppIds];

        return array_values(array_filter(array_map('intval', $normalized), function ($value) {
            return $value > 0;
        }));
    }

    private function getLatestF02ValidasiForUpp(int $uppId)
    {
        return DB::table('f02_validasi as fv')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->join('upps as u', 'fp.upp_id', '=', 'u.id')
            ->join('periode as p', 'fv.periode_id', '=', 'p.id')
            ->select(
                'fv.id as f02_validasi_id',
                'fv.f01_pengisian_id',
                'fv.periode_id',
                'fv.total_nilai',
                'fv.nilai_mentah',
                'fv.status',
                'fv.divalidasi_pada',
                'u.id as upp_id',
                'u.nama as upp_nama',
                'p.tahun as periode_tahun',
                'p.nama as periode_nama'
            )
            ->where('fp.upp_id', $uppId)
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '=', 'selesai')
            ->when(!empty($this->periode_id), function ($query) {
                $query->where('fv.periode_id', $this->periode_id);
            })
            ->orderByDesc('fv.divalidasi_pada')
            ->orderByDesc('fv.updated_at')
            ->first();
    }

    private function buildF02ValidationExcelContent($validasi): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Validasi F02');
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $sheet->setCellValue('A1', 'Laporan Hasil Validasi F02');
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', 'UPP');
        $sheet->setCellValue('B2', $validasi->upp_nama ?? '-');
        $sheet->setCellValue('A3', 'Periode');
        $sheet->setCellValue('B3', trim(($validasi->periode_tahun ?? '') . ' - ' . ($validasi->periode_nama ?? '')));
        $sheet->setCellValue('A4', 'Tanggal Export');
        $sheet->setCellValue('B4', now()->format('d/m/Y H:i:s'));

        $headerRow = 6;
        $sheet->fromArray([
            'Nomor',
            'Nama Indikator',
            'Jawaban hasil validasi',
            'Skor (0-5)',
            'Link bukti dukung',
            'Catatan evaluator',
        ], null, 'A' . $headerRow);

        $aspeks = DB::table('aspek')
            ->select('id', 'kode', 'nama', 'bobot', 'urutan')
            ->where('periode_id', $validasi->periode_id)
            ->where('aktif', 1)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $indikatorRows = DB::table('indikator as i')
            ->leftJoin('f02_indikator_validasi as fiv', function ($join) use ($validasi) {
                $join->on('fiv.indikator_id', '=', 'i.id')
                    ->where('fiv.f02_validasi_id', '=', $validasi->f02_validasi_id);
            })
            ->leftJoin('f02_skors as fs', function ($join) use ($validasi) {
                $join->on('fs.indikator_id', '=', 'i.id')
                    ->where('fs.periode_id', '=', $validasi->periode_id);
            })
            ->select(
                'i.id',
                'i.aspek_id',
                'i.kode',
                'i.nama',
                'i.urutan',
                'fiv.nilai',
                'fiv.catatan',
                'fs.skor_0',
                'fs.skor_1',
                'fs.skor_2',
                'fs.skor_3',
                'fs.skor_4',
                'fs.skor_5'
            )
            ->where('i.aktif', 1)
            ->whereRaw("LOWER(i.nama) NOT LIKE ?", ['test create %'])
            ->orderBy('i.urutan')
            ->orderBy('i.id')
            ->get()
            ->groupBy('aspek_id');

        $buktiByIndikator = DB::table('f01_bukti_dukung')
            ->select('indikator_id', 'url_bukti')
            ->where('f01_pengisian_id', $validasi->f01_pengisian_id)
            ->get()
            ->groupBy('indikator_id')
            ->map(function ($rows) {
                return $rows->pluck('url_bukti')->filter()->values()->all();
            });

        $currentRow = $headerRow + 1;
        $nomor = 1;
        $totalF02Computed = 0.0;
        $aspekHeaderRows = [];
        $subtotalRows = [];

        foreach ($aspeks as $aspek) {
            $aspekHeaderRows[] = $currentRow;
            $sheet->setCellValue('B' . $currentRow, sprintf('Aspek %s', $aspek->nama));
            $sheet->mergeCells('B' . $currentRow . ':F' . $currentRow);
            $currentRow++;

            $rows = collect($indikatorRows->get($aspek->id, []));
            $nilaiAspek = [];

            foreach ($rows as $row) {
                $nilai = is_null($row->nilai) ? '' : (int) $row->nilai;
                if ($nilai !== '') {
                    $nilaiAspek[] = (float) $nilai;
                }

                $narasiField = is_numeric($nilai) ? 'skor_' . (int) $nilai : null;
                $jawabanHasilValidasi = $narasiField ? ($row->{$narasiField} ?? '') : '';
                $buktiLinks = collect($buktiByIndikator->get($row->id, []))
                    ->map(function ($url) {
                        return wordwrap((string) $url, 42, "\n", true);
                    })
                    ->implode("\n");

                $sheet->fromArray([
                    $nomor++,
                    (string) ($row->nama ?? ''),
                    $jawabanHasilValidasi,
                    $nilai,
                    $buktiLinks,
                    $row->catatan ?? '',
                ], null, 'A' . $currentRow);
                $currentRow++;
            }

            $avgAspek = !empty($nilaiAspek) ? (array_sum($nilaiAspek) / count($nilaiAspek)) : 0;
            $bobot = (float) ($aspek->bobot ?? 0);
            $skorAspekFinal = ($avgAspek * $bobot) / 100;
            $totalF02Computed += $skorAspekFinal;

            $sheet->fromArray([
                '',
                sprintf('Subtotal Aspek %s', $aspek->nama),
                'Rata-rata skor aspek',
                round($avgAspek, 4),
                'Bobot ' . rtrim(rtrim((string) $bobot, '0'), '.') . '%',
                round($skorAspekFinal, 4),
            ], null, 'A' . $currentRow);
            $subtotalRows[] = $currentRow;
            $currentRow++;
        }

        $totalF02 = !is_null($validasi->total_nilai) ? (float) $validasi->total_nilai : $totalF02Computed;
        $kontribusiIpp = $totalF02 * 0.75;

        $totalF02Row = $currentRow;
        $sheet->fromArray(['', '', '', '', 'Total skor akhir F02', round($totalF02, 4)], null, 'A' . $currentRow);
        $currentRow++;
        $kontribusiRow = $currentRow;
        $sheet->fromArray(['', '', '', '', 'Kontribusi untuk IPP (75% x skor F02)', round($kontribusiIpp, 4)], null, 'A' . $currentRow);

        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
        ]);
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach ($aspekHeaderRows as $row) {
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '1E3A8A']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
            ]);
        }

        foreach ($subtotalRows as $row) {
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D1FAE5'],
                ],
            ]);
        }

        $sheet->getStyle('A' . $totalF02Row . ':F' . $totalF02Row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '7C2D12']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFEDD5'],
            ],
        ]);

        $sheet->getStyle('A' . $kontribusiRow . ':F' . $kontribusiRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '7C3AED']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EDE9FE'],
            ],
        ]);

        // Apply thin borders to table area for readability.
        $sheet->getStyle('A' . $headerRow . ':F' . $currentRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $sheet->getStyle('A' . ($headerRow + 1) . ':F' . $currentRow)
            ->getAlignment()
            ->setWrapText(true);
        $sheet->getStyle('A' . ($headerRow + 1) . ':F' . $currentRow)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('D' . ($headerRow + 1) . ':D' . $currentRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $currentRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $currentRow)
            ->getNumberFormat()
            ->setFormatCode('0.0000');
        $sheet->getStyle('D' . ($headerRow + 1) . ':D' . $currentRow)
            ->getNumberFormat()
            ->setFormatCode('0.0000');

        // Fixed widths keep layout stable and prevent one long cell from stretching the whole sheet.
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(52);
        $sheet->getColumnDimension('C')->setWidth(52);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(42);
        $sheet->getColumnDimension('F')->setWidth(34);

        $sheet->freezePane('A7');

        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        $content = (string) ob_get_clean();
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $content;
    }

    private function dispatchChartDataUpdated(): void
    {
        $this->dispatch('analytics-charts-updated', chartData: [
            'upp_id' => $this->upp_id,
            'f02_labels' => $this->f02_labels,
            'f02_data' => $this->f02_data,
            'f03_labels' => $this->f03_labels,
            'f03_data' => $this->f03_data,
            'ipp_labels' => $this->ipp_labels,
            'ipp_data' => $this->ipp_data,
            'aspek_ids' => $this->aspek_ids,
            'aspek_labels' => $this->aspek_labels,
            'aspek_values' => $this->aspek_values,
            'f03_aspek_ids' => $this->f03_aspek_ids,
            'f03_aspek_labels' => $this->f03_aspek_labels,
            'f03_aspek_values' => $this->f03_aspek_values,
            'summary_cards' => $this->summary_cards,
            'summary_card_details' => $this->summary_card_details,
        ]);
    }

    /**
     * Get UPP distribution in score buckets (0-5) for selected aspek.
     * Bucket is derived by rounding average skor aspek per UPP to nearest integer.
     */
    private function getAspekScoreDistribution(int $aspekId, int $periodeId): array
    {
        $bucketMethod = 'UPP belum submit dihitung skor 0, sedangkan yang sudah submit dibulatkan ke skor terdekat (0-5).';

        if (empty($aspekId) || empty($periodeId)) {
            return [
                'aspek_id' => $aspekId,
                'total_upp' => 0,
                'bucket_method' => $bucketMethod,
                'scores' => [],
            ];
        }

        $scoreLabels = [
            0 => 'Prioritas Pembinaan',
            1 => 'Kurang',
            2 => 'Cukup',
            3 => 'Baik Dengan Catatan',
            4 => 'Baik',
            5 => 'Istimewa',
        ];

        $scoreColors = [
            0 => '#A32D2D',
            1 => '#C43E3E',
            2 => '#D97706',
            3 => '#EAB308',
            4 => '#10B981',
            5 => '#185FA5',
        ];

        $scoreBuckets = [];
        foreach ([5, 4, 3, 2, 1, 0] as $score) {
            $scoreBuckets[$score] = [
                'skor' => $score,
                'predikat' => $scoreLabels[$score],
                'color' => $scoreColors[$score],
                'upp_count' => 0,
                'percentage' => 0.0,
                'upp_rows' => [],
            ];
        }

        $scopedUppIds = $this->getScopedUppIds();
        if (empty($scopedUppIds)) {
            return [
                'aspek_id' => $aspekId,
                'total_upp' => 0,
                'bucket_method' => $bucketMethod,
                'scores' => array_values($scoreBuckets),
            ];
        }

        $labelByUppId = collect($this->upp_options)
            ->pluck('label', 'id')
            ->mapWithKeys(fn($label, $id) => [(int) $id => (string) $label]);

        $uppNameById = DB::table('upps')
            ->whereIn('id', $scopedUppIds)
            ->pluck('nama', 'id')
            ->mapWithKeys(fn($name, $id) => [(int) $id => (string) $name]);

        $perUppRows = DB::table('f02_indikator_validasi as fiv')
            ->join('f02_validasi as fv', 'fiv.f02_validasi_id', '=', 'fv.id')
            ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
            ->join('indikator as ind', 'fiv.indikator_id', '=', 'ind.id')
            ->select(
                'fp.upp_id',
                DB::raw('AVG(COALESCE(fiv.nilai, 0)) as skor_aspek')
            )
            ->where('fp.is_latest_version', 1)
            ->where('fv.status', '!=', 'draft')
            ->where('ind.aspek_id', '=', $aspekId)
            ->where('fv.periode_id', '=', $periodeId)
            ->when(!empty($scopedUppIds), function ($query) use ($scopedUppIds) {
                $query->whereIn('fp.upp_id', $scopedUppIds);
            })
            ->groupBy('fp.upp_id')
            ->get();

        $scoreByUpp = $perUppRows
            ->mapWithKeys(function ($row) {
                return [(int) $row->upp_id => round((float) ($row->skor_aspek ?? 0), 4)];
            })
            ->all();

        foreach ($scopedUppIds as $uppId) {
            $uppId = (int) $uppId;
            $rawScore = (float) ($scoreByUpp[$uppId] ?? 0);
            $bucketScore = (int) round($rawScore, 0, PHP_ROUND_HALF_UP);
            $bucketScore = max(0, min(5, $bucketScore));

            $uppLabel = $labelByUppId->get($uppId, strtoupper((string) ($uppNameById->get($uppId, 'UPP-' . $uppId))));

            $scoreBuckets[$bucketScore]['upp_rows'][] = [
                'upp_id' => $uppId,
                'upp_label' => $uppLabel,
                'skor_aspek_raw' => $rawScore,
            ];
        }

        $totalUpp = count($scopedUppIds);

        foreach ([5, 4, 3, 2, 1, 0] as $score) {
            usort($scoreBuckets[$score]['upp_rows'], function ($left, $right) {
                $scoreComparison = $right['skor_aspek_raw'] <=> $left['skor_aspek_raw'];
                if ($scoreComparison !== 0) {
                    return $scoreComparison;
                }

                return strcmp((string) $left['upp_label'], (string) $right['upp_label']);
            });

            $count = count($scoreBuckets[$score]['upp_rows']);
            $scoreBuckets[$score]['upp_count'] = $count;
            $scoreBuckets[$score]['percentage'] = $totalUpp > 0
                ? round(($count / $totalUpp) * 100, 1)
                : 0.0;
        }

        return [
            'aspek_id' => $aspekId,
            'total_upp' => $totalUpp,
            'bucket_method' => $bucketMethod,
            'scores' => array_values($scoreBuckets),
        ];
    }

    /**
     * Get score distribution for an indikator with narasi and UPP breakdown
     * @param int $indikator_id
     * @param int $aspek_id
     * @param int $periode_id
     * @return array
     */
    private function getIndikatorScoreDistribution(int $indikator_id, int $aspek_id, int $periode_id): array
    {
        try {
            // Color palette untuk 6 skor (0-5)
            $scoreColors = [
                0 => '#A32D2D', // Red - Prioritas Pembinaan
                1 => '#C43E3E', // Dark Red
                2 => '#D97706', // Orange - Cukup
                3 => '#EAB308', // Amber - Cukup Dengan Catatan
                4 => '#10B981', // Green - Baik
                5 => '#185FA5'  // Blue - Istimewa
            ];

            // Map predikat label per skor
            $scorePredikat = [
                0 => 'Prioritas Pembinaan',
                1 => 'Kurang',
                2 => 'Cukup',
                3 => 'Baik Dengan Catatan',
                4 => 'Baik',
                5 => 'Istimewa'
            ];

            // Validate periode
            if (empty($periode_id)) {
                Log::warning('Empty periode_id in getIndikatorScoreDistribution');
                return [
                    'indikator_id' => $indikator_id,
                    'aspek_id' => $aspek_id,
                    'scores' => [],
                    'total_upp' => 0,
                    'colors' => $scoreColors,
                    'chart_data' => ['labels' => [], 'data' => [], 'backgroundColor' => []]
                ];
            }

            // Get F02 Skor narasi untuk semua score (0-5)
            $f02Skor = DB::table('f02_skors')
                ->where('indikator_id', $indikator_id)
                ->where('periode_id', $periode_id)
                ->first();

            Log::info('F02Skor lookup', [
                'indikator_id' => $indikator_id,
                'periode_id' => $periode_id,
                'found' => $f02Skor ? 'yes' : 'no'
            ]);

            // Get scoped UPP IDs (dari filter global)
            $scopedUppIds = $this->getScopedUppIds();

            Log::info('Scoped UPP IDs', [
                'count' => count($scopedUppIds),
                'ids' => $scopedUppIds
            ]);

            if (empty($scopedUppIds)) {
                return [
                    'indikator_id' => $indikator_id,
                    'aspek_id' => $aspek_id,
                    'scores' => [],
                    'total_upp' => 0,
                    'colors' => $scoreColors,
                    'chart_data' => ['labels' => [], 'data' => [], 'backgroundColor' => []]
                ];
            }

            $uppMetaById = DB::table('upps')
                ->whereIn('id', $scopedUppIds)
                ->select('id', 'kode', 'nama')
                ->get()
                ->keyBy('id');

            // Ambil skor per UPP dari hasil validasi F02 (bukan self-assessment F01).
            // Hanya validasi non-draft dan is_latest_version = 1 yang dihitung.
            // UPP yang tidak memiliki baris di sini (belum submit / belum validasi)
            // akan otomatis mendapat skor 0 via fallback `?? 0` di loop di bawah.
            $scoreByUpp = [];
            if (!empty($scopedUppIds)) {
                // Subquery: ambil f02_validasi_id terbaru per UPP untuk periode ini.
                // Tujuannya agar kalau ada lebih dari satu baris validasi per UPP
                // (misalnya karena revisi), hanya yang paling terakhir dipakai.
                $latestValidasiPerUpp = DB::table('f02_validasi as fv')
                    ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
                    ->select('fp.upp_id', DB::raw('MAX(fv.id) as latest_f02_validasi_id'))
                    ->where('fp.is_latest_version', 1)
                    ->where('fv.status', '!=', 'draft')
                    ->where('fv.periode_id', $periode_id)
                    ->whereIn('fp.upp_id', $scopedUppIds)
                    ->groupBy('fp.upp_id')
                    ->get()
                    ->keyBy('upp_id');

                $latestValidasiIds = $latestValidasiPerUpp
                    ->pluck('latest_f02_validasi_id')
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->all();

                if (!empty($latestValidasiIds)) {
                    $nilaiRows = DB::table('f02_indikator_validasi as fiv')
                        ->join('f02_validasi as fv', 'fiv.f02_validasi_id', '=', 'fv.id')
                        ->join('f01_pengisian as fp', 'fv.f01_pengisian_id', '=', 'fp.id')
                        ->selectRaw('fp.upp_id, CAST(fiv.nilai AS UNSIGNED) as skor')
                        ->whereIn('fiv.f02_validasi_id', $latestValidasiIds)
                        ->where('fiv.indikator_id', $indikator_id)
                        ->get();

                    foreach ($nilaiRows as $row) {
                        $uppId = (int) ($row->upp_id ?? 0);
                        $skor  = max(0, min(5, (int) ($row->skor ?? 0)));
                        if ($uppId > 0) {
                            $scoreByUpp[$uppId] = $skor;
                        }
                    }
                }
            }

            // Include all UPP in current scope; missing/unsent data is forced to score 0.
            $totalUpp = count($scopedUppIds);

            Log::info('Total UPP count', [
                'indikator_id' => $indikator_id,
                'total_upp' => $totalUpp
            ]);

            // Build scores array dengan narasi dan UPP list
            $scores = [];
            $chartLabels = [];
            $chartData = [];
            $chartColors = [];
            $uppByScore = [
                5 => [],
                4 => [],
                3 => [],
                2 => [],
                1 => [],
                0 => [],
            ];

            $labelByUppId = collect($this->upp_options)->pluck('label', 'id');

            foreach ($scopedUppIds as $uppId) {
                $uppId = (int) $uppId;
                $skor = (int) ($scoreByUpp[$uppId] ?? 0);

                $uppMeta = $uppMetaById->get($uppId);
                $uppByScore[$skor][] = (object) [
                    'upp_id' => $uppId,
                    'upp_nama' => $labelByUppId->get($uppId, strtoupper((string) ($uppMeta->nama ?? ('UPP-' . $uppId)))),
                    'upp_kode' => (string) ($uppMeta->kode ?? ''),
                ];
            }

            foreach ([5, 4, 3, 2, 1, 0] as $skor) {
                $uppList = $uppByScore[$skor] ?? [];
                usort($uppList, function ($left, $right) {
                    return strcmp((string) ($left->upp_nama ?? ''), (string) ($right->upp_nama ?? ''));
                });

                $count = count($uppList);
                $percentage = $totalUpp > 0 ? ($count / $totalUpp) * 100 : 0;

                // Get narasi dari F02Skor
                $narasiField = 'skor_' . $skor;
                $narasi = $f02Skor?->{$narasiField} ?? '';

                Log::info("Score {$skor} processing", [
                    'upp_count' => $count,
                    'narasi' => substr($narasi, 0, 50),
                    'upp_list_count' => count($uppList)
                ]);

                $scores[] = [
                    'skor' => $skor,
                    'narasi' => $narasi,
                    'predikat' => $scorePredikat[$skor] ?? 'Tidak Diketahui',
                    'upp_count' => $count,
                    'percentage' => round($percentage, 1),
                    'upp_list' => $uppList,
                    'color' => $scoreColors[$skor]
                ];

                if ($count > 0) {
                    $chartLabels[] = $scorePredikat[$skor];
                    $chartData[] = $count;
                    $chartColors[] = $scoreColors[$skor];
                }
            }

            Log::info('Final scores array', [
                'indikator_id' => $indikator_id,
                'scores_count' => count($scores),
                'chart_labels_count' => count($chartLabels),
                'chart_data_count' => count($chartData),
                'chart_labels' => $chartLabels,
                'chart_data' => $chartData,
                'chart_colors' => $chartColors,
                'total_upp' => $totalUpp
            ]);

            return [
                'indikator_id' => $indikator_id,
                'aspek_id' => $aspek_id,
                'scores' => $scores,
                'total_upp' => $totalUpp,
                'colors' => $scoreColors,
                'chart_data' => [
                    'labels' => $chartLabels,
                    'data' => $chartData,
                    'backgroundColor' => $chartColors
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error in getIndikatorScoreDistribution', ['error' => $e->getMessage()]);
            return [
                'indikator_id' => $indikator_id,
                'aspek_id' => $aspek_id,
                'scores' => [],
                'total_upp' => 0,
                'colors' => [
                    0 => '#A32D2D',
                    1 => '#C43E3E',
                    2 => '#D97706',
                    3 => '#EAB308',
                    4 => '#10B981',
                    5 => '#185FA5',
                ],
                'chart_data' => ['labels' => [], 'data' => [], 'backgroundColor' => []]
            ];
        }
    }

    /**
     * Show indicator detail modal
     */
    public function showIndikatorDetail($indikator_id)
    {
        try {
            $indikator_id = (int) $indikator_id;

            // Get indikator info
            $indikator = DB::table('indikator')
                ->where('id', $indikator_id)
                ->first(['id', 'nama', 'kode', 'aspek_id']);

            if (!$indikator) {
                Log::warning('Indikator not found', ['indikator_id' => $indikator_id]);
                return;
            }

            // Validate periode_id - if not set, get active periode
            $periodeId = (int) $this->periode_id;
            if (empty($periodeId)) {
                $activePeriode = DB::table('periode')->where('is_aktif', 1)->orderByDesc('tahun')->first(['id']);
                $periodeId = $activePeriode?->id ?? null;

                if (!$periodeId) {
                    Log::warning('No active periode found');
                    return;
                }
            }

            Log::info('showIndikatorDetail', [
                'indikator_id' => $indikator_id,
                'periode_id' => $periodeId,
                'upp_filter' => $this->upp_id,
                'scoped_upp_ids' => $this->getScopedUppIds()
            ]);

            // Get distribution data
            $distribution = $this->getIndikatorScoreDistribution(
                $indikator_id,
                (int) $indikator->aspek_id,
                $periodeId
            );

            // Get aspek info
            $aspek = DB::table('aspek')
                ->where('id', (int) $indikator->aspek_id)
                ->first(['id', 'nama']);

            $this->indicator_detail = [
                'indikator' => [
                    'id' => $indikator->id,
                    'nama' => $indikator->nama,
                    'kode' => $indikator->kode
                ],
                'aspek' => [
                    'id' => $aspek->id,
                    'nama' => $aspek->nama
                ],
                'distribution' => $distribution,
                'periode_id' => $this->periode_id
            ];

            $this->indicator_detail_visible = true;
            $this->selected_score_for_upp = null;

            // Dispatch to initialize chart after render
            $this->dispatch('initChart');

        } catch (\Exception $e) {
            Log::error('Error showing indicator detail', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Select score to show UPP list
     */
    public function selectScoreForUpp($skor)
    {
        $this->selected_score_for_upp = (int) $skor;
    }

    /**
     * Close indicator detail modal
     */
    public function closeIndicatorDetail()
    {
        $this->indicator_detail = null;
        $this->indicator_detail_visible = false;
        $this->selected_score_for_upp = null;
    }

    /**
     * Show aspek detail modal with UPP distribution for score 0-5.
     */
    public function showAspekDetail($aspekId)
    {
        try {
            $aspekId = (int) $aspekId;

            if ($aspekId <= 0) {
                return;
            }

            $aspek = DB::table('aspek')
                ->where('id', $aspekId)
                ->first(['id', 'nama', 'bobot']);

            if (!$aspek) {
                Log::warning('Aspek not found for detail modal', ['aspek_id' => $aspekId]);
                return;
            }

            $periodeId = (int) $this->periode_id;
            if (empty($periodeId)) {
                $activePeriode = DB::table('periode')
                    ->where('is_aktif', 1)
                    ->orderByDesc('tahun')
                    ->first(['id']);

                $periodeId = $activePeriode?->id ?? null;
            }

            if (empty($periodeId)) {
                Log::warning('No active periode available for aspek detail modal', ['aspek_id' => $aspekId]);
                return;
            }

            $distribution = $this->getAspekScoreDistribution($aspekId, (int) $periodeId);

            $this->aspek_detail = [
                'aspek' => [
                    'id' => (int) $aspek->id,
                    'nama' => (string) $aspek->nama,
                    'bobot' => (float) ($aspek->bobot ?? 0),
                ],
                'distribution' => $distribution,
                'periode_id' => (int) $periodeId,
            ];

            $this->aspek_detail_visible = true;
            $this->selectAspek($aspekId);
        } catch (\Exception $e) {
            Log::error('Error showing aspek detail', [
                'aspek_id' => $aspekId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Close aspek detail modal.
     */
    public function closeAspekDetail()
    {
        $this->aspek_detail = null;
        $this->aspek_detail_visible = false;
    }

    public function selectAspek($aspekId)
    {
        $aspekId = (int) $aspekId;
        $validTabIds = collect($this->aspek_tabs)->pluck('id')->all();

        if (in_array($aspekId, $validTabIds, true)) {
            $this->selected_aspek_id = $aspekId;
        }
    }

    public function render()
    {
        return view('livewire.analytics.panel', [
            'aspek_ids' => $this->aspek_ids,
            'f03_aspek_ids' => $this->f03_aspek_ids,
            'f02_labels' => $this->f02_labels,
            'f02_data' => $this->f02_data,
            'f03_labels' => $this->f03_labels,
            'f03_data' => $this->f03_data,
            'f03_indikator_ids' => $this->f03_indikator_ids,
            'f03_indikator_labels' => $this->f03_indikator_labels,
            'f03_indikator_values' => $this->f03_indikator_values,
            'f03_indikator_ids' => $this->f03_indikator_ids,
            'f03_indikator_labels' => $this->f03_indikator_labels,
            'f03_indikator_values' => $this->f03_indikator_values,
            'f03_aspek_labels' => $this->f03_aspek_labels,
            'f03_aspek_values' => $this->f03_aspek_values,
            'ipp_labels' => $this->ipp_labels,
            'ipp_data' => $this->ipp_data,
            'aspek_chart_data' => $this->aspek_chart_data,
            'aspek_tabs' => $this->aspek_tabs,
            'aspek_indikator_scores' => $this->aspek_indikator_scores,
            'selected_aspek_id' => $this->selected_aspek_id,
            'selected_aspek_rows' => $this->selected_aspek_id
                ? ($this->aspek_indikator_scores[$this->selected_aspek_id] ?? [])
                : [],
            'summary_cards' => $this->summary_cards,
            'summary_card_details' => $this->summary_card_details,
            'periode_options' => $this->periode_options,
            'upp_options' => $this->upp_options,
            'indicator_detail' => $this->indicator_detail,
            'indicator_detail_visible' => $this->indicator_detail_visible,
            'selected_score_for_upp' => $this->selected_score_for_upp,
            'aspek_detail' => $this->aspek_detail,
            'aspek_detail_visible' => $this->aspek_detail_visible,
        ]);
    }
}
