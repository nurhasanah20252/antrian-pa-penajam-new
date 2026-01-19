<?php

use App\Models\User;
use App\UserRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Login', function () {
    it('can render login page', function () {
        $response = $this->get('/login');

        $response->assertOk();
    });

    it('can login with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    });

    it('cannot login with invalid credentials', function () {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    });
});

describe('Role-based Login Redirect', function () {
    it('redirects admin to admin dashboard', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
    });

    it('redirects petugas umum to officer dashboard', function () {
        $officer = User::factory()->petugasUmum()->create();

        $response = $this->post('/login', [
            'email' => $officer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/officer/dashboard');
    });

    it('redirects petugas posbakum to officer dashboard', function () {
        $officer = User::factory()->petugasPosbakum()->create();

        $response = $this->post('/login', [
            'email' => $officer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/officer/dashboard');
    });

    it('redirects petugas pembayaran to officer dashboard', function () {
        $officer = User::factory()->petugasPembayaran()->create();

        $response = $this->post('/login', [
            'email' => $officer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/officer/dashboard');
    });

    it('redirects masyarakat to dashboard', function () {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    });
});

describe('Logout', function () {
    it('can logout', function () {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    });
});
