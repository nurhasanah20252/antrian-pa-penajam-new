<?php

use App\Models\Officer;
use App\Models\Service;
use App\Models\User;
use App\Policies\OfficerPolicy;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new OfficerPolicy;
    $this->service = Service::factory()->create();
});

describe('OfficerPolicy::viewAny', function () {
    it('allows only admin to view officers list', function () {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->petugasUmum()->create();
        $user = User::factory()->create();

        expect($this->policy->viewAny($admin))->toBeTrue()
            ->and($this->policy->viewAny($officer))->toBeFalse()
            ->and($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('OfficerPolicy::view', function () {
    it('allows admin to view any officer', function () {
        $admin = User::factory()->admin()->create();
        $officerUser = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $officerUser->id, 'service_id' => $this->service->id]);

        expect($this->policy->view($admin, $officer))->toBeTrue();
    });

    it('allows officer to view their own profile', function () {
        $user = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $user->id, 'service_id' => $this->service->id]);

        expect($this->policy->view($user, $officer))->toBeTrue();
    });

    it('denies officer to view other officer profile', function () {
        $user1 = User::factory()->petugasUmum()->create();
        $user2 = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $user2->id, 'service_id' => $this->service->id]);

        expect($this->policy->view($user1, $officer))->toBeFalse();
    });
});

describe('OfficerPolicy::create', function () {
    it('allows only admin to create officer', function () {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->petugasUmum()->create();
        $user = User::factory()->create();

        expect($this->policy->create($admin))->toBeTrue()
            ->and($this->policy->create($officer))->toBeFalse()
            ->and($this->policy->create($user))->toBeFalse();
    });
});

describe('OfficerPolicy::update', function () {
    it('allows admin to update any officer', function () {
        $admin = User::factory()->admin()->create();
        $officerUser = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $officerUser->id, 'service_id' => $this->service->id]);

        expect($this->policy->update($admin, $officer))->toBeTrue();
    });

    it('allows officer to update their own profile', function () {
        $user = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $user->id, 'service_id' => $this->service->id]);

        expect($this->policy->update($user, $officer))->toBeTrue();
    });
});

describe('OfficerPolicy::setAvailability', function () {
    it('allows admin to set availability for any officer', function () {
        $admin = User::factory()->admin()->create();
        $officerUser = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $officerUser->id, 'service_id' => $this->service->id]);

        expect($this->policy->setAvailability($admin, $officer))->toBeTrue();
    });

    it('allows officer to set their own availability', function () {
        $user = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $user->id, 'service_id' => $this->service->id]);

        expect($this->policy->setAvailability($user, $officer))->toBeTrue();
    });

    it('denies officer to set other officer availability', function () {
        $user1 = User::factory()->petugasUmum()->create();
        $user2 = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $user2->id, 'service_id' => $this->service->id]);

        expect($this->policy->setAvailability($user1, $officer))->toBeFalse();
    });
});

describe('OfficerPolicy::delete', function () {
    it('allows only admin to delete officer', function () {
        $admin = User::factory()->admin()->create();
        $officerUser = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $officerUser->id, 'service_id' => $this->service->id]);

        expect($this->policy->delete($admin, $officer))->toBeTrue()
            ->and($this->policy->delete($officerUser, $officer))->toBeFalse();
    });
});
