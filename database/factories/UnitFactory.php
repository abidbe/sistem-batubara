<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_tipe' => $this->faker->randomElement(['Mobil', 'Motor', 'Bus', 'Truk']),
            'nama' => $this->faker->name(),
            'foto' => 'unit-images/01JBF257DDPNGQ4XD4PSPJ8QZ3.jpg',
            'tahun' => $this->faker->year(),
            'kondisi' => $this->faker->randomElement(['Baik', 'Rusak Ringan', 'Rusak Berat']),
            'keterangan' => $this->faker->optional()->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
