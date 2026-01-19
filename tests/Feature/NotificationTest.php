<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;
use App\Notifications\QueueApproachingNotification;
use App\Notifications\QueueCalledNotification;
use App\Notifications\QueueRegisteredNotification;
use App\Services\QueueService;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('QueueRegisteredNotification', function () {
    it('sends email notification when notify_email is true', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $queueService = app(QueueService::class);

        $queue = $queueService->createQueue($service, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'notify_email' => true,
        ]);

        Notification::assertSentTo($queue, QueueRegisteredNotification::class);
    });

    it('does not send email notification when notify_email is false', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $queueService = app(QueueService::class);

        $queue = $queueService->createQueue($service, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'notify_email' => false,
        ]);

        Notification::assertNotSentTo($queue, QueueRegisteredNotification::class);
    });

    it('does not send email notification when email is null', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $queueService = app(QueueService::class);

        $queue = $queueService->createQueue($service, [
            'name' => 'Test User',
            'email' => null,
            'notify_email' => true,
        ]);

        Notification::assertNotSentTo($queue, QueueRegisteredNotification::class);
    });

    it('includes correct data in registered notification', function () {
        $queue = Queue::factory()->withEmailNotification()->create([
            'number' => 'A001',
        ]);

        $notification = new QueueRegisteredNotification($queue);

        $mailMessage = $notification->toMail($queue);

        expect($mailMessage->subject)->toContain('A001');
        expect($mailMessage->greeting)->toContain($queue->name);
    });

    it('returns correct array data for database notification', function () {
        $queue = Queue::factory()->create(['number' => 'A001']);

        $notification = new QueueRegisteredNotification($queue);

        $data = $notification->toArray($queue);

        expect($data)->toHaveKey('queue_id', $queue->id);
        expect($data)->toHaveKey('queue_number', 'A001');
        expect($data)->toHaveKey('type', 'queue_registered');
    });
});

describe('QueueCalledNotification', function () {
    it('sends email notification when queue is called', function () {
        Notification::fake();

        $queue = Queue::factory()->waiting()->withEmailNotification()->create();
        $officer = Officer::factory()->create([
            'service_id' => $queue->service_id,
        ]);

        $queueService = app(QueueService::class);
        $queueService->callQueue($queue, $officer);

        Notification::assertSentTo($queue->fresh(), QueueCalledNotification::class);
    });

    it('does not send duplicate notifications on recall', function () {
        Notification::fake();

        $queue = Queue::factory()->waiting()->withEmailNotification()->create();
        $officer = Officer::factory()->create([
            'service_id' => $queue->service_id,
        ]);

        $queueService = app(QueueService::class);
        $calledQueue = $queueService->callQueue($queue, $officer);

        Notification::assertSentToTimes($calledQueue, QueueCalledNotification::class, 1);

        $queueService->recallQueue($calledQueue, $officer);

        Notification::assertSentToTimes($calledQueue->fresh(), QueueCalledNotification::class, 1);
    });

    it('updates notified_called_at timestamp when notification is sent', function () {
        Notification::fake();

        $queue = Queue::factory()->waiting()->withEmailNotification()->create();
        $officer = Officer::factory()->create([
            'service_id' => $queue->service_id,
        ]);

        expect($queue->notified_called_at)->toBeNull();

        $queueService = app(QueueService::class);
        $calledQueue = $queueService->callQueue($queue, $officer);

        expect($calledQueue->notified_called_at)->not->toBeNull();
    });

    it('includes counter name in notification when officer has counter', function () {
        $queue = Queue::factory()->withEmailNotification()->create();

        $notification = new QueueCalledNotification($queue, 'Loket 5');

        $mailMessage = $notification->toMail($queue);

        expect($mailMessage->introLines)->toContain('Silakan menuju loket di Loket 5 segera.');
    });

    it('returns correct array data for called notification', function () {
        $queue = Queue::factory()->create(['number' => 'B002']);

        $notification = new QueueCalledNotification($queue, 'Loket 3');

        $data = $notification->toArray($queue);

        expect($data)->toHaveKey('queue_id', $queue->id);
        expect($data)->toHaveKey('queue_number', 'B002');
        expect($data)->toHaveKey('counter_name', 'Loket 3');
        expect($data)->toHaveKey('type', 'queue_called');
    });
});

describe('QueueApproachingNotification', function () {
    it('sends approaching notification to queues near the front', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        $officer = Officer::factory()->create([
            'service_id' => $service->id,
        ]);

        $queues = [];
        for ($i = 0; $i < 10; $i++) {
            $queues[] = Queue::factory()->waiting()->withEmailNotification()->create([
                'service_id' => $service->id,
                'created_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $queueService = app(QueueService::class);
        $queueService->callNextQueue($officer);

        Notification::assertSentTo($queues[3]->fresh(), QueueApproachingNotification::class);
        Notification::assertSentTo($queues[4]->fresh(), QueueApproachingNotification::class);
        Notification::assertSentTo($queues[5]->fresh(), QueueApproachingNotification::class);
        Notification::assertSentTo($queues[6]->fresh(), QueueApproachingNotification::class);
    });

    it('does not send approaching notification to queues without email', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        $officer = Officer::factory()->create([
            'service_id' => $service->id,
        ]);

        $firstQueue = Queue::factory()->waiting()->create([
            'service_id' => $service->id,
            'created_at' => now()->subMinutes(10),
        ]);

        $approachingQueue = Queue::factory()->waiting()->create([
            'service_id' => $service->id,
            'email' => null,
            'notify_email' => true,
            'created_at' => now()->subMinutes(5),
        ]);

        $queueService = app(QueueService::class);
        $queueService->callNextQueue($officer);

        Notification::assertNotSentTo($approachingQueue, QueueApproachingNotification::class);
    });

    it('updates notified_approaching_at timestamp', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        $officer = Officer::factory()->create([
            'service_id' => $service->id,
        ]);

        for ($i = 0; $i < 6; $i++) {
            Queue::factory()->waiting()->create([
                'service_id' => $service->id,
                'created_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $approachingQueue = Queue::factory()->waiting()->withEmailNotification()->create([
            'service_id' => $service->id,
            'created_at' => now()->subMinutes(4),
        ]);

        expect($approachingQueue->notified_approaching_at)->toBeNull();

        $queueService = app(QueueService::class);
        $queueService->callNextQueue($officer);

        expect($approachingQueue->fresh()->notified_approaching_at)->not->toBeNull();
    });

    it('does not send duplicate approaching notifications', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        $officer = Officer::factory()->create([
            'service_id' => $service->id,
        ]);

        for ($i = 0; $i < 6; $i++) {
            Queue::factory()->waiting()->create([
                'service_id' => $service->id,
                'created_at' => now()->subMinutes(20 - $i),
            ]);
        }

        $approachingQueue = Queue::factory()->waiting()->withEmailNotification()->create([
            'service_id' => $service->id,
            'notified_approaching_at' => now()->subMinutes(5),
            'created_at' => now()->subMinutes(14),
        ]);

        $queueService = app(QueueService::class);
        $queueService->callNextQueue($officer);

        Notification::assertNotSentTo($approachingQueue, QueueApproachingNotification::class);
    });

    it('includes position in approaching notification', function () {
        $queue = Queue::factory()->create(['number' => 'C003']);

        $notification = new QueueApproachingNotification($queue, 5);

        $mailMessage = $notification->toMail($queue);

        expect($mailMessage->introLines)->toContain('**Posisi:** 5 antrian lagi');
    });

    it('returns correct array data for approaching notification', function () {
        $queue = Queue::factory()->create(['number' => 'C003']);

        $notification = new QueueApproachingNotification($queue, 3);

        $data = $notification->toArray($queue);

        expect($data)->toHaveKey('queue_id', $queue->id);
        expect($data)->toHaveKey('queue_number', 'C003');
        expect($data)->toHaveKey('position_ahead', 3);
        expect($data)->toHaveKey('type', 'queue_approaching');
    });
});

describe('Queue Model Notification Methods', function () {
    it('shouldNotifyByEmail returns true when both email and notify_email are set', function () {
        $queue = Queue::factory()->create([
            'email' => 'test@example.com',
            'notify_email' => true,
        ]);

        expect($queue->shouldNotifyByEmail())->toBeTrue();
    });

    it('shouldNotifyByEmail returns false when email is null', function () {
        $queue = Queue::factory()->create([
            'email' => null,
            'notify_email' => true,
        ]);

        expect($queue->shouldNotifyByEmail())->toBeFalse();
    });

    it('shouldNotifyByEmail returns false when notify_email is false', function () {
        $queue = Queue::factory()->create([
            'email' => 'test@example.com',
            'notify_email' => false,
        ]);

        expect($queue->shouldNotifyByEmail())->toBeFalse();
    });

    it('routeNotificationForMail returns email when set', function () {
        $queue = Queue::factory()->create([
            'email' => 'queue@example.com',
        ]);

        expect($queue->routeNotificationForMail())->toBe('queue@example.com');
    });

    it('routeNotificationForMail returns null when email is not set', function () {
        $queue = Queue::factory()->create([
            'email' => null,
        ]);

        expect($queue->routeNotificationForMail())->toBeNull();
    });
});

describe('Queue Registration with Notifications', function () {
    it('can register queue with email notification preference via online form', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $this->post('/antrian/daftar', [
            'service_id' => $service->id,
            'name' => 'Online User',
            'email' => 'online@example.com',
            'notify_email' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'name' => 'Online User',
            'email' => 'online@example.com',
            'notify_email' => true,
            'source' => 'online',
        ]);

        $queue = Queue::where('email', 'online@example.com')->first();
        Notification::assertSentTo($queue, QueueRegisteredNotification::class);
    });

    it('can register queue with email notification preference via kiosk', function () {
        Notification::fake();

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $this->post('/kiosk', [
            'service_id' => $service->id,
            'name' => 'Kiosk User',
            'email' => 'kiosk@example.com',
            'notify_email' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'name' => 'Kiosk User',
            'email' => 'kiosk@example.com',
            'notify_email' => true,
            'source' => 'kiosk',
        ]);

        $queue = Queue::where('email', 'kiosk@example.com')->first();
        Notification::assertSentTo($queue, QueueRegisteredNotification::class);
    });

    it('validates email format when provided', function () {
        $service = Service::factory()->create(['is_active' => true]);

        $this->post('/antrian/daftar', [
            'service_id' => $service->id,
            'name' => 'Test User',
            'email' => 'invalid-email',
        ])->assertSessionHasErrors('email');
    });
});

describe('Transfer Queue Preserves Notification Preferences', function () {
    it('preserves notification preferences when queue is transferred', function () {
        Notification::fake();

        $sourceService = Service::factory()->create(['is_active' => true]);
        $targetService = Service::factory()->create(['is_active' => true]);

        $queue = Queue::factory()->waiting()->create([
            'service_id' => $sourceService->id,
            'email' => 'transfer@example.com',
            'notify_email' => true,
            'notify_sms' => false,
        ]);

        $officer = Officer::factory()->create([
            'service_id' => $sourceService->id,
        ]);

        $queueService = app(QueueService::class);
        $newQueue = $queueService->transferQueue($queue, $targetService, $officer, 'Transfer test');

        expect($newQueue->email)->toBe('transfer@example.com');
        expect($newQueue->notify_email)->toBeTrue();
        expect($newQueue->notify_sms)->toBeFalse();
    });
});
