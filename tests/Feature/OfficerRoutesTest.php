<?php

use App\UserRole;
use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Officer Dashboard Access', function () {
    it('requires authentication', function () {
        $this->get('/officer/queues')
            ->assertRedirect('/login');
    });

    it('requires officer role', function () {
        $user = User::factory()->create(['role' => UserRole::Masyarakat]);

        $this->actingAs($user)
            ->get('/officer/queues')
            ->assertForbidden();
    });

    it('allows officer to access dashboard', function () {
        $service = Service::factory()->create();
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
        Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/officer/queues')
            ->assertSuccessful();
    });
});

describe('Officer Queue Show', function () {
    it('requires authentication', function () {
        $queue = Queue::factory()->create();

        $this->get("/officer/queues/{$queue->id}")
            ->assertRedirect('/login');
    });

    it('allows officer to view queue detail', function () {
        $service = Service::factory()->create();
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);
        $queue = Queue::factory()->create([
            'service_id' => $service->id,
            'officer_id' => $officer->id,
        ]);

        $this->actingAs($user)
            ->get("/officer/queues/{$queue->id}")
            ->assertSuccessful();
    });
});

describe('Officer Queue Actions', function () {
    it('can call next queue', function () {
        $service = Service::factory()->create();
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
        Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);
        Queue::factory()->waiting()->create(['service_id' => $service->id]);

        $this->actingAs($user)
            ->post('/officer/queues/call-next')
            ->assertRedirect();
    });

    it('can process called queue', function () {
        $service = Service::factory()->create();
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);
        $queue = Queue::factory()->called()->create([
            'service_id' => $service->id,
            'officer_id' => $officer->id,
        ]);

        $this->actingAs($user)
            ->post("/officer/queues/{$queue->id}/process")
            ->assertRedirect();

        $queue->refresh();
        expect($queue->status->value)->toBe('processing');
    });

    it('can complete processing queue', function () {
        $service = Service::factory()->create();
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);
        $queue = Queue::factory()->processing($officer)->create([
            'service_id' => $service->id,
        ]);

        $this->actingAs($user)
            ->post("/officer/queues/{$queue->id}/complete")
            ->assertRedirect();

        $queue->refresh();
        expect($queue->status->value)->toBe('completed');
    });

    it('can skip queue', function () {
        $service = Service::factory()->create();
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'is_active' => true,
        ]);
        $queue = Queue::factory()->called()->create([
            'service_id' => $service->id,
            'officer_id' => $officer->id,
        ]);

        $this->actingAs($user)
            ->post("/officer/queues/{$queue->id}/skip")
            ->assertRedirect();

        $queue->refresh();
        expect($queue->status->value)->toBe('skipped');
    });
});
