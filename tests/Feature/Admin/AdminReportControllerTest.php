<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use App\QueueStatus;
use App\UserRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Admin Report Access', function () {
    it('requires authentication', function () {
        $this->get('/admin/reports')
            ->assertRedirect('/login');
    });

    it('requires admin role', function () {
        $user = User::factory()->create(['role' => UserRole::Masyarakat]);

        $this->actingAs($user)
            ->get('/admin/reports')
            ->assertForbidden();
    });

    it('forbids officer role', function () {
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);

        $this->actingAs($user)
            ->get('/admin/reports')
            ->assertForbidden();
    });

    it('allows admin to access reports', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/reports')
            ->assertSuccessful();
    });
});

describe('Admin Report Index', function () {
    it('displays reports with summary and filters', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();
        $officer = Officer::factory()->create(['service_id' => $service->id]);

        Queue::factory()->completed($officer)->count(3)->create([
            'service_id' => $service->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin/reports')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/reports/index')
                ->has('queues')
                ->has('summary')
                ->has('dailyStats')
                ->has('services')
                ->has('filters')
            );
    });

    it('can filter by date range', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();

        Queue::factory()->create([
            'service_id' => $service->id,
            'created_at' => now()->subDays(5),
        ]);
        Queue::factory()->create([
            'service_id' => $service->id,
            'created_at' => now()->subDays(60),
        ]);

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $this->actingAs($admin)
            ->get("/admin/reports?start_date={$startDate}&end_date={$endDate}")
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/reports/index')
                ->where('queues.data', fn ($queues) => count($queues) === 1)
            );
    });

    it('can filter by service', function () {
        $admin = User::factory()->admin()->create();
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();

        Queue::factory()->count(2)->create(['service_id' => $service1->id]);
        Queue::factory()->count(3)->create(['service_id' => $service2->id]);

        $this->actingAs($admin)
            ->get("/admin/reports?service_id={$service1->id}")
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/reports/index')
                ->where('queues.data', fn ($queues) => count($queues) === 2)
            );
    });
});

describe('Admin Report Export', function () {
    it('can export CSV', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();
        Queue::factory()->count(2)->create(['service_id' => $service->id]);

        $response = $this->actingAs($admin)
            ->get('/admin/reports/export')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        expect($response->headers->get('Content-Disposition'))
            ->toContain('attachment')
            ->toContain('.csv');
    });

    it('exports filtered data', function () {
        $admin = User::factory()->admin()->create();
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();

        Queue::factory()->count(2)->create(['service_id' => $service1->id]);
        Queue::factory()->count(3)->create(['service_id' => $service2->id]);

        $response = $this->actingAs($admin)
            ->get("/admin/reports/export?service_id={$service1->id}")
            ->assertSuccessful();

        $content = $response->getContent();
        $lines = explode("\n", trim($content));

        expect(count($lines))->toBe(3);
    });

    it('requires authentication for export', function () {
        $this->get('/admin/reports/export')
            ->assertRedirect('/login');
    });

    it('requires admin role for export', function () {
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);

        $this->actingAs($user)
            ->get('/admin/reports/export')
            ->assertForbidden();
    });
});
