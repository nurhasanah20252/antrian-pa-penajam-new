<?php

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Route;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['web', 'auth', 'role:admin'])->get('/test-admin', fn () => 'OK');
    Route::middleware(['web', 'auth', 'role:petugas_umum,petugas_posbakum,petugas_pembayaran'])->get('/test-officer', fn () => 'OK');
    Route::middleware(['web', 'auth', 'role:admin,petugas_umum'])->get('/test-multi-role', fn () => 'OK');
    Route::middleware(['web', 'auth', 'active'])->get('/test-active', fn () => 'OK');
});

describe('EnsureUserHasRole Middleware', function () {
    it('allows admin to access admin route', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/test-admin');

        $response->assertOk();
    });

    it('denies masyarakat access to admin route', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/test-admin');

        $response->assertForbidden();
    });

    it('allows officer to access officer route', function (UserRole $role) {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->get('/test-officer');

        $response->assertOk();
    })->with([
        'petugas umum' => UserRole::PetugasUmum,
        'petugas posbakum' => UserRole::PetugasPosbakum,
        'petugas pembayaran' => UserRole::PetugasPembayaran,
    ]);

    it('denies admin access to officer-only route', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/test-officer');

        $response->assertForbidden();
    });

    it('allows multiple roles access', function () {
        $admin = User::factory()->admin()->create();
        $petugasUmum = User::factory()->petugasUmum()->create();
        $masyarakat = User::factory()->create();

        $this->actingAs($admin)->get('/test-multi-role')->assertOk();
        $this->actingAs($petugasUmum)->get('/test-multi-role')->assertOk();
        $this->actingAs($masyarakat)->get('/test-multi-role')->assertForbidden();
    });

    it('redirects guests to login', function () {
        $response = $this->get('/test-admin');

        $response->assertRedirect('/login');
    });
});

describe('EnsureUserIsActive Middleware', function () {
    it('allows active user to access route', function () {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get('/test-active');

        $response->assertOk();
    });

    it('logs out inactive user and redirects to login', function () {
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($user)->get('/test-active');

        $this->assertGuest();
        $response->assertRedirect('/login');
    });
});
