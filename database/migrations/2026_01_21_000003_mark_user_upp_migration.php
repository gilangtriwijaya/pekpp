<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) add hasil_fallback column if not exists
        if (! Schema::hasColumn('user_upp', 'hasil_fallback')) {
            Schema::table('user_upp', function (Blueprint $table) {
                $table->boolean('hasil_fallback')->default(false)->after('peran');
            });
        }

        // 2) determine superadmin setter id
        $superadmin = DB::table('users')->where('role_sso', 'like', '%superadmin%')->orWhere('sso_user_id', 1)->first();
        $setterId = $superadmin->id ?? DB::table('users')->value('id');

        // 3) set ditetapkan_oleh and ditetapkan_pada for all rows if null
        DB::table('user_upp')->whereNull('ditetapkan_oleh')->update([
            'ditetapkan_oleh' => $setterId,
            'ditetapkan_pada' => now(),
        ]);

        // 4) Compute hasil_fallback: for each user_upp row, check if there exists a
        //    row in user_unit_roles where user_id matches and opd_unit_id maps to
        //    upp via opd_units.sso_id -> upps.unit_opd_id_sso. If matched, hasil_fallback=false.
        //    If only mapping was via fallback (opd_unit_id == upp.id), mark true.

        $rows = DB::table('user_upp')->select('id','user_id','upp_id')->get();
        foreach ($rows as $r) {
            $isFallback = true;

            // find user_unit_roles entries for this user
            $candidates = DB::table('user_unit_roles')->where('user_id', $r->user_id)->get();
            foreach ($candidates as $c) {
                // try mapping via opd_units.sso_id -> upps.unit_opd_id_sso
                $unit = DB::table('opd_units')->where('id', $c->opd_unit_id)->first();
                if ($unit && isset($unit->sso_id)) {
                    $upp = DB::table('upps')->where('unit_opd_id_sso', $unit->sso_id)->first();
                    if ($upp && $upp->id == $r->upp_id) {
                        $isFallback = false;
                        break;
                    }
                }
                // fallback candidate: opd_unit_id equals upp id
                if ($c->opd_unit_id == $r->upp_id) {
                    $isFallback = true;
                    break;
                }
            }

            DB::table('user_upp')->where('id', $r->id)->update(['hasil_fallback' => $isFallback ? 1 : 0]);
        }

        // 5) Ensure ditetapkan_oleh/didetapkan_pada exist for active rows (application should enforce too)
        DB::table('user_upp')->where('aktif', 1)->whereNull('ditetapkan_oleh')->update([
            'ditetapkan_oleh' => $setterId,
            'ditetapkan_pada' => now(),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_upp', 'hasil_fallback')) {
            Schema::table('user_upp', function (Blueprint $table) {
                $table->dropColumn('hasil_fallback');
            });
        }
    }
};
