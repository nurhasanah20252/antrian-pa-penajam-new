<?php

namespace Database\Factories;

use App\Models\Officer;
use App\Models\Queue;
use App\QueueStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QueueLog>
 */
class QueueLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'queue_id' => Queue::factory(),
            'officer_id' => null,
            'from_status' => null,
            'to_status' => QueueStatus::Waiting,
            'notes' => null,
            'created_at' => now(),
        ];
    }

    public function withOfficer(Officer $officer): static
    {
        return $this->state(fn (array $attributes) => [
            'officer_id' => $officer->id,
        ]);
    }

    public function statusChange(QueueStatus $from, QueueStatus $to): static
    {
        return $this->state(fn (array $attributes) => [
            'from_status' => $from,
            'to_status' => $to,
        ]);
    }
}
