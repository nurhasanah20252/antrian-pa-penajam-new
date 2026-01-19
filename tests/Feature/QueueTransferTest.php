<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\QueueStatus;
use App\Services\QueueService;
use App\UserRole;

beforeEach(function () {
    $this->sourceService = Service::factory()->create([
        'prefix' => 'A',
        'is_active' => true,
    ]);

    $this->targetService = Service::factory()->create([
        'prefix' => 'B',
        'is_active' => true,
    ]);

    ServiceSchedule::factory()->create([
        'service_id' => $this->sourceService->id,
        'day_of_week' => now()->dayOfWeek,
        'open_time' => '00:00:00',
        'close_time' => '23:59:59',
        'is_active' => true,
    ]);

    ServiceSchedule::factory()->create([
        'service_id' => $this->targetService->id,
        'day_of_week' => now()->dayOfWeek,
        'open_time' => '00:00:00',
        'close_time' => '23:59:59',
        'is_active' => true,
    ]);

    $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
    $this->officer = Officer::factory()->create([
        'user_id' => $user->id,
        'service_id' => $this->sourceService->id,
        'counter_number' => 1,
        'is_active' => true,
    ]);

    $this->queueService = app(QueueService::class);
});

describe('QueueService::transferQueue', function () {
    it('transfers queue to another service', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
            'name' => 'John Doe',
            'phone' => '08123456789',
        ]);

        $newQueue = $this->queueService->transferQueue(
            $queue,
            $this->targetService,
            $this->officer,
            'Transfer test'
        );

        expect($newQueue->service_id)->toBe($this->targetService->id)
            ->and($newQueue->transferred_from_id)->toBe($queue->id)
            ->and($newQueue->name)->toBe('John Doe')
            ->and($newQueue->phone)->toBe('08123456789')
            ->and($newQueue->status)->toBe(QueueStatus::Waiting)
            ->and($newQueue->number)->toStartWith('B');

        $queue->refresh();
        expect($queue->status)->toBe(QueueStatus::Cancelled);
    });

    it('preserves priority status on transfer', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
            'is_priority' => true,
        ]);

        $newQueue = $this->queueService->transferQueue($queue, $this->targetService, $this->officer);

        expect($newQueue->is_priority)->toBeTrue();
    });

    it('throws exception when transferring to same service', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $this->queueService->transferQueue($queue, $this->sourceService, $this->officer);
    })->throws(\RuntimeException::class, 'Tidak dapat transfer ke layanan yang sama.');

    it('throws exception when transferring inactive queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Completed,
        ]);

        $this->queueService->transferQueue($queue, $this->targetService, $this->officer);
    })->throws(\RuntimeException::class, 'Antrian sudah tidak aktif dan tidak dapat ditransfer.');

    it('throws exception when target service is inactive', function () {
        $inactiveService = Service::factory()->create(['is_active' => false]);

        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $this->queueService->transferQueue($queue, $inactiveService, $this->officer);
    })->throws(\RuntimeException::class, 'Layanan tujuan tidak aktif.');

    it('creates queue logs for transfer', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $newQueue = $this->queueService->transferQueue($queue, $this->targetService, $this->officer);

        expect($queue->logs()->count())->toBe(1);
        expect($newQueue->logs()->count())->toBe(1);

        $sourceLog = $queue->logs()->first();
        expect($sourceLog->to_status)->toBe(QueueStatus::Cancelled);
        expect($sourceLog->notes)->toContain('Ditransfer ke layanan');

        $targetLog = $newQueue->logs()->first();
        expect($targetLog->to_status)->toBe(QueueStatus::Waiting);
        expect($targetLog->notes)->toContain('Ditransfer dari layanan');
    });
});

describe('Officer QueueController::transfer', function () {
    it('allows officer to transfer queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $response = $this->actingAs($this->officer->user)
            ->post(route('officer.queues.transfer', $queue), [
                'target_service_id' => $this->targetService->id,
                'notes' => 'Transfer by officer',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $queue->refresh();
        expect($queue->status)->toBe(QueueStatus::Cancelled);

        $newQueue = Queue::where('transferred_from_id', $queue->id)->first();
        expect($newQueue)->not->toBeNull()
            ->and($newQueue->service_id)->toBe($this->targetService->id);
    });

    it('denies transfer for non-officer users', function () {
        $user = User::factory()->create(['role' => UserRole::Masyarakat]);

        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $response = $this->actingAs($user)
            ->post(route('officer.queues.transfer', $queue), [
                'target_service_id' => $this->targetService->id,
            ]);

        $response->assertForbidden();
    });

    it('denies transfer for officer from different service', function () {
        $otherService = Service::factory()->create();
        $otherUser = User::factory()->create(['role' => UserRole::PetugasUmum]);
        $otherOfficer = Officer::factory()->create([
            'user_id' => $otherUser->id,
            'service_id' => $otherService->id,
            'is_active' => true,
        ]);

        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $response = $this->actingAs($otherOfficer->user)
            ->post(route('officer.queues.transfer', $queue), [
                'target_service_id' => $this->targetService->id,
            ]);

        $response->assertForbidden();
    });

    it('validates target service is required', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $response = $this->actingAs($this->officer->user)
            ->post(route('officer.queues.transfer', $queue), []);

        $response->assertSessionHasErrors('target_service_id');
    });

    it('validates cannot transfer to same service', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Waiting,
        ]);

        $response = $this->actingAs($this->officer->user)
            ->post(route('officer.queues.transfer', $queue), [
                'target_service_id' => $this->sourceService->id,
            ]);

        $response->assertSessionHasErrors('target_service_id');
    });
});

describe('Queue model transfer relationships', function () {
    it('can access transferred from queue', function () {
        $originalQueue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Cancelled,
        ]);

        $newQueue = Queue::factory()->create([
            'service_id' => $this->targetService->id,
            'transferred_from_id' => $originalQueue->id,
        ]);

        expect($newQueue->transferredFrom->id)->toBe($originalQueue->id);
    });

    it('can access transfer history', function () {
        $originalQueue = Queue::factory()->create([
            'service_id' => $this->sourceService->id,
            'status' => QueueStatus::Cancelled,
        ]);

        $newQueue = Queue::factory()->create([
            'service_id' => $this->targetService->id,
            'transferred_from_id' => $originalQueue->id,
        ]);

        expect($originalQueue->transfers)->toHaveCount(1)
            ->and($originalQueue->transfers->first()->id)->toBe($newQueue->id);
    });
});
