<?php

namespace Database\Factories;

use App\Models\Officer;
use App\Models\Service;
use App\Models\User;
use App\QueueStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Queue>
 */
class QueueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'number' => 'A-'.str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'service_id' => Service::factory(),
            'user_id' => null,
            'officer_id' => null,
            'name' => fake()->name(),
            'nik' => fake()->numerify('################'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'notify_email' => false,
            'notify_sms' => false,
            'is_priority' => false,
            'status' => QueueStatus::Waiting,
            'source' => fake()->randomElement(['online', 'kiosk']),
            'estimated_time' => fake()->numberBetween(10, 60),
            'called_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'notes' => null,
            'notified_approaching_at' => null,
            'notified_called_at' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'name' => $user->name,
            'nik' => $user->nik,
            'phone' => $user->phone,
            'email' => $user->email,
        ]);
    }

    public function priority(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_priority' => true,
        ]);
    }

    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QueueStatus::Waiting,
        ]);
    }

    public function called(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QueueStatus::Called,
            'called_at' => now(),
        ]);
    }

    public function processing(Officer $officer): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QueueStatus::Processing,
            'officer_id' => $officer->id,
            'called_at' => now()->subMinutes(5),
            'started_at' => now(),
        ]);
    }

    public function completed(Officer $officer): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QueueStatus::Completed,
            'officer_id' => $officer->id,
            'called_at' => now()->subMinutes(20),
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QueueStatus::Skipped,
            'called_at' => now()->subMinutes(10),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QueueStatus::Cancelled,
        ]);
    }

    public function fromKiosk(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'kiosk',
        ]);
    }

    public function fromOnline(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'online',
        ]);
    }

    public function withEmailNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'notify_email' => true,
            'email' => $attributes['email'] ?? fake()->safeEmail(),
        ]);
    }

    public function withSmsNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'notify_sms' => true,
            'phone' => $attributes['phone'] ?? fake()->phoneNumber(),
        ]);
    }

    public function withAllNotifications(): static
    {
        return $this->withEmailNotification()->withSmsNotification();
    }
}
