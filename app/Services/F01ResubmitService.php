<?php

namespace App\Services;

use App\Models\F01Pengisian;
use App\Models\F01Jawaban;
use App\Models\F01IndikatorNilai;
use App\Models\F01IndikatorBukti;
use App\Models\F01BuktiDukung;
use App\Models\F02Validasi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class F01ResubmitService
{
    /**
     * Allow UPP to resubmit - create NEW F01Pengisian version
     *
     * @param F02Validasi $f02
     * @param User $admin
     * @param array $metadata Optional metadata (reason, notes, etc)
     * @return F01Pengisian new pending version
     *
     * @throws \Exception if F02 status != selesai
     */
    public function allowResubmit(F02Validasi $f02, User $admin, array $metadata = [])
    {
        // Validate: F02 must be selesai
        if ($f02->status !== 'selesai') {
            throw new \Exception(
                "Cannot allow resubmit: F02 status is {$f02->status}, expected 'selesai'"
            );
        }

        $f01Old = $f02->f01pengisian;

        // Validate: F01 must be selesai
        if ($f01Old->status !== 'selesai') {
            throw new \Exception(
                "Cannot allow resubmit: F01 status is {$f01Old->status}, expected 'selesai'"
            );
        }

        return DB::transaction(function () use ($f01Old, $metadata, $admin) {
            // 1. Mark old version as not latest
            $f01Old->update(['is_latest_version' => false]);

            // 2. Create new F01Pengisian (v+1)
            $f01New = F01Pengisian::create([
                'periode_id' => $f01Old->periode_id,
                'upp_id' => $f01Old->upp_id,
                'status' => 'draft', // KEY: reset to draft
                'version_number' => $f01Old->version_number + 1,
                'previous_f01_pengisian_id' => $f01Old->id,
                'is_latest_version' => true,
                'dikirim_oleh' => $admin->id, // Admin yang allow
                'catatan_umum' => $f01Old->catatan_umum ?? $metadata['catatan'] ?? null,
            ]);

            // 2b. Copy F01 indicator summary scores & notes (untuk referensi di form)
            $oldIndikatorScores = F01IndikatorNilai::where('f01_pengisian_id', $f01Old->id)->get();
            foreach ($oldIndikatorScores as $item) {
                $newIndikator = F01IndikatorNilai::create([
                    'f01_pengisian_id' => $f01New->id,
                    'indikator_id' => $item->indikator_id,
                    'nilai' => $item->nilai,
                    'justifikasi' => $item->justifikasi,
                    'status' => $item->status,
                ]);

                // Copy bukti if exists
                $oldBukti = $item->bukti;
                if ($oldBukti && $oldBukti->count() > 0) {
                    foreach ($oldBukti as $bukti) {
                        F01IndikatorBukti::create([
                            'f01_indikator_nilai_id' => $newIndikator->id,
                            'jenis' => $bukti->jenis,
                            'nama' => $bukti->nama,
                            'path_atau_url' => $bukti->path_atau_url,
                            'keterangan' => $bukti->keterangan,
                        ]);
                    }
                }
            }

            // 3. Copy all f01_jawaban dari v1 → v2 (untuk prefill)
            $oldJawaban = F01Jawaban::where('f01_pengisian_id', $f01Old->id)->get();

            foreach ($oldJawaban as $jawaban) {
                F01Jawaban::create([
                    'f01_pengisian_id' => $f01New->id,
                    'pertanyaan_id' => $jawaban->pertanyaan_id,
                    'nilai' => $jawaban->nilai, // Copy nilai
                ]);
            }

            // 3b. Copy all f01_bukti_dukung dari v1 → v2
            $oldBuktiDukung = \App\Models\F01BuktiDukung::where('f01_pengisian_id', $f01Old->id)->get();

            foreach ($oldBuktiDukung as $bukti) {
                \App\Models\F01BuktiDukung::create([
                    'f01_pengisian_id' => $f01New->id,
                    'indikator_id' => $bukti->indikator_id,
                    'url_bukti' => $bukti->url_bukti,
                ]);
            }

            // 4. Log this action (untuk audit)
            if (function_exists('activity')) {
                activity()
                    ->performedBy($admin)
                    ->on($f01New)
                    ->event('resubmit_allowed')
                    ->withProperties([
                        'from_version' => $f01Old->version_number,
                        'to_version' => $f01New->version_number,
                        'f02_id' => $f01Old->f02?->id,
                        'upp_id' => $f01Old->upp_id,
                    ])
                    ->log('UPP allowed to resubmit');
            }

            return $f01New; // Return NEW pengisian (belum submit, status=draft)
        });
    }

    /**
     * Bulk allow resubmit untuk multiple F02
     *
     * @param array $f02Ids
     * @param User $admin
     * @return array count success/failed
     */
    public function bulkAllowResubmit(array $f02Ids, User $admin)
    {
        $success = 0;
        $failed = [];

        foreach ($f02Ids as $f02Id) {
            try {
                $f02 = F02Validasi::findOrFail($f02Id);
                $this->allowResubmit($f02, $admin);
                $success++;
            } catch (\Exception $e) {
                $failed[$f02Id] = $e->getMessage();
            }
        }

        return [
            'success' => $success,
            'failed_count' => count($failed),
            'failed_details' => $failed,
        ];
    }

    /**
     * Get previous F02 validasi data untuk display di F01 form
     *
     * @param F01Pengisian $f01New
     * @return F02Validasi|null previous F02 (dari v sebelumnya)
     */
    public function getPreviousF02Data(F01Pengisian $f01New)
    {
        if (!$f01New->previous_f01_pengisian_id) {
            // Ini v1, tidak ada previous
            return null;
        }

        $f01Previous = $f01New->previousVersion;

        // Get F02 yang link ke previous version
        return F02Validasi::where('f01_pengisian_id', $f01Previous->id)->first();
    }

    public function autoCreateF02(F01Pengisian $f01)
    {
        $f02 = F02Validasi::firstOrCreate([
            'f01_pengisian_id' => $f01->id,
        ], [
            'periode_id' => $f01->periode_id,
            'status' => 'draft',
            'catatan_umum' => null,
            'total_nilai' => null,
            'nilai_mentah' => null,
        ]);

        // Carry-over logic: only run if F02 is freshly created (no indikators yet)
        if ($f02->wasRecentlyCreated || $f02->indikatorValidasi()->count() === 0) {
            $previousF02 = $this->getPreviousF02Data($f01);

            if ($previousF02) {
                // Get all indikator changes status for this F01
                $f01Indikators = \App\Models\F01IndikatorNilai::where('f01_pengisian_id', $f01->id)
                    ->get()
                    ->keyBy('indikator_id');

                // Get ALL previous validasi scores
                $previousScores = \App\Models\F02IndikatorValidasi::where('f02_validasi_id', $previousF02->id)->get();

                foreach ($previousScores as $prevScore) {
                    $indId = $prevScore->indikator_id;
                    $isChanged = false;

                    if (isset($f01Indikators[$indId])) {
                        $isChanged = (bool) $f01Indikators[$indId]->is_changed;
                    }

                    // Insert to new F02, avoiding duplicates just in case
                    \App\Models\F02IndikatorValidasi::firstOrCreate([
                        'f02_validasi_id' => $f02->id,
                        'indikator_id'    => $indId,
                    ], [
                        'nilai'          => $isChanged ? null : $prevScore->nilai, // Reset score if changed
                        'catatan'        => $prevScore->catatan, // ALWAYS keep previous notes
                        'status'         => $isChanged ? 'draft' : $prevScore->status,
                        'is_carried_over' => !$isChanged,
                    ]);
                }
            }
        }

        return $f02;
    }
}
