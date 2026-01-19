<?php

use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\QueueStatus;

beforeEach(function () {
    $this->service = Service::factory()->create([
        'code' => 'UMUM',
        'prefix' => 'A',
        'average_time' => 10,
    ]);

    ServiceSchedule::factory()->create([
        'service_id' => $this->service->id,
        'day_of_week' => now()->dayOfWeek,
        'open_time' => '00:00:00',
        'close_time' => '23:59:59',
        'is_active' => true,
    ]);
});

describe('GET /api/v1/services', function () {
    it('returns list of active services', function () {
        $response = $this->getJson('/api/v1/services');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'description',
                        'prefix',
                        'average_time',
                        'is_available',
                        'waiting_count',
                        'estimated_wait',
                    ],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    });

    it('excludes inactive services', function () {
        Service::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/services');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data');
    });

    it('includes waiting count and estimated time', function () {
        Queue::factory()->waiting()->count(3)->create([
            'service_id' => $this->service->id,
        ]);

        $response = $this->getJson('/api/v1/services');

        $response->assertSuccessful()
            ->assertJsonPath('data.0.waiting_count', 3)
            ->assertJsonPath('data.0.estimated_wait', 30);
    });
});

describe('POST /api/v1/queues', function () {
    it('creates a queue successfully', function () {
        $response = $this->postJson('/api/v1/queues', [
            'service_id' => $this->service->id,
            'name' => 'John Doe',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'number',
                    'service',
                    'estimated_time',
                    'position',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.number', 'A001');

        $this->assertDatabaseHas('queues', [
            'name' => 'John Doe',
            'service_id' => $this->service->id,
            'status' => QueueStatus::Waiting->value,
        ]);
    });

    it('creates a priority queue', function () {
        $response = $this->postJson('/api/v1/queues', [
            'service_id' => $this->service->id,
            'name' => 'Priority Customer',
            'is_priority' => true,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('queues', [
            'name' => 'Priority Customer',
            'is_priority' => true,
        ]);
    });

    it('validates required fields', function () {
        $response = $this->postJson('/api/v1/queues', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['service_id', 'name']);
    });

    it('validates service exists', function () {
        $response = $this->postJson('/api/v1/queues', [
            'service_id' => 999,
            'name' => 'John Doe',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['service_id']);
    });

    it('associates user_id when authenticated', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/queues', [
                'service_id' => $this->service->id,
                'name' => 'Authenticated User',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('queues', [
            'name' => 'Authenticated User',
            'user_id' => $user->id,
        ]);
    });
});

describe('GET /api/v1/queues/{queue}', function () {
    it('returns queue details', function () {
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'number' => 'A001',
        ]);

        $response = $this->getJson("/api/v1/queues/{$queue->id}");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'number',
                    'service',
                    'status',
                    'status_label',
                    'is_priority',
                    'position',
                    'counter',
                    'waiting_time',
                    'created_at',
                    'called_at',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.number', 'A001');
    });

    it('returns 404 for non-existent queue', function () {
        $response = $this->getJson('/api/v1/queues/999');

        $response->assertNotFound();
    });

    it('includes position for waiting queues', function () {
        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'created_at' => now()->subMinutes(2),
        ]);
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'created_at' => now()->subMinute(),
        ]);

        $response = $this->getJson("/api/v1/queues/{$queue->id}");

        $response->assertSuccessful()
            ->assertJsonPath('data.position', 2);
    });

    it('position is null for non-waiting queues', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Completed,
        ]);

        $response = $this->getJson("/api/v1/queues/{$queue->id}");

        $response->assertSuccessful()
            ->assertJsonPath('data.position', null);
    });
});

describe('POST /api/v1/queues/{queue}/cancel', function () {
    it('requires authentication', function () {
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $response = $this->postJson("/api/v1/queues/{$queue->id}/cancel");

        $response->assertUnauthorized();
    });

    it('cancels own queue', function () {
        $user = User::factory()->create();
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/queues/{$queue->id}/cancel");

        $response->assertSuccessful()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Antrian berhasil dibatalkan.');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => QueueStatus::Cancelled->value,
        ]);
    });

    it('forbids cancelling other user queue', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/queues/{$queue->id}/cancel");

        $response->assertForbidden()
            ->assertJsonPath('success', false);
    });

    it('returns error for already completed queue', function () {
        $user = User::factory()->create();
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'user_id' => $user->id,
            'status' => QueueStatus::Completed,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/queues/{$queue->id}/cancel");

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    });
});
