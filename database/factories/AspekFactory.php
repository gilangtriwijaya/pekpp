<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aspek>
 */
class AspekFactory extends Factory
{
    protected $model = \App\Models\Aspek::class;

    public function definition(): array
    {
        return [
            'periode_id' => \App\Models\Periode::factory(),
            'kode' => strtoupper(fake()->unique()->bothify('A-###')),
            'nama' => fake()->words(2, true),
            'domain' => fake()->randomElement(['internal', 'publik']),
            'urutan' => fake()->numberBetween(1, 10),
            'bobot' => fake()->randomFloat(2, 0, 100),
            'aktif' => true,
            'keterangan' => null,
        ];
    }
}
