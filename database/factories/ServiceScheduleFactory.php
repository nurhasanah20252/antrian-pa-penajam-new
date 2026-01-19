<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceSchedule>
 */
class ServiceScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'open_time' => '08:00:00',
            'close_time' => '16:00:00',
            'is_active' => true,
        ];
    }

    public function forDay(int $dayOfWeek): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $dayOfWeek,
        ]);
    }

    public function weekday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => fake()->numberBetween(1, 5),
        ]);
    }

    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => fake()->randomElement([0, 6]),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
