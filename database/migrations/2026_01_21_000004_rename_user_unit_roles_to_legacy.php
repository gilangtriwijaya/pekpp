<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_unit_roles')) {
            Schema::rename('user_unit_roles', 'user_unit_roles_legacy');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_unit_roles_legacy')) {
            Schema::rename('user_unit_roles_legacy', 'user_unit_roles');
        }
    }
};
