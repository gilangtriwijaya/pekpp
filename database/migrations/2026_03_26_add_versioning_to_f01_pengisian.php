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
        // 1. Add versioning columns to f01_pengisian (check if not already exists)
        if (!Schema::hasColumn('f01_pengisian', 'version_number')) {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->integer('version_number')->default(1)->after('status')->comment('Increment setiap kali resubmit');
            });
        }
        
        if (!Schema::hasColumn('f01_pengisian', 'previous_f01_pengisian_id')) {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->foreignId('previous_f01_pengisian_id')
                      ->nullable()
                      ->after('version_number')
                      ->constrained('f01_pengisian')
                      ->nullOnDelete()
                      ->comment('Link ke versi sebelumnya (NULL untuk v1)');
            });
        }
        
        if (!Schema::hasColumn('f01_pengisian', 'is_latest_version')) {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->boolean('is_latest_version')->default(true)->after('previous_f01_pengisian_id')->comment('Flag untuk query terbaru');
            });
        }

        // 2. Add indexes untuk query optimization (wrapped in try-catch for safety)
        try {
            DB::statement('ALTER TABLE f01_pengisian ADD INDEX `idx_is_latest_version` (`is_latest_version`)');
        } catch (\Exception $e) {
            // Index already exists, that's fine
        }
        
        try {
            DB::statement('ALTER TABLE f01_pengisian ADD INDEX `idx_upp_periode_latest` (`upp_id`, `periode_id`, `is_latest_version`)');
        } catch (\Exception $e) {
            // Index already exists
        }
        
        try {
            DB::statement('ALTER TABLE f01_pengisian ADD INDEX `idx_periode_upp_latest` (`periode_id`, `upp_id`, `is_latest_version`)');
        } catch (\Exception $e) {
            // Index already exists
        }

        // 3. Handle existing unique constraint - try multiple names
        // Laravel auto-generates name: tablename_col1_col2_unique
        $constraintNames = [
            'f01_pengisian_upp_periode_unique',        // Actual name in DB
            'f01_pengisian_periode_id_upp_id_unique',  // Laravel default
            'uq_f01_periode_upp',                      // Custom name
            'periode_id_upp_id_unique',                // Alternative
        ];
        
        $constraintDropped = false;
        foreach ($constraintNames as $name) {
            try {
                DB::statement("ALTER TABLE f01_pengisian DROP INDEX `$name`");
                $constraintDropped = true;
                break;
            } catch (\Exception $e) {
                // Continue to next name
            }
        }

        // 4. Add new conditional unique constraint (MySQL 8.0.16+ syntax)
        // This ensures only 1 latest-version pengisian per periode+upp
        try {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE f01_pengisian ADD UNIQUE KEY `uq_f01_periode_upp_latest` 
                             (periode_id, upp_id, is_latest_version) WHERE is_latest_version = 1');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE f01_pengisian ADD CONSTRAINT uq_f01_periode_upp_latest 
                             UNIQUE (periode_id, upp_id, is_latest_version) WHERE is_latest_version = true');
            }
        } catch (\Exception $e) {
            // If conditional unique doesn't work, app will use scope + validation
            \Log::warning('Could not add conditional unique constraint: ' . $e->getMessage() . 
                         '. Using application-level validation instead.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f01_pengisian', function (Blueprint $table) {
            // Drop indexes
            try {
                $table->dropIndex(['is_latest_version']);
            } catch (\Exception $e) {
                \Log::warning('Could not drop index: ' . $e->getMessage());
            }
            
            try {
                $table->dropIndex(['upp_id', 'periode_id', 'is_latest_version']);
            } catch (\Exception $e) {
                \Log::warning('Could not drop composite index: ' . $e->getMessage());
            }
            
            try {
                $table->dropIndex(['periode_id', 'upp_id', 'is_latest_version']);
            } catch (\Exception $e) {
                \Log::warning('Could not drop composite index 2: ' . $e->getMessage());
            }

            // Drop foreign key for previous_f01_pengisian_id
            try {
                $table->dropForeign(['previous_f01_pengisian_id']);
            } catch (\Exception $e) {
                \Log::warning('Could not drop foreign key: ' . $e->getMessage());
            }

            // Drop columns
            $table->dropColumn(['version_number', 'previous_f01_pengisian_id', 'is_latest_version']);
        });

        // Intercept any new constraint and remove it
        try {
            DB::statement('ALTER TABLE f01_pengisian DROP INDEX `uq_f01_periode_upp_latest`');
        } catch (\Exception $e) {
            \Log::warning('Could not drop unique constraint: ' . $e->getMessage());
        }

        // Recreate old unique constraint
        try {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->unique(['periode_id', 'upp_id'], 'uq_f01_periode_upp');
            });
        } catch (\Exception $e) {
            \Log::warning('Could not recreate unique constraint: ' . $e->getMessage());
        }
    }
};
