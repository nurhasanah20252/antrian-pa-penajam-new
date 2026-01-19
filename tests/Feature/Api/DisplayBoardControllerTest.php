<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\QueueStatus;
use App\UserRole;

beforeEach(function () {
    $this->service = Service::factory()->create([
        'code' => 'UMUM',
        'prefix' => 'A',
    ]);

    ServiceSchedule::factory()->create([
        'service_id' => $this->service->id,
        'day_of_week' => now()->dayOfWeek,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
    $this->officer = Officer::factory()->create([
        'user_id' => $user->id,
        'service_id' => $this->service->id,
        'counter_number' => 1,
        'is_active' => true,
    ]);
});

describe('GET /api/v1/display', function () {
    it('returns display board data', function () {
        $response = $this->getJson('/api/v1/display');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_queues',
                    'statistics' => [
                        'waiting',
                        'called',
                        'processing',
                        'completed',
                        'skipped',
                        'cancelled',
                        'total',
                        'average_wait_time',
                        'average_service_time',
                    ],
                    'timestamp',
                ],
            ])
            ->assertJsonPath('success', true);
    });

    it('includes currently called queues', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'number' => 'A001',
        ]);

        $response = $this->getJson('/api/v1/display');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data.current_queues')
            ->assertJsonPath('data.current_queues.0.number', 'A001');

        expect((int) $response->json('data.current_queues.0.counter'))->toBe(1);
    });

    it('includes processing queues', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Processing,
            'officer_id' => $this->officer->id,
            'number' => 'A002',
        ]);

        $response = $this->getJson('/api/v1/display');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data.current_queues');
    });

    it('excludes waiting queues from current_queues', function () {
        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $response = $this->getJson('/api/v1/display');

        $response->assertSuccessful()
            ->assertJsonCount(0, 'data.current_queues');
    });

    it('returns statistics for today', function () {
        Queue::factory()->waiting()->count(2)->create([
            'service_id' => $this->service->id,
        ]);
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Completed,
        ]);

        $response = $this->getJson('/api/v1/display');

        $response->assertSuccessful()
            ->assertJsonPath('data.statistics.waiting', 2)
            ->assertJsonPath('data.statistics.completed', 1)
            ->assertJsonPath('data.statistics.total', 3);
    });
});

describe('GET /api/v1/display/{service}', function () {
    it('returns display board for specific service', function () {
        $response = $this->getJson("/api/v1/display/{$this->service->id}");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'service' => [
                        'id',
                        'code',
                        'name',
                    ],
                    'current_queues',
                    'waiting_queues',
                    'statistics',
                    'timestamp',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.service.code', 'UMUM');
    });

    it('includes waiting queues list', function () {
        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'number' => 'A001',
            'is_priority' => true,
        ]);
        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'number' => 'A002',
            'is_priority' => false,
        ]);

        $response = $this->getJson("/api/v1/display/{$this->service->id}");

        $response->assertSuccessful()
            ->assertJsonCount(2, 'data.waiting_queues')
            ->assertJsonPath('data.waiting_queues.0.number', 'A001')
            ->assertJsonPath('data.waiting_queues.0.is_priority', true);
    });

    it('returns 404 for non-existent service', function () {
        $response = $this->getJson('/api/v1/display/999');

        $response->assertNotFound();
    });

    it('filters queues by service', function () {
        $otherService = Service::factory()->create(['prefix' => 'B']);
        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);
        Queue::factory()->waiting()->create([
            'service_id' => $otherService->id,
        ]);

        $response = $this->getJson("/api/v1/display/{$this->service->id}");

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data.waiting_queues')
            ->assertJsonPath('data.statistics.total', 1);
    });

    it('orders waiting queues by priority and time', function () {
        $third = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'number' => 'A003',
            'is_priority' => false,
            'created_at' => now()->subMinutes(1),
        ]);
        $first = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'number' => 'A001',
            'is_priority' => true,
            'created_at' => now(),
        ]);
        $second = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'number' => 'A002',
            'is_priority' => false,
            'created_at' => now()->subMinutes(2),
        ]);

        $response = $this->getJson("/api/v1/display/{$this->service->id}");

        $response->assertSuccessful();
        $waitingNumbers = collect($response->json('data.waiting_queues'))->pluck('number')->toArray();
        expect($waitingNumbers)->toBe(['A001', 'A002', 'A003']);
    });
});
