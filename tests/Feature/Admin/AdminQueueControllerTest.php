<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use App\QueueStatus;
use App\UserRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Admin Queue Access', function () {
    it('requires authentication', function () {
        $this->get('/admin/queues')
            ->assertRedirect('/login');
    });

    it('requires admin role', function () {
        $user = User::factory()->create(['role' => UserRole::Masyarakat]);

        $this->actingAs($user)
            ->get('/admin/queues')
            ->assertForbidden();
    });

    it('forbids officer role', function () {
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);

        $this->actingAs($user)
            ->get('/admin/queues')
            ->assertForbidden();
    });

    it('allows admin to access queues index', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/queues')
            ->assertSuccessful();
    });
});

describe('Admin Queue Index', function () {
    it('displays queues with statistics', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();
        Queue::factory()->waiting()->count(2)->create(['service_id' => $service->id]);
        Queue::factory()->count(1)->create([
            'service_id' => $service->id,
            'status' => QueueStatus::Completed,
        ]);

        $this->actingAs($admin)
            ->get('/admin/queues')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/queues/index')
                ->has('queues')
                ->has('statistics')
                ->has('services')
            );
    });

    it('shows only today queues by default', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();

        Queue::factory()->create([
            'service_id' => $service->id,
            'created_at' => now(),
        ]);
        Queue::factory()->create([
            'service_id' => $service->id,
            'created_at' => now()->subDays(5),
        ]);

        $this->actingAs($admin)
            ->get('/admin/queues')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/queues/index')
                ->where('queues.data', fn ($queues) => count($queues) === 1)
            );
    });
});

describe('Admin Queue Show', function () {
    it('displays queue details with logs', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->create();

        $this->actingAs($admin)
            ->get("/admin/queues/{$queue->id}")
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/queues/show')
                ->has('queue')
            );
    });
});

describe('Admin Queue Delete', function () {
    it('can delete waiting queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->waiting()->create();

        $this->actingAs($admin)
            ->delete("/admin/queues/{$queue->id}")
            ->assertRedirect('/admin/queues')
            ->assertSessionHas('success');

        expect(Queue::find($queue->id))->toBeNull();
    });

    it('can delete completed queue', function () {
        $admin = User::factory()->admin()->create();
        $officer = Officer::factory()->create();
        $queue = Queue::factory()->completed($officer)->create();

        $this->actingAs($admin)
            ->delete("/admin/queues/{$queue->id}")
            ->assertRedirect('/admin/queues')
            ->assertSessionHas('success');

        expect(Queue::find($queue->id))->toBeNull();
    });

    it('can delete skipped queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->skipped()->create();

        $this->actingAs($admin)
            ->delete("/admin/queues/{$queue->id}")
            ->assertRedirect('/admin/queues')
            ->assertSessionHas('success');

        expect(Queue::find($queue->id))->toBeNull();
    });

    it('can delete cancelled queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->cancelled()->create();

        $this->actingAs($admin)
            ->delete("/admin/queues/{$queue->id}")
            ->assertRedirect('/admin/queues')
            ->assertSessionHas('success');

        expect(Queue::find($queue->id))->toBeNull();
    });

    it('cannot delete called queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->called()->create();

        $this->actingAs($admin)
            ->delete("/admin/queues/{$queue->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(Queue::find($queue->id))->not->toBeNull();
    });

    it('cannot delete processing queue', function () {
        $admin = User::factory()->admin()->create();
        $officer = Officer::factory()->create();
        $queue = Queue::factory()->processing($officer)->create();

        $this->actingAs($admin)
            ->delete("/admin/queues/{$queue->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(Queue::find($queue->id))->not->toBeNull();
    });
});
