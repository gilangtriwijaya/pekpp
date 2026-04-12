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
        // 1) Add new columns (safe guards)
        if (! Schema::hasColumn('users', 'nama')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nama')->nullable()->after('username');
            });
        }
        if (! Schema::hasColumn('users', 'nip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nip', 50)->nullable()->after('email');
            });
        }
        if (! Schema::hasColumn('users', 'role_sso')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role_sso', 255)->nullable()->after('nip');
            });
        }
        if (! Schema::hasColumn('users', 'aktif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('aktif')->default(true)->after('role_sso');
            });
        }
        if (! Schema::hasColumn('users', 'last_sync_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_sync_at')->nullable()->after('aktif');
            });
        }

        // 2) Migrate data from old columns to new ones
        DB::table('users')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                $nama = $r->name ?? $r->username ?? null;

                // role_sso: prefer sso_app_role, else use sso_app_roles raw
                $role = null;
                if (isset($r->sso_app_role) && $r->sso_app_role) {
                    $role = $r->sso_app_role;
                } elseif (isset($r->sso_app_roles) && $r->sso_app_roles) {
                    // truncate to 255 chars
                    $role = substr($r->sso_app_roles, 0, 255);
                }

                // sso_user_id: if null, assign negative id marker to preserve uniqueness
                $sso = isset($r->sso_user_id) && $r->sso_user_id ? $r->sso_user_id : (-1 * (int)$r->id);

                DB::table('users')->where('id', $r->id)->update([
                    'nama' => $nama,
                    'role_sso' => $role,
                    'sso_user_id' => $sso,
                ]);
            }
        });

        // 3) Normalize and enforce column constraints
        // make email nullable
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN email varchar(255) NULL");

            // ensure role_sso not null string (set empty where null)
            DB::table('users')->whereNull('role_sso')->update(['role_sso' => '']);
            DB::statement("ALTER TABLE users MODIFY COLUMN role_sso varchar(255) NOT NULL DEFAULT ''");

            // make sso_user_id not null (unique exists already). If unique index missing, create.
            DB::statement("ALTER TABLE users MODIFY COLUMN sso_user_id bigint(20) unsigned NOT NULL");
        } else {
            // On SQLite, perform the non-DDL update
            DB::table('users')->whereNull('role_sso')->update(['role_sso' => '']);
        }

        // ensure unique index on sso_user_id
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('sso_user_id');
            });
        } catch (\Exception $e) {
            // index may already exist
        }

        // 4) Drop auth / payload columns we no longer keep
        try {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'password')) {
                    $table->dropColumn('password');
                }
                if (Schema::hasColumn('users', 'remember_token')) {
                    $table->dropColumn('remember_token');
                }
                if (Schema::hasColumn('users', 'email_verified_at')) {
                    $table->dropColumn('email_verified_at');
                }
                if (Schema::hasColumn('users', 'sso_app_roles')) {
                    $table->dropColumn('sso_app_roles');
                }
                if (Schema::hasColumn('users', 'sso_app_role')) {
                    $table->dropColumn('sso_app_role');
                }
                if (Schema::hasColumn('users', 'sso_allowed_opds_by_app')) {
                    $table->dropColumn('sso_allowed_opds_by_app');
                }
                if (Schema::hasColumn('users', 'username')) {
                    $table->dropColumn('username');
                }
            });
        } catch (\Exception $e) {
            // ignore drop failures (manual cleanup may be required)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate removed columns (best-effort)
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->after('sso_user_id');
            }
            if (! Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->string('remember_token', 100)->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'sso_app_roles')) {
                $table->longText('sso_app_roles')->nullable()->after('sso_app_role');
            }
            if (! Schema::hasColumn('users', 'sso_app_role')) {
                $table->string('sso_app_role')->nullable()->after('sso_user_id');
            }
            if (! Schema::hasColumn('users', 'sso_allowed_opds_by_app')) {
                $table->longText('sso_allowed_opds_by_app')->nullable()->after('sso_app_roles');
            }
        });

        // Try to move data back where possible
        DB::table('users')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                $sso_user = $r->sso_user_id;
                // if sso_user_id was negative marker, null it back
                if ($sso_user !== null && (int)$sso_user < 0) {
                    $sso_user = null;
                }

                DB::table('users')->where('id', $r->id)->update([
                    'name' => $r->nama ?? null,
                    'username' => null,
                    'sso_user_id' => $sso_user,
                ]);
            }
        });

        // Drop the new columns added
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nama')) {
                $table->dropColumn('nama');
            }
            if (Schema::hasColumn('users', 'nip')) {
                $table->dropColumn('nip');
            }
            if (Schema::hasColumn('users', 'role_sso')) {
                $table->dropColumn('role_sso');
            }
            if (Schema::hasColumn('users', 'aktif')) {
                $table->dropColumn('aktif');
            }
            if (Schema::hasColumn('users', 'last_sync_at')) {
                $table->dropColumn('last_sync_at');
            }
        });
    }
};
