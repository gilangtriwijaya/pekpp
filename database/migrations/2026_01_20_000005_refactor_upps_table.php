<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Add new columns (nullable initially) — guard if already present
        if (! Schema::hasColumn('upps', 'kode')) {
            Schema::table('upps', function (Blueprint $table) {
                $table->string('kode', 50)->nullable()->after('id');
            });
        }
        if (! Schema::hasColumn('upps', 'jenis')) {
            Schema::table('upps', function (Blueprint $table) {
                $table->enum('jenis', ['opd', 'unit'])->nullable()->after('nama');
            });
        }
        if (! Schema::hasColumn('upps', 'opd_id_sso')) {
            Schema::table('upps', function (Blueprint $table) {
                $table->unsignedBigInteger('opd_id_sso')->nullable()->after('parent_upp_id');
            });
        }
        if (! Schema::hasColumn('upps', 'unit_opd_id_sso')) {
            Schema::table('upps', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_opd_id_sso')->nullable()->after('opd_id_sso');
            });
        }
        if (! Schema::hasColumn('upps', 'aktif')) {
            Schema::table('upps', function (Blueprint $table) {
                $table->boolean('aktif')->default(true)->after('unit_opd_id_sso');
            });
        }

        // 2) Migrate data from old columns into new columns
        //    - kode: prefer existing `code`, fallback to UPP-{id}
        //    - opd_id_sso <- sso_opd_id
        //    - unit_opd_id_sso <- sso_opd_unit_id
        //    - aktif: status == 'AKTIF'
        //    - jenis: if parent_upp_id NOT NULL or sso_opd_unit_id NOT NULL => 'unit' else 'opd'

        $used = [];
        DB::table('upps')->orderBy('id')->chunkById(200, function ($rows) use (&$used) {
            foreach ($rows as $r) {
                $base = (isset($r->code) && $r->code) ? $r->code : ('UPP-' . $r->id);
                $base = preg_replace('/\s+/', '_', $base);
                $kode = Str::limit($base, 50, '');
                if (isset($used[$kode])) {
                    // make unique by appending id (ensure <=50)
                    $suffix = '-'. $r->id;
                    $max = 50 - strlen($suffix);
                    $kode = Str::substr($kode, 0, $max) . $suffix;
                }
                $used[$kode] = true;

                $jenis = 'opd';
                if (! empty($r->parent_upp_id) || (isset($r->sso_opd_unit_id) && ! empty($r->sso_opd_unit_id))) {
                    $jenis = 'unit';
                }

                DB::table('upps')->where('id', $r->id)->update([
                    'kode' => $kode,
                    'jenis' => $jenis,
                    'opd_id_sso' => (isset($r->sso_opd_id) ? $r->sso_opd_id : null),
                    'unit_opd_id_sso' => (isset($r->sso_opd_unit_id) ? $r->sso_opd_unit_id : null),
                    'aktif' => (isset($r->status) && $r->status === 'AKTIF') ? 1 : 0,
                ]);
            }
        });

        // 3) Make columns NOT NULL where required and add constraints
        // Use raw SQL MODIFY to avoid requiring doctrine/dbal
        // Skip raw ALTER MODIFY on SQLite (in-memory testing) which doesn't support it
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE upps MODIFY COLUMN kode varchar(50) NOT NULL");
            DB::statement("ALTER TABLE upps MODIFY COLUMN jenis ENUM('opd','unit') NOT NULL");
            DB::statement("ALTER TABLE upps MODIFY COLUMN aktif tinyint(1) NOT NULL DEFAULT 1");
        }

        // Add unique constraints (safe: ignore if already exists)
        try {
            Schema::table('upps', function (Blueprint $table) {
                $table->unique('kode');
                $table->unique('unit_opd_id_sso');
            });
        } catch (\Exception $e) {
            // index might already exist or be created in a previous run — ignore
        }

        // 4) Drop non-evaluation / SSO duplicated columns we don't want in final model
        // Attempt to drop legacy/non-eval columns; wrap in try/catch to allow
        // this migration to be idempotent if it was partially applied earlier.
        try {
            Schema::table('upps', function (Blueprint $table) {
                // keep sso ids moved to new columns; drop old ones if present
                if (Schema::hasColumn('upps', 'code')) {
                    $table->dropColumn('code');
                }
                if (Schema::hasColumn('upps', 'sso_opd_id')) {
                    $table->dropColumn('sso_opd_id');
                }
                if (Schema::hasColumn('upps', 'sso_opd_unit_id')) {
                    $table->dropColumn('sso_opd_unit_id');
                }

                // drop org detail & user columns as per design (not evaluation attributes)
                if (Schema::hasColumn('upps', 'slug')) {
                    $table->dropColumn('slug');
                }
                if (Schema::hasColumn('upps', 'alamat')) {
                    $table->dropColumn('alamat');
                }
                if (Schema::hasColumn('upps', 'telepon')) {
                    $table->dropColumn('telepon');
                }
                if (Schema::hasColumn('upps', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('upps', 'created_by')) {
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn('upps', 'updated_by')) {
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn('upps', 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        } catch (\Exception $e) {
            // ignore drop failures (indexes/constraints may block drops on some runs)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate dropped columns (best-effort) and move data back
        Schema::table('upps', function (Blueprint $table) {
            // restore some old columns
            $table->string('code')->nullable()->after('id');
            $table->unsignedBigInteger('sso_opd_id')->nullable()->after('parent_upp_id');
            $table->unsignedBigInteger('sso_opd_unit_id')->nullable()->after('sso_opd_id');

            $table->string('slug')->nullable()->after('nama');
            $table->text('alamat')->nullable()->after('parent_upp_id');
            $table->string('telepon')->nullable()->after('alamat');
            $table->enum('status', ['AKTIF','NONAKTIF'])->default('AKTIF')->after('telepon');
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
        });

        // Move data back where possible
        DB::table('upps')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                DB::table('upps')->where('id', $r->id)->update([
                    'code' => $r->kode,
                    'sso_opd_id' => $r->opd_id_sso,
                    'sso_opd_unit_id' => $r->unit_opd_id_sso,
                    'status' => ($r->aktif) ? 'AKTIF' : 'NONAKTIF',
                ]);
            }
        });

        // Drop new constraints and columns
        Schema::table('upps', function (Blueprint $table) {
            $table->dropUnique(['kode']);
            $table->dropUnique(['unit_opd_id_sso']);
        });

        Schema::table('upps', function (Blueprint $table) {
            if (Schema::hasColumn('upps', 'kode')) {
                $table->dropColumn('kode');
            }
            if (Schema::hasColumn('upps', 'jenis')) {
                $table->dropColumn('jenis');
            }
            if (Schema::hasColumn('upps', 'opd_id_sso')) {
                $table->dropColumn('opd_id_sso');
            }
            if (Schema::hasColumn('upps', 'unit_opd_id_sso')) {
                $table->dropColumn('unit_opd_id_sso');
            }
            if (Schema::hasColumn('upps', 'aktif')) {
                $table->dropColumn('aktif');
            }
        });
    }
};
