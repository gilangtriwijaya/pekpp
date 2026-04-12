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
        // Drop composite unique index referencing legacy sso columns if exists
        try {
            DB::statement("ALTER TABLE upps DROP INDEX `uq_upps_sso_opd_unit`");
        } catch (\Exception $e) {
            // ignore if not exists
        }

        // Drop legacy / non-eval columns
        try {
            Schema::table('upps', function (Blueprint $table) {
                $cols = [
                    'sso_opd_id',
                    'sso_opd_unit_id',
                    'slug',
                    'alamat',
                    'telepon',
                    'status',
                    'created_by',
                    'updated_by',
                    'deleted_at'
                ];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('upps', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        } catch (\Exception $e) {
            // if drop fails due to FK/index issues, ignore — admin can clean manually
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upps', function (Blueprint $table) {
            if (! Schema::hasColumn('upps', 'sso_opd_id')) {
                $table->unsignedBigInteger('sso_opd_id')->nullable()->after('kode');
            }
            if (! Schema::hasColumn('upps', 'sso_opd_unit_id')) {
                $table->unsignedBigInteger('sso_opd_unit_id')->nullable()->after('sso_opd_id');
            }
            if (! Schema::hasColumn('upps', 'slug')) {
                $table->string('slug')->nullable()->after('nama');
            }
            if (! Schema::hasColumn('upps', 'alamat')) {
                $table->text('alamat')->nullable()->after('parent_upp_id');
            }
            if (! Schema::hasColumn('upps', 'telepon')) {
                $table->string('telepon')->nullable()->after('alamat');
            }
            if (! Schema::hasColumn('upps', 'status')) {
                $table->enum('status', ['AKTIF','NONAKTIF'])->default('AKTIF')->after('telepon');
            }
            if (! Schema::hasColumn('upps', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
            }
            if (! Schema::hasColumn('upps', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            if (! Schema::hasColumn('upps', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }
        });

        // Recreate composite unique if possible
        try {
            DB::statement("ALTER TABLE upps ADD UNIQUE `uq_upps_sso_opd_unit` (`sso_opd_id`,`sso_opd_unit_id`)");
        } catch (\Exception $e) {
            // ignore
        }
    }
};
