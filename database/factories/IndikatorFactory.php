<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Indikator>
 */
class IndikatorFactory extends Factory
{
    protected $model = \App\Models\Indikator::class;

    public function definition(): array
    {
        return [
            'aspek_id' => \App\Models\Aspek::factory(),
            'kode' => strtoupper(fake()->unique()->bothify('I-###')),
            'nama' => fake()->words(3, true),
            'deskripsi' => null,
            'bukti_dukung' => null,
            'urutan' => fake()->numberBetween(1, 20),
            'aktif' => true,
        ];
    }
}
