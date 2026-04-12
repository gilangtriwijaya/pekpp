<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_upp', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('upp_id')
                  ->constrained('upps')
                  ->cascadeOnDelete();

            $table->enum('peran', ['superadmin','admin_organisasi','admin_upp','verifikator']);

            $table->boolean('aktif')->default(true);

            $table->foreignId('ditetapkan_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('ditetapkan_pada')->nullable();

            $table->timestamps();

            $table->unique(['user_id','upp_id','peran']);
            $table->index('user_id');
            $table->index('upp_id');
        });

        // Migrate existing data from user_unit_roles
        // Mapping strategy:
        // - find matching upp by mapping opd_unit_id -> opd_units.sso_id -> upps.unit_opd_id_sso
        // - fallback: if upp with id == opd_unit_id exists, use it
        // - map role strings to locked enum values; skip unknown roles

        $mapRole = function ($roleString) {
            if (! $roleString) return null;
            $r = strtolower(trim($roleString));
            // common mappings
            if (strpos($r, 'super') !== false) return 'superadmin';
            if (strpos($r, 'organisasi') !== false || strpos($r, 'admin_org') !== false || strpos($r, 'adminorganisasi') !== false || strpos($r, 'admin_opd') !== false) return 'admin_organisasi';
            if (strpos($r, 'admin_upp') !== false || strpos($r, 'admin upp') !== false || strpos($r, 'adminupp') !== false || strpos($r, 'admin') === 0) return 'admin_upp';
            if (strpos($r, 'verifik') !== false || strpos($r, 'verifier') !== false) return 'verifikator';
            return null; // unknown
        };

        DB::table('user_unit_roles')->orderBy('id')->chunkById(200, function ($rows) use ($mapRole) {
            foreach ($rows as $row) {
                $userId = $row->user_id;
                $opdUnitId = $row->opd_unit_id;
                $roleStr = $row->role;

                $peran = $mapRole($roleStr);
                if (! $peran) {
                    // skip unknown role strings to avoid invalid enum
                    continue;
                }

                // try map opd_unit_id -> opd_units.sso_id -> upps.unit_opd_id_sso
                $upp = null;
                if ($opdUnitId) {
                    $unit = DB::table('opd_units')->where('id', $opdUnitId)->first();
                    if ($unit && isset($unit->sso_id)) {
                        $upp = DB::table('upps')->where('unit_opd_id_sso', $unit->sso_id)->first();
                    }
                }

                // fallback: maybe opd_unit_id already stores upp id
                if (! $upp) {
                    $maybe = DB::table('upps')->where('id', $opdUnitId)->first();
                    if ($maybe) $upp = $maybe;
                }

                if (! $upp) {
                    // cannot map; skip and leave manual reconciliation
                    continue;
                }

                // insert if not exists
                try {
                    DB::table('user_upp')->insert([
                        'user_id' => $userId,
                        'upp_id' => $upp->id,
                        'peran' => $peran,
                        'aktif' => true,
                        'ditetapkan_oleh' => null,
                        'ditetapkan_pada' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // likely duplicate unique constraint; skip
                    continue;
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_upp');
    }
};
