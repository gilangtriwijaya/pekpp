<?php

namespace App\Services;

use App\Models\F01Pengisian;
use App\Models\F01IndikatorNilai;
use App\Models\F01Jawaban;
use App\Models\Indikator;
use Illuminate\Support\Facades\DB;

class F01ScoringService
{
    public function finalizePengisian(F01Pengisian $pengisian): void
    {
        if ($pengisian->status !== 'draft') {
            throw new \LogicException('Pengisian bukan draft');
        }

        DB::transaction(function () use ($pengisian) {
            foreach ($this->indikatorList($pengisian) as $indikator) {
                $this->scoreIndikator($pengisian, $indikator);
            }
        });
    }

    protected function indikatorList(F01Pengisian $pengisian)
    {
        // periode is source of truth
        return $pengisian->periode
            ->indikator()
            ->with('pertanyaan')
            ->get();
    }

    protected function scoreIndikator(F01Pengisian $pengisian, $indikator): void
    {
        $nilaiPertanyaan = [];

        foreach ($indikator->pertanyaan as $pertanyaan) {
            $nilaiPertanyaan[] = $this->scorePertanyaan($pengisian, $pertanyaan);
        }

        $final = $this->aggregate($nilaiPertanyaan);

        F01IndikatorNilai::updateOrCreate(
            [
                'f01_pengisian_id' => $pengisian->id,
                'indikator_id' => $indikator->id,
            ],
            [
                'nilai' => $final,
                'justifikasi' => $this->buildJustifikasi($nilaiPertanyaan),
                'status' => 'final',
            ]
        );
    }

    protected function scorePertanyaan(F01Pengisian $pengisian, $pertanyaan): float
    {
        $jawaban = F01Jawaban::where('f01_pengisian_id', $pengisian->id)
            ->where('pertanyaan_id', $pertanyaan->id)
            ->first();

        if (!$jawaban || empty($jawaban->nilai)) {
            return 0.0;
        }

        $tipe = method_exists($pertanyaan, 'tipe') ? $pertanyaan->tipe() : ($pertanyaan->tipe ?? 'text');

        return match ($tipe) {
            'text' => $this->scoreText($jawaban),
            'radio' => $this->scoreRadio($jawaban, $pertanyaan),
            'checkbox' => $this->scoreCheckbox($jawaban, $pertanyaan),
            'skala' => $this->scoreSkala($jawaban),
            default => 0.0,
        };
    }

    protected function scoreText($jawaban): float
    {
        return trim((string) $jawaban->nilai) !== '' ? 1.0 : 0.0;
    }

    protected function scoreRadio($jawaban, $pertanyaan): float
    {
        $map = method_exists($pertanyaan, 'opsi') ? $pertanyaan->opsi() : ($pertanyaan->opsi ?? []);
        $val = is_array($jawaban->nilai) ? ($jawaban->nilai[0] ?? null) : $jawaban->nilai;
        return isset($map[$val]) ? (float) $map[$val] : 0.0;
    }

    protected function scoreCheckbox($jawaban, $pertanyaan): float
    {
        $checked = (array) $jawaban->nilai;
        // attempt to get total options
        $opts = [];
        if (method_exists($pertanyaan, 'opsi')) {
            $opts = $pertanyaan->opsi_jawaban ?? [];
        } elseif (isset($pertanyaan->opsi)) {
            $opts = $pertanyaan->opsi;
        }

        $total = is_array($opts) ? count($opts) : 0;
        if ($total === 0) return 0.0;

        return round(count($checked) / $total, 4);
    }

    protected function scoreSkala($jawaban): float
    {
        return (float) $jawaban->nilai;
    }

    protected function aggregate(array $values): float
    {
        if (count($values) === 0) return 0.0;
        return round(array_sum($values) / count($values), 2);
    }

    protected function buildJustifikasi(array $values): string
    {
        $filled = collect($values)->filter(fn($v) => $v > 0)->count();
        $total = count($values);

        return "Terisi {$filled} dari {$total} pertanyaan.";
    }
}

