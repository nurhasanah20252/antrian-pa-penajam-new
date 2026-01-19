<?php

use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Kiosk Index', function () {
    it('can access kiosk page', function () {
        $this->get('/kiosk')
            ->assertSuccessful();
    });

    it('shows available services', function () {
        $service = Service::factory()->create(['is_active' => true, 'name' => 'Layanan Umum']);

        $this->get('/kiosk')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('kiosk/index')
                ->has('services', 1)
            );
    });
});

describe('Kiosk Queue Registration', function () {
    it('can register for a queue from kiosk', function () {
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
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'name' => 'Kiosk User',
            'source' => 'kiosk',
        ]);
    });

    it('creates queue with kiosk source', function () {
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
            'name' => 'Test User',
            'is_priority' => true,
        ]);

        $queue = Queue::first();
        expect($queue->source)->toBe('kiosk');
        expect($queue->is_priority)->toBeTrue();
    });

    it('redirects to kiosk ticket page after registration', function () {
        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $response = $this->post('/kiosk', [
            'service_id' => $service->id,
            'name' => 'Test User',
        ]);

        $queue = Queue::first();
        $response->assertRedirect(route('kiosk.tiket', $queue));
    });

    it('requires name when registering from kiosk', function () {
        $service = Service::factory()->create(['is_active' => true]);

        $this->post('/kiosk', [
            'service_id' => $service->id,
        ])
            ->assertSessionHasErrors('name');
    });

    it('requires valid service when registering from kiosk', function () {
        $this->post('/kiosk', [
            'service_id' => 999,
            'name' => 'Test User',
        ])
            ->assertSessionHasErrors('service_id');
    });
});

describe('Kiosk Ticket', function () {
    it('can view kiosk ticket page', function () {
        $queue = Queue::factory()->create();

        $this->get(route('kiosk.tiket', $queue))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('kiosk/tiket')
                ->has('queue')
                ->has('position')
                ->has('estimated_wait')
            );
    });

    it('returns 404 for non-existent queue', function () {
        $this->get('/kiosk/tiket/999')
            ->assertNotFound();
    });
});
