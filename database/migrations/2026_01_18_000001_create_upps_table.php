<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('upps', function (Blueprint $table) {
            $table->id();

            // Reference to SSO source (opd and unit opd ids from SSO)
            $table->unsignedBigInteger('sso_opd_id')->nullable();
            $table->unsignedBigInteger('sso_opd_unit_id')->nullable();

            // UPP identity and optional code/slug used by PEKPP
            $table->string('code')->nullable();
            $table->string('nama');
            $table->string('slug')->nullable();

            // Hierarchy for reporting/aggregation
            $table->unsignedBigInteger('parent_upp_id')->nullable();

            // Optional contact / location / status
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->enum('status', ['AKTIF', 'NONAKTIF'])->default('AKTIF');

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Ensure one-to-one mapping from SSO pair -> upp
            $table->unique(['sso_opd_id', 'sso_opd_unit_id'], 'uq_upps_sso_opd_unit');

            $table->index('parent_upp_id');

            // Self foreign key for hierarchy (set null on delete)
            $table->foreign('parent_upp_id')->references('id')->on('upps')->onDelete('SET NULL');
        });
    }

    public function down(): void
    {
        Schema::table('upps', function (Blueprint $table) {
            $table->dropForeign(['parent_upp_id']);
        });
        Schema::dropIfExists('upps');
    }
};
