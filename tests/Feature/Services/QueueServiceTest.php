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
    $this->service = Service::factory()->create([
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

    $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
    $this->officer = Officer::factory()->create([
        'user_id' => $user->id,
        'service_id' => $this->service->id,
        'counter_number' => 1,
        'is_active' => true,
    ]);

    $this->queueService = app(QueueService::class);
});

describe('createQueue', function () {
    it('creates a queue with generated number', function () {
        $queue = $this->queueService->createQueue($this->service, [
            'name' => 'John Doe',
        ]);

        expect($queue)->toBeInstanceOf(Queue::class)
            ->and($queue->number)->toBe('A001')
            ->and($queue->status)->toBe(QueueStatus::Waiting)
            ->and($queue->name)->toBe('John Doe');
    });

    it('increments queue number for same service', function () {
        $this->queueService->createQueue($this->service, ['name' => 'First']);
        $second = $this->queueService->createQueue($this->service, ['name' => 'Second']);

        expect($second->number)->toBe('A002');
    });

    it('creates a priority queue', function () {
        $queue = $this->queueService->createQueue($this->service, [
            'name' => 'Priority Customer',
            'is_priority' => true,
        ]);

        expect($queue->is_priority)->toBeTrue();
    });

    it('creates queue log on creation', function () {
        $queue = $this->queueService->createQueue($this->service, ['name' => 'Test']);

        expect($queue->logs)->toHaveCount(1)
            ->and($queue->logs->first()->to_status)->toBe(QueueStatus::Waiting);
    });
});

describe('generateQueueNumber', function () {
    it('generates number with service prefix', function () {
        $number = $this->queueService->generateQueueNumber($this->service);

        expect($number)->toBe('A001');
    });

    it('pads number to 3 digits', function () {
        Queue::factory()->count(9)->create([
            'service_id' => $this->service->id,
            'created_at' => now(),
        ]);

        $number = $this->queueService->generateQueueNumber($this->service);

        expect($number)->toBe('A010');
    });

    it('counts only today queues', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'created_at' => now()->subDay(),
        ]);

        $number = $this->queueService->generateQueueNumber($this->service);

        expect($number)->toBe('A001');
    });
});

describe('callNextQueue', function () {
    it('calls the next waiting queue', function () {
        $waiting = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $called = $this->queueService->callNextQueue($this->officer);

        expect($called)->not->toBeNull()
            ->and($called->id)->toBe($waiting->id)
            ->and($called->status)->toBe(QueueStatus::Called)
            ->and($called->officer_id)->toBe($this->officer->id);
    });

    it('returns null when no waiting queues', function () {
        $result = $this->queueService->callNextQueue($this->officer);

        expect($result)->toBeNull();
    });

    it('prioritizes priority queues', function () {
        $normal = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'is_priority' => false,
        ]);
        $priority = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'is_priority' => true,
        ]);

        $called = $this->queueService->callNextQueue($this->officer);

        expect($called->id)->toBe($priority->id);
    });

    it('returns null if officer cannot accept queue', function () {
        $this->officer->update(['is_active' => false]);

        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $result = $this->queueService->callNextQueue($this->officer);

        expect($result)->toBeNull();
    });

    it('only calls queues from same service', function () {
        $otherService = Service::factory()->create();
        Queue::factory()->waiting()->create([
            'service_id' => $otherService->id,
        ]);

        $result = $this->queueService->callNextQueue($this->officer);

        expect($result)->toBeNull();
    });
});

describe('callQueue', function () {
    it('calls a specific waiting queue', function () {
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $called = $this->queueService->callQueue($queue, $this->officer);

        expect($called->status)->toBe(QueueStatus::Called)
            ->and($called->officer_id)->toBe($this->officer->id);
    });

    it('calls a skipped queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Skipped,
        ]);

        $called = $this->queueService->callQueue($queue, $this->officer);

        expect($called->status)->toBe(QueueStatus::Called);
    });

    it('throws exception for non-waiting queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Processing,
        ]);

        $this->queueService->callQueue($queue, $this->officer);
    })->throws(RuntimeException::class, 'Antrian tidak dalam status menunggu atau dilewati.');

    it('throws exception if officer cannot accept', function () {
        $this->officer->update(['is_active' => false]);

        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $this->queueService->callQueue($queue, $this->officer);
    })->throws(RuntimeException::class, 'Officer tidak dapat menerima antrian.');
});

describe('startProcessing', function () {
    it('starts processing a called queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
        ]);

        $processing = $this->queueService->startProcessing($queue, $this->officer);

        expect($processing->status)->toBe(QueueStatus::Processing)
            ->and($processing->started_at)->not->toBeNull();
    });

    it('throws exception for non-called queue', function () {
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $this->queueService->startProcessing($queue, $this->officer);
    })->throws(RuntimeException::class, 'Antrian harus dalam status dipanggil untuk mulai diproses.');

    it('throws exception for different officer', function () {
        $otherUser = User::factory()->create(['role' => UserRole::PetugasUmum]);
        $otherOfficer = Officer::factory()->create([
            'user_id' => $otherUser->id,
            'service_id' => $this->service->id,
        ]);

        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $otherOfficer->id,
        ]);

        $this->queueService->startProcessing($queue, $this->officer);
    })->throws(RuntimeException::class, 'Antrian bukan milik officer ini.');
});

describe('completeQueue', function () {
    it('completes a processing queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Processing,
            'officer_id' => $this->officer->id,
            'started_at' => now(),
        ]);

        $completed = $this->queueService->completeQueue($queue, $this->officer, 'Done');

        expect($completed->status)->toBe(QueueStatus::Completed)
            ->and($completed->completed_at)->not->toBeNull()
            ->and($completed->notes)->toBe('Done');
    });

    it('throws exception for non-processing queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
        ]);

        $this->queueService->completeQueue($queue, $this->officer);
    })->throws(RuntimeException::class, 'Antrian harus dalam status sedang diproses untuk diselesaikan.');
});

describe('skipQueue', function () {
    it('skips a called queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
        ]);

        $skipped = $this->queueService->skipQueue($queue, $this->officer, 'Not present');

        expect($skipped->status)->toBe(QueueStatus::Skipped)
            ->and($skipped->notes)->toBe('Not present');
    });

    it('throws exception for non-called queue', function () {
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $this->queueService->skipQueue($queue, $this->officer);
    })->throws(RuntimeException::class, 'Antrian harus dalam status dipanggil untuk dilewati.');
});

describe('cancelQueue', function () {
    it('cancels a waiting queue', function () {
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $cancelled = $this->queueService->cancelQueue($queue, null, 'User cancelled');

        expect($cancelled->status)->toBe(QueueStatus::Cancelled)
            ->and($cancelled->notes)->toBe('User cancelled');
    });

    it('cancels a called queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
        ]);

        $cancelled = $this->queueService->cancelQueue($queue, $this->officer);

        expect($cancelled->status)->toBe(QueueStatus::Cancelled);
    });

    it('throws exception for already completed queue', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Completed,
        ]);

        $this->queueService->cancelQueue($queue);
    })->throws(RuntimeException::class, 'Antrian sudah tidak aktif.');
});

describe('recallQueue', function () {
    it('updates called_at for recall', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now()->subMinutes(5),
        ]);

        $oldCalledAt = $queue->called_at;
        $recalled = $this->queueService->recallQueue($queue, $this->officer);

        expect($recalled->called_at->gt($oldCalledAt))->toBeTrue();
    });

    it('throws exception for non-called queue', function () {
        $queue = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $this->queueService->recallQueue($queue, $this->officer);
    })->throws(RuntimeException::class, 'Antrian harus dalam status dipanggil untuk recall.');
});

describe('getCurrentlyCalledQueues', function () {
    it('returns called and processing queues', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
        ]);
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Processing,
            'officer_id' => $this->officer->id,
        ]);
        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);

        $result = $this->queueService->getCurrentlyCalledQueues();

        expect($result)->toHaveCount(2);
    });

    it('filters by service_id', function () {
        $otherService = Service::factory()->create();

        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
        ]);
        Queue::factory()->create([
            'service_id' => $otherService->id,
            'status' => QueueStatus::Called,
        ]);

        $result = $this->queueService->getCurrentlyCalledQueues($this->service->id);

        expect($result)->toHaveCount(1);
    });
});

describe('getTodayStatistics', function () {
    it('returns statistics for today', function () {
        Queue::factory()->waiting()->count(2)->create([
            'service_id' => $this->service->id,
        ]);
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Completed,
            'called_at' => now()->subMinutes(10),
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);

        $stats = $this->queueService->getTodayStatistics();

        expect($stats['waiting'])->toBe(2)
            ->and($stats['completed'])->toBe(1)
            ->and($stats['total'])->toBe(3);
    });

    it('filters by service_id', function () {
        $otherService = Service::factory()->create();

        Queue::factory()->waiting()->count(2)->create([
            'service_id' => $this->service->id,
        ]);
        Queue::factory()->waiting()->create([
            'service_id' => $otherService->id,
        ]);

        $stats = $this->queueService->getTodayStatistics($this->service->id);

        expect($stats['total'])->toBe(2);
    });
});

describe('getWaitingQueues', function () {
    it('returns waiting queues ordered by priority and time', function () {
        $third = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'is_priority' => false,
            'created_at' => now()->subMinutes(2),
        ]);
        $first = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'is_priority' => true,
            'created_at' => now()->subMinute(),
        ]);
        $second = Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
            'is_priority' => false,
            'created_at' => now()->subMinutes(3),
        ]);

        $result = $this->queueService->getWaitingQueues($this->service->id);

        expect($result->pluck('id')->toArray())->toBe([$first->id, $second->id, $third->id]);
    });

    it('excludes non-waiting queues', function () {
        Queue::factory()->waiting()->create([
            'service_id' => $this->service->id,
        ]);
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
        ]);

        $result = $this->queueService->getWaitingQueues($this->service->id);

        expect($result)->toHaveCount(1);
    });
});

describe('isServiceAcceptingQueue', function () {
    it('returns true when service is active and within schedule', function () {
        $result = $this->queueService->isServiceAcceptingQueue($this->service);

        expect($result)->toBeTrue();
    });

    it('returns false when service is inactive', function () {
        $this->service->update(['is_active' => false]);

        $result = $this->queueService->isServiceAcceptingQueue($this->service);

        expect($result)->toBeFalse();
    });

    it('returns false when no schedule for today', function () {
        ServiceSchedule::where('service_id', $this->service->id)->delete();

        $result = $this->queueService->isServiceAcceptingQueue($this->service);

        expect($result)->toBeFalse();
    });
});

describe('calculateEstimatedTime', function () {
    it('calculates based on waiting count and average time', function () {
        Queue::factory()->waiting()->count(3)->create([
            'service_id' => $this->service->id,
        ]);

        $time = $this->queueService->calculateEstimatedTime($this->service);

        expect($time)->toBe(30);
    });

    it('divides by active officers count', function () {
        Queue::factory()->waiting()->count(4)->create([
            'service_id' => $this->service->id,
        ]);

        $anotherUser = User::factory()->create(['role' => UserRole::PetugasUmum]);
        Officer::factory()->create([
            'user_id' => $anotherUser->id,
            'service_id' => $this->service->id,
            'is_active' => true,
        ]);

        $time = $this->queueService->calculateEstimatedTime($this->service);

        expect($time)->toBe(20);
    });
});

describe('getRecentlyCalledQueues', function () {
    it('returns queues called within specified seconds', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now()->subSeconds(3),
        ]);

        $result = $this->queueService->getRecentlyCalledQueues(5);

        expect($result)->toHaveCount(1)
            ->and($result[0])->toHaveKeys(['number', 'counter', 'service_name', 'called_at']);
    });

    it('excludes queues called before specified seconds', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now()->subSeconds(10),
        ]);

        $result = $this->queueService->getRecentlyCalledQueues(5);

        expect($result)->toHaveCount(0);
    });

    it('excludes non-called status queues', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Processing,
            'officer_id' => $this->officer->id,
            'called_at' => now()->subSeconds(3),
        ]);

        $result = $this->queueService->getRecentlyCalledQueues(5);

        expect($result)->toHaveCount(0);
    });

    it('returns counter number from officer', function () {
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now()->subSeconds(2),
        ]);

        $result = $this->queueService->getRecentlyCalledQueues(5);

        expect($result[0]['counter'])->toBe((int) $this->officer->counter_number);
    });
});
