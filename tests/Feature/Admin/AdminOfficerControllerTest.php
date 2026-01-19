<?php

use App\Models\Officer;
use App\Models\Service;
use App\Models\User;
use App\UserRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Admin Officer Access', function () {
    it('requires authentication', function () {
        $this->get('/admin/officers')
            ->assertRedirect('/login');
    });

    it('requires admin role', function () {
        $user = User::factory()->create(['role' => UserRole::Masyarakat]);

        $this->actingAs($user)
            ->get('/admin/officers')
            ->assertForbidden();
    });

    it('forbids officer role', function () {
        $user = User::factory()->create(['role' => UserRole::PetugasUmum]);

        $this->actingAs($user)
            ->get('/admin/officers')
            ->assertForbidden();
    });

    it('allows admin to access officers index', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/officers')
            ->assertSuccessful();
    });
});

describe('Admin Officer Index', function () {
    it('displays all officers', function () {
        $admin = User::factory()->admin()->create();
        Officer::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get('/admin/officers')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/officers/index')
                ->has('officers', 3)
            );
    });
});

describe('Admin Officer Create', function () {
    it('displays create form with available users and services', function () {
        $admin = User::factory()->admin()->create();
        Service::factory()->count(2)->create();
        User::factory()->petugasUmum()->count(2)->create();

        $this->actingAs($admin)
            ->get('/admin/officers/create')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/officers/create')
                ->has('availableUsers')
                ->has('services')
            );
    });

    it('only shows users not already assigned as officers', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();
        $assignedUser = User::factory()->petugasUmum()->create();
        $unassignedUser = User::factory()->petugasUmum()->create();

        Officer::factory()->create([
            'user_id' => $assignedUser->id,
            'service_id' => $service->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin/officers/create')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/officers/create')
                ->has('availableUsers', 1)
            );
    });

    it('can create an officer', function () {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create();
        $user = User::factory()->petugasUmum()->create();

        $this->actingAs($admin)
            ->post('/admin/officers', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'counter_number' => 1,
                'is_active' => true,
                'is_available' => true,
                'max_concurrent' => 1,
            ])
            ->assertRedirect('/admin/officers')
            ->assertSessionHas('success');

        expect(Officer::where('user_id', $user->id)->exists())->toBeTrue();
    });

    it('validates required fields on create', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/officers', [])
            ->assertSessionHasErrors(['user_id', 'service_id', 'counter_number']);
    });
});

describe('Admin Officer Edit', function () {
    it('displays edit form', function () {
        $admin = User::factory()->admin()->create();
        $officer = Officer::factory()->create();

        $this->actingAs($admin)
            ->get("/admin/officers/{$officer->id}/edit")
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/officers/edit')
                ->has('officer')
                ->has('availableUsers')
                ->has('services')
            );
    });

    it('can update an officer', function () {
        $admin = User::factory()->admin()->create();
        $officer = Officer::factory()->create(['counter_number' => 1]);
        $newService = Service::factory()->create();

        $this->actingAs($admin)
            ->put("/admin/officers/{$officer->id}", [
                'user_id' => $officer->user_id,
                'service_id' => $newService->id,
                'counter_number' => 5,
                'is_active' => true,
                'is_available' => false,
                'max_concurrent' => 2,
            ])
            ->assertRedirect('/admin/officers')
            ->assertSessionHas('success');

        $officer->refresh();
        expect((int) $officer->counter_number)->toBe(5);
        expect($officer->service_id)->toBe($newService->id);
    });
});

describe('Admin Officer Delete', function () {
    it('can delete an officer', function () {
        $admin = User::factory()->admin()->create();
        $officer = Officer::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/officers/{$officer->id}")
            ->assertRedirect('/admin/officers')
            ->assertSessionHas('success');

        expect(Officer::find($officer->id))->toBeNull();
    });
});
