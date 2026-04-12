<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\Type;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns exist before dropping
        if (Schema::hasTable('f01_indikator_bukti')) {
            // Get the existing columns
            $hasJenis = Schema::hasColumn('f01_indikator_bukti', 'jenis');
            $hasNama = Schema::hasColumn('f01_indikator_bukti', 'nama');
            $hasPath = Schema::hasColumn('f01_indikator_bukti', 'path_atau_url');
            $hasUploadedBy = Schema::hasColumn('f01_indikator_bukti', 'uploaded_by');
            $hasLinkUrl = Schema::hasColumn('f01_indikator_bukti', 'link_url');
            $hasDeskripsi = Schema::hasColumn('f01_indikator_bukti', 'deskripsi');

            // Only modify if needed
            if ($hasJenis || $hasNama || $hasPath || $hasUploadedBy) {
                Schema::table('f01_indikator_bukti', function (Blueprint $table) use ($hasUploadedBy) {
                    // Drop foreign key first if it exists
                    if ($hasUploadedBy) {
                        try {
                            $table->dropForeign(['uploaded_by']);
                        } catch (\Exception $e) {
                            // Foreign key might not exist, continue
                        }
                    }
                });

                Schema::table('f01_indikator_bukti', function (Blueprint $table) use ($hasJenis, $hasNama, $hasPath, $hasUploadedBy) {
                    // Drop old columns
                    $columnsToDrop = [];
                    if ($hasJenis) $columnsToDrop[] = 'jenis';
                    if ($hasNama) $columnsToDrop[] = 'nama';
                    if ($hasPath) $columnsToDrop[] = 'path_atau_url';
                    if ($hasUploadedBy) $columnsToDrop[] = 'uploaded_by';
                    
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }

            // Add new columns if they don't exist
            if (!$hasLinkUrl || !$hasDeskripsi) {
                Schema::table('f01_indikator_bukti', function (Blueprint $table) use ($hasLinkUrl, $hasDeskripsi) {
                    if (!$hasLinkUrl) {
                        $table->text('link_url')->after('f01_indikator_nilai_id');
                    }
                    if (!$hasDeskripsi) {
                        $table->text('deskripsi')->nullable()->after('link_url');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('f01_indikator_bukti')) {
            $hasLinkUrl = Schema::hasColumn('f01_indikator_bukti', 'link_url');
            $hasDeskripsi = Schema::hasColumn('f01_indikator_bukti', 'deskripsi');

            if ($hasLinkUrl || $hasDeskripsi) {
                Schema::table('f01_indikator_bukti', function (Blueprint $table) use ($hasLinkUrl, $hasDeskripsi) {
                    $columnsToDrop = [];
                    if ($hasLinkUrl) $columnsToDrop[] = 'link_url';
                    if ($hasDeskripsi) $columnsToDrop[] = 'deskripsi';
                    
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }

            // Restore old columns for rollback
            Schema::table('f01_indikator_bukti', function (Blueprint $table) {
                $table->enum('jenis', ['file','url'])->default('file')->after('f01_indikator_nilai_id');
                $table->string('nama')->nullable()->after('jenis');
                $table->text('path_atau_url')->after('nama');
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                
                $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
};

