<?php

use App\Models\Service;
use App\Models\User;
use App\Policies\ServicePolicy;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ServicePolicy;
    $this->service = Service::factory()->create();
});

describe('ServicePolicy::viewAny', function () {
    it('allows anyone to view services list', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeTrue();
    });
});

describe('ServicePolicy::view', function () {
    it('allows anyone to view a service', function () {
        $user = User::factory()->create();

        expect($this->policy->view($user, $this->service))->toBeTrue();
    });
});

describe('ServicePolicy::create', function () {
    it('allows only admin to create service', function () {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->petugasUmum()->create();
        $user = User::factory()->create();

        expect($this->policy->create($admin))->toBeTrue()
            ->and($this->policy->create($officer))->toBeFalse()
            ->and($this->policy->create($user))->toBeFalse();
    });
});

describe('ServicePolicy::update', function () {
    it('allows only admin to update service', function () {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->petugasUmum()->create();
        $user = User::factory()->create();

        expect($this->policy->update($admin, $this->service))->toBeTrue()
            ->and($this->policy->update($officer, $this->service))->toBeFalse()
            ->and($this->policy->update($user, $this->service))->toBeFalse();
    });
});

describe('ServicePolicy::delete', function () {
    it('allows only admin to delete service', function () {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->petugasUmum()->create();
        $user = User::factory()->create();

        expect($this->policy->delete($admin, $this->service))->toBeTrue()
            ->and($this->policy->delete($officer, $this->service))->toBeFalse()
            ->and($this->policy->delete($user, $this->service))->toBeFalse();
    });
});
