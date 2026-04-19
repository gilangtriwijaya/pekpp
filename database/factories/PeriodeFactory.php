<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Periode>
 */
class PeriodeFactory extends Factory
{
    protected $model = \App\Models\Periode::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2018, 2030);
        $start = now()->setYear($year)->startOfYear()->toDateString();
        $end = now()->setYear($year)->endOfYear()->toDateString();

        return [
            'kode' => strtoupper(fake()->unique()->bothify('PRD####')),
            'nama' => 'Periode ' . $year,
            'tahun' => $year,
            'tanggal_mulai' => $start,
            'tanggal_selesai' => $end,
            'status' => 'draft',
            'keterangan' => null,
            'is_aktif' => false,
        ];
    }
}
