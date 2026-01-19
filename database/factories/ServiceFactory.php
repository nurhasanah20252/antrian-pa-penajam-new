<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'prefix' => strtoupper(fake()->randomLetter()),
            'average_time' => fake()->numberBetween(10, 30),
            'max_daily_queue' => fake()->numberBetween(50, 150),
            'is_active' => true,
            'requires_documents' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function umum(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'UMUM',
            'name' => 'Pelayanan Umum',
            'prefix' => 'A',
            'description' => 'Layanan umum untuk berbagai keperluan administrasi pengadilan',
        ]);
    }

    public function posbakum(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'POSBAKUM',
            'name' => 'Pos Bantuan Hukum',
            'prefix' => 'P',
            'description' => 'Layanan konsultasi hukum gratis untuk masyarakat tidak mampu',
        ]);
    }

    public function pembayaran(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'BAYAR',
            'name' => 'Pembayaran',
            'prefix' => 'B',
            'description' => 'Layanan pembayaran biaya perkara dan administrasi',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
