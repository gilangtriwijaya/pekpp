<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'skala' type to tipe_input enum for scale/rating type questions
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE pertanyaan MODIFY COLUMN tipe_input
                ENUM('text','textarea','number','radio','checkbox','select','yesno','skala')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE pertanyaan MODIFY COLUMN tipe_input
                ENUM('text','textarea','number','radio','checkbox','select','yesno')");
        }
    }
};
