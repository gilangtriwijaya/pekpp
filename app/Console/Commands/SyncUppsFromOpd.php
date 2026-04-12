<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Opd;
use App\Models\OpdUnit;
use App\Models\Upp;
use Illuminate\Support\Facades\DB;

class SyncUppsFromOpd extends Command
{
    protected $signature = 'upp:sync-from-opd';
    protected $description = 'Sinkronkan tabel `upps` dari data `opds` dan `opd_units` (SSO mirror)';

    public function handle(): int
    {
        $this->info('Starting UPP sync from OPD/opd_units');

        DB::transaction(function () {
            // First ensure OPD-level UPPs
            Opd::orderBy('id')->chunk(100, function ($opds) {
                foreach ($opds as $opd) {
                    $ssoOpdId = $opd->sso_id ?? null;

                    $upp = Upp::updateOrCreate(
                        ['opd_id_sso' => $ssoOpdId],
                        [
                            'unit_opd_id_sso' => null,
                            'kode' => 'OPD-' . $ssoOpdId,
                            'nama' => $opd->nama,
                            'jenis' => 'opd',
                            'aktif' => 1,
                            'parent_upp_id' => null,
                        ]
                    );

                    // then ensure units under this OPD
                    OpdUnit::where('opd_id', $opd->id)->orderBy('id')->get()->each(function ($unit) use ($upp) {
                        $ssoUnitId = $unit->sso_id ?? null;
                        Upp::updateOrCreate(
                            ['unit_opd_id_sso' => $ssoUnitId],
                            [
                                'opd_id_sso' => $unit->opd ? $unit->opd->sso_id ?? null : null,
                                'kode' => 'UNIT-' . $ssoUnitId,
                                'nama' => $unit->nama,
                                'jenis' => 'unit',
                                'aktif' => 1,
                                'parent_upp_id' => $upp->id,
                            ]
                        );
                    });
                }
            });
        });

        $this->info('UPP sync completed');
        return 0;
    }
}
