<?php

use App\Events\DisplayBoardUpdatedEvent;
use App\Events\QueueCalledEvent;
use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\QueueStatus;
use App\Services\QueueService;
use App\UserRole;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->service = Service::factory()->create([
        'prefix' => 'A',
        'is_active' => true,
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

describe('QueueCalledEvent', function () {
    it('broadcasts on display channel', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now(),
        ]);

        $event = new QueueCalledEvent($queue);

        expect($event->broadcastOn())->toHaveCount(1);
        expect($event->broadcastOn()[0])->toBeInstanceOf(Channel::class);
        expect($event->broadcastOn()[0]->name)->toBe('display');
    });

    it('uses custom broadcast name', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now(),
        ]);

        $event = new QueueCalledEvent($queue);

        expect($event->broadcastAs())->toBe('queue.called');
    });

    it('broadcasts correct data', function () {
        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now(),
            'number' => 'A001',
        ]);

        $event = new QueueCalledEvent($queue);
        $data = $event->broadcastWith();

        expect($data)->toHaveKeys(['number', 'counter', 'service_name', 'called_at']);
        expect($data['number'])->toBe('A001');
        expect($data['counter'])->toBe(1);
        expect($data['service_name'])->toBe($this->service->name);
    });
});

describe('DisplayBoardUpdatedEvent', function () {
    it('broadcasts on display channel', function () {
        $event = new DisplayBoardUpdatedEvent;

        expect($event->broadcastOn())->toHaveCount(1);
        expect($event->broadcastOn()[0])->toBeInstanceOf(Channel::class);
        expect($event->broadcastOn()[0]->name)->toBe('display');
    });

    it('uses custom broadcast name', function () {
        $event = new DisplayBoardUpdatedEvent;

        expect($event->broadcastAs())->toBe('display.updated');
    });

    it('broadcasts statistics data', function () {
        Queue::factory()->count(3)->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Waiting,
        ]);

        $event = new DisplayBoardUpdatedEvent;
        $data = $event->broadcastWith();

        expect($data)->toHaveKeys(['statistics', 'last_updated']);
        expect($data['statistics'])->toHaveKeys([
            'waiting',
            'called',
            'processing',
            'completed',
            'skipped',
            'cancelled',
            'total',
            'average_wait_time',
            'average_service_time',
        ]);
        expect($data['statistics']['waiting'])->toBe(3);
        expect($data['statistics']['total'])->toBe(3);
    });
});

describe('QueueService broadcasts events', function () {
    it('dispatches events when callNextQueue is called', function () {
        Event::fake([QueueCalledEvent::class, DisplayBoardUpdatedEvent::class]);

        Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Waiting,
        ]);

        $this->queueService->callNextQueue($this->officer);

        Event::assertDispatched(QueueCalledEvent::class);
        Event::assertDispatched(DisplayBoardUpdatedEvent::class);
    });

    it('dispatches events when callQueue is called', function () {
        Event::fake([QueueCalledEvent::class, DisplayBoardUpdatedEvent::class]);

        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Waiting,
        ]);

        $this->queueService->callQueue($queue, $this->officer);

        Event::assertDispatched(QueueCalledEvent::class);
        Event::assertDispatched(DisplayBoardUpdatedEvent::class);
    });

    it('dispatches events when recallQueue is called', function () {
        Event::fake([QueueCalledEvent::class, DisplayBoardUpdatedEvent::class]);

        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Called,
            'officer_id' => $this->officer->id,
            'called_at' => now()->subMinutes(1),
        ]);

        $this->queueService->recallQueue($queue, $this->officer);

        Event::assertDispatched(QueueCalledEvent::class);
        Event::assertDispatched(DisplayBoardUpdatedEvent::class);
    });

    it('dispatches QueueCalledEvent with correct queue data', function () {
        Event::fake([QueueCalledEvent::class, DisplayBoardUpdatedEvent::class]);

        $queue = Queue::factory()->create([
            'service_id' => $this->service->id,
            'status' => QueueStatus::Waiting,
            'number' => 'A001',
        ]);

        $this->queueService->callQueue($queue, $this->officer);

        Event::assertDispatched(QueueCalledEvent::class, function ($event) {
            return $event->queueNumber === 'A001'
                && $event->counterNumber === 1
                && $event->serviceName === $this->service->name;
        });
    });

    it('does not dispatch events when no queue is available', function () {
        Event::fake([QueueCalledEvent::class, DisplayBoardUpdatedEvent::class]);

        $result = $this->queueService->callNextQueue($this->officer);

        expect($result)->toBeNull();
        Event::assertNotDispatched(QueueCalledEvent::class);
        Event::assertNotDispatched(DisplayBoardUpdatedEvent::class);
    });
});
