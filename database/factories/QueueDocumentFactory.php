<?php

namespace Database\Factories;

use App\Models\Queue;
use App\Models\QueueDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class QueueDocumentFactory extends Factory
{
    protected $model = QueueDocument::class;

    public function definition(): array
    {
        $fileName = $this->faker->uuid().'.pdf';

        return [
            'queue_id' => Queue::factory(),
            'name' => $this->faker->words(2, true),
            'original_name' => $fileName,
            'path' => "queue-documents/{$this->faker->randomNumber()}/{$fileName}",
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(10000, 500000),
        ];
    }
}
