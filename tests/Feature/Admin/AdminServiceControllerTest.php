<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use App\UserRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Admin Service Access', function () {
    it('requires authentication', function () {
        $this->get('/admin/services')
            ->assertRedirect('/login');
    });

    it('requires admin role', function () {
        $user = User::factory()->create(['role' => UserRole::Masyarakat]);

        $this->actingAs($user)
            ->get('/admin/services')
            ->assertForbidden();
    });

    it('forbids officer role', function () {
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);

        $this->actingAs($user)
            ->get('/admin/services')
            ->assertForbidden();
    });

    it('allows admin to access services index', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/services')
            ->assertSuccessful();
    });
});

describe('Admin Service Index', function () {
    it('displays all services with counts', function () {
        $admin = User::factory()->admin()->create();
        Service::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get('/admin/services')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/services/index')
                ->has('services', 3)
            );
    });
});

describe('Admin Service Create', function () {
    it('displays create form', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/services/create')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('admin/services/create'));
    });

    it('can create a service', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/services', [
                'code' => 'TEST',
                'name' => 'Test Service',
                'prefix' => 'T',
                'description' => 'Test description',
                'average_time' => 15,
                'max_daily_queue' => 100,
                'is_active' => true,
                'requires_documents' => false,
                'sort_order' => 1,
            ])
            ->assertRedirect('/admin/services')
            ->assertSessionHas('success');

        expect(Service::where('code', 'TEST')->exists())->toBeTrue();
    });

    it('validates required fields on create', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/services', [])
            ->assertSessionHasErrors(['code', 'name', 'prefix']);
    });

    it('validates unique code', function () {
        $admin = User::factory()->admin()->create();
        Service::factory()->create(['code' => 'EXIST']);

        $this->actingAs($admin)
            ->post('/admin/services', [
                'code' => 'EXIST',
                'name' => 'Test Service',
                'prefix' => 'T',
            ])
            ->assertSessionHasErrors(['code']);
    });
});

describe('Admin Service Edit', function () {
    it('displays edit form', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->get("/admin/services/{$service->id}/edit")
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/services/edit')
                ->has('service')
            );
    });

    it('can update a service', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create(['name' => 'Old Name']);

        $this->actingAs($admin)
            ->put("/admin/services/{$service->id}", [
                'code' => $service->code,
                'name' => 'New Name',
                'prefix' => $service->prefix,
                'description' => 'Updated description',
                'average_time' => 20,
                'max_daily_queue' => 150,
                'is_active' => true,
                'requires_documents' => true,
                'sort_order' => 2,
            ])
            ->assertRedirect('/admin/services')
            ->assertSessionHas('success');

        $service->refresh();
        expect($service->name)->toBe('New Name');
    });

    it('validates unique code on update excluding self', function () {
        $admin = User::factory()->admin()->create();
        $service1 = Service::factory()->create(['code' => 'CODE1']);
        $service2 = Service::factory()->create(['code' => 'CODE2']);

        $this->actingAs($admin)
            ->put("/admin/services/{$service2->id}", [
                'code' => 'CODE1',
                'name' => 'Test',
                'prefix' => 'T',
            ])
            ->assertSessionHasErrors(['code']);

        $this->actingAs($admin)
            ->put("/admin/services/{$service1->id}", [
                'code' => 'CODE1',
                'name' => 'Updated',
                'prefix' => 'X',
            ])
            ->assertRedirect();
    });
});

describe('Admin Service Delete', function () {
    it('can delete a service without queues or officers', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/services/{$service->id}")
            ->assertRedirect('/admin/services')
            ->assertSessionHas('success');

        expect(Service::find($service->id))->toBeNull();
    });

    it('cannot delete service with existing queues', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();
        Queue::factory()->create(['service_id' => $service->id]);

        $this->actingAs($admin)
            ->delete("/admin/services/{$service->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(Service::find($service->id))->not->toBeNull();
    });

    it('cannot delete service with existing officers', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();
        Officer::factory()->create(['service_id' => $service->id]);

        $this->actingAs($admin)
            ->delete("/admin/services/{$service->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(Service::find($service->id))->not->toBeNull();
    });
});
