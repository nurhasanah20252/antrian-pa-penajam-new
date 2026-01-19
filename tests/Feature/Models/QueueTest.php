<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\QueueLog;
use App\Models\Service;
use App\Models\User;
use App\QueueStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Queue Model', function () {
    it('can be created with factory', function () {
        $queue = Queue::factory()->create();

        expect($queue)->toBeInstanceOf(Queue::class)
            ->and($queue->id)->toBeInt()
            ->and($queue->number)->toBeString()
            ->and($queue->name)->toBeString()
            ->and($queue->status)->toBeInstanceOf(QueueStatus::class);
    });

    it('creates with waiting status by default', function () {
        $queue = Queue::factory()->create();

        expect($queue->status)->toBe(QueueStatus::Waiting);
    });

    it('can create priority queue', function () {
        $queue = Queue::factory()->priority()->create();

        expect($queue->is_priority)->toBeTrue();
    });

    it('can create queue from kiosk', function () {
        $queue = Queue::factory()->fromKiosk()->create();

        expect($queue->source)->toBe('kiosk');
    });

    it('can create queue from online', function () {
        $queue = Queue::factory()->fromOnline()->create();

        expect($queue->source)->toBe('online');
    });

    it('can create called queue', function () {
        $queue = Queue::factory()->called()->create();

        expect($queue->status)->toBe(QueueStatus::Called)
            ->and($queue->called_at)->not->toBeNull();
    });

    it('can create processing queue', function () {
        $officer = Officer::factory()->create();
        $queue = Queue::factory()->processing($officer)->create();

        expect($queue->status)->toBe(QueueStatus::Processing)
            ->and($queue->officer_id)->toBe($officer->id)
            ->and($queue->started_at)->not->toBeNull();
    });

    it('can create completed queue', function () {
        $officer = Officer::factory()->create();
        $queue = Queue::factory()->completed($officer)->create();

        expect($queue->status)->toBe(QueueStatus::Completed)
            ->and($queue->officer_id)->toBe($officer->id)
            ->and($queue->completed_at)->not->toBeNull();
    });

    it('can create skipped queue', function () {
        $queue = Queue::factory()->skipped()->create();

        expect($queue->status)->toBe(QueueStatus::Skipped);
    });

    it('can create cancelled queue', function () {
        $queue = Queue::factory()->cancelled()->create();

        expect($queue->status)->toBe(QueueStatus::Cancelled);
    });

    it('can create queue for user', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'nik' => '1234567890123456',
            'phone' => '08123456789',
            'email' => 'test@example.com',
        ]);

        $queue = Queue::factory()->forUser($user)->create();

        expect($queue->user_id)->toBe($user->id)
            ->and($queue->name)->toBe('Test User')
            ->and($queue->nik)->toBe('1234567890123456')
            ->and($queue->phone)->toBe('08123456789')
            ->and($queue->email)->toBe('test@example.com');
    });
});

describe('Queue Relationships', function () {
    it('belongs to service', function () {
        $service = Service::factory()->create();
        $queue = Queue::factory()->create(['service_id' => $service->id]);

        expect($queue->service)->toBeInstanceOf(Service::class)
            ->and($queue->service->id)->toBe($service->id);
    });

    it('belongs to user', function () {
        $user = User::factory()->create();
        $queue = Queue::factory()->forUser($user)->create();

        expect($queue->user)->toBeInstanceOf(User::class)
            ->and($queue->user->id)->toBe($user->id);
    });

    it('belongs to officer', function () {
        $officer = Officer::factory()->create();
        $queue = Queue::factory()->processing($officer)->create();

        expect($queue->officer)->toBeInstanceOf(Officer::class)
            ->and($queue->officer->id)->toBe($officer->id);
    });

    it('has many logs', function () {
        $queue = Queue::factory()->create();
        QueueLog::factory()->count(3)->create(['queue_id' => $queue->id]);

        expect($queue->logs)->toHaveCount(3)
            ->and($queue->logs->first())->toBeInstanceOf(QueueLog::class);
    });
});

describe('Queue Helper Methods', function () {
    it('checks active status correctly', function () {
        $waitingQueue = Queue::factory()->waiting()->create();
        $calledQueue = Queue::factory()->called()->create();
        $officer = Officer::factory()->create();
        $processingQueue = Queue::factory()->processing($officer)->create();
        $completedQueue = Queue::factory()->completed($officer)->create();
        $skippedQueue = Queue::factory()->skipped()->create();
        $cancelledQueue = Queue::factory()->cancelled()->create();

        expect($waitingQueue->isActive())->toBeTrue()
            ->and($calledQueue->isActive())->toBeTrue()
            ->and($processingQueue->isActive())->toBeTrue()
            ->and($completedQueue->isActive())->toBeFalse()
            ->and($skippedQueue->isActive())->toBeFalse()
            ->and($cancelledQueue->isActive())->toBeFalse();
    });

    it('checks waiting status correctly', function () {
        $waitingQueue = Queue::factory()->waiting()->create();
        $calledQueue = Queue::factory()->called()->create();

        expect($waitingQueue->isWaiting())->toBeTrue()
            ->and($calledQueue->isWaiting())->toBeFalse();
    });

    it('calculates waiting time for waiting queue', function () {
        $queue = Queue::factory()->waiting()->create([
            'created_at' => now()->subMinutes(15),
        ]);

        expect($queue->waiting_time)->toBeGreaterThanOrEqual(15);
    });

    it('calculates waiting time for called queue', function () {
        $queue = Queue::factory()->create([
            'created_at' => now()->subMinutes(20),
            'called_at' => now()->subMinutes(10),
        ]);

        expect($queue->waiting_time)->toBe(10);
    });

    it('calculates service time for completed queue', function () {
        $officer = Officer::factory()->create();
        $queue = Queue::factory()->create([
            'officer_id' => $officer->id,
            'status' => QueueStatus::Completed,
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
        ]);

        expect($queue->service_time)->toBe(15);
    });

    it('returns null service time for incomplete queue', function () {
        $queue = Queue::factory()->waiting()->create();

        expect($queue->service_time)->toBeNull();
    });
});
