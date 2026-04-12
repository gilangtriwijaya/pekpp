<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix all non-padded kodes (FI1 -> FI001, FI2 -> FI002, etc)
        $indikators = \App\Models\F03Indikator::all();
        foreach ($indikators as $ind) {
            if (preg_match('/^FI(\d+)$/', $ind->kode, $matches)) {
                $number = intval($matches[1]);
                $paddedKode = 'FI' . str_pad($number, 3, '0', STR_PAD_LEFT);
                if ($ind->kode !== $paddedKode) {
                    $ind->kode = $paddedKode;
                    $ind->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Can't really revert this safely, but we can indicate it's been done
    }
};
