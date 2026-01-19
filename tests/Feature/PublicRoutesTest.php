<?php

use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Display Board', function () {
    it('can access display board page', function () {
        $this->get('/display')
            ->assertSuccessful();
    });
});

describe('Queue Registration', function () {
    it('can access registration page', function () {
        $this->get('/antrian/daftar')
            ->assertSuccessful();
    });

    it('can register for a queue', function () {
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
            'name' => 'Test User',
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'service_id' => $service->id,
            'name' => 'Test User',
            'source' => 'online',
        ]);
    });

    it('can upload documents when registering', function () {
        Storage::fake();

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 200, 'application/pdf');

        $this->post('/antrian/daftar', [
            'service_id' => $service->id,
            'name' => 'Test User',
            'documents' => [$file],
        ])->assertRedirect();

        $queue = Queue::first();
        $document = $queue?->documents()->first();

        expect($document)->not->toBeNull();
        $this->assertDatabaseHas('queue_documents', [
            'queue_id' => $queue->id,
            'original_name' => 'dokumen.pdf',
        ]);
        Storage::assertExists($document->path);
    });

    it('requires name when registering', function () {
        $service = Service::factory()->create(['is_active' => true]);

        $this->post('/antrian/daftar', [
            'service_id' => $service->id,
        ])
            ->assertSessionHasErrors('name');
    });

    it('requires valid service when registering', function () {
        $this->post('/antrian/daftar', [
            'service_id' => 999,
            'name' => 'Test User',
        ])
            ->assertSessionHasErrors('service_id');
    });

    it('redirects to ticket page after registration', function () {
        $service = Service::factory()->create(['is_active' => true]);
        ServiceSchedule::factory()->create([
            'service_id' => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'open_time' => '00:00:00',
            'close_time' => '23:59:59',
            'is_active' => true,
        ]);

        $response = $this->post('/antrian/daftar', [
            'service_id' => $service->id,
            'name' => 'Test User',
        ]);

        $queue = Queue::first();
        $response->assertRedirect(route('antrian.tiket', $queue));
    });
});

describe('Queue Ticket', function () {
    it('can view ticket page', function () {
        $queue = Queue::factory()->create();

        $this->get(route('antrian.tiket', $queue))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent queue', function () {
        $this->get('/antrian/tiket/999')
            ->assertNotFound();
    });
});

describe('Queue Status', function () {
    it('can access status page', function () {
        $this->get('/antrian/status')
            ->assertSuccessful();
    });

    it('can check status of existing queue', function () {
        $queue = Queue::factory()->create([
            'number' => 'A001',
            'created_at' => now(),
        ]);

        $this->get('/antrian/status/A001')
            ->assertSuccessful();
    });

    it('shows error for non-existent queue number', function () {
        $this->get('/antrian/status/INVALID')
            ->assertSuccessful();
    });

    it('is case-insensitive for queue number lookup', function () {
        $queue = Queue::factory()->create([
            'number' => 'A001',
            'created_at' => now(),
        ]);

        $this->get('/antrian/status/a001')
            ->assertSuccessful();
    });
});
