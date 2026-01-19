<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Officer>
 */
class OfficerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->petugasUmum(),
            'service_id' => Service::factory(),
            'counter_number' => fake()->numberBetween(1, 5),
            'is_active' => true,
            'is_available' => true,
            'max_concurrent' => 1,
        ];
    }

    public function forPosbakum(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->petugasPosbakum(),
            'service_id' => Service::factory()->posbakum(),
        ]);
    }

    public function forPembayaran(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->petugasPembayaran(),
            'service_id' => Service::factory()->pembayaran(),
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
