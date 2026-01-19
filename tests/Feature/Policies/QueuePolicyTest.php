<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use App\Policies\QueuePolicy;
use App\QueueStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new QueuePolicy;
    $this->service = Service::factory()->create();
});

describe('QueuePolicy::viewAny', function () {
    it('allows anyone to view list', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeTrue();
    });
});

describe('QueuePolicy::view', function () {
    it('allows admin to view any queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->create(['service_id' => $this->service->id]);

        expect($this->policy->view($admin, $queue))->toBeTrue();
    });

    it('allows officer to view queue in their service', function () {
        $user = User::factory()->petugasUmum()->create();
        Officer::factory()->create(['user_id' => $user->id, 'service_id' => $this->service->id]);
        $queue = Queue::factory()->create(['service_id' => $this->service->id]);

        expect($this->policy->view($user, $queue))->toBeTrue();
    });

    it('denies officer to view queue in different service', function () {
        $user = User::factory()->petugasUmum()->create();
        $otherService = Service::factory()->create();
        Officer::factory()->create(['user_id' => $user->id, 'service_id' => $otherService->id]);
        $queue = Queue::factory()->create(['service_id' => $this->service->id]);

        expect($this->policy->view($user, $queue))->toBeFalse();
    });

    it('allows user to view their own queue', function () {
        $user = User::factory()->create();
        $queue = Queue::factory()->forUser($user)->create(['service_id' => $this->service->id]);

        expect($this->policy->view($user, $queue))->toBeTrue();
    });
});

describe('QueuePolicy::create', function () {
    it('allows anyone to create queue', function () {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeTrue();
    });
});

describe('QueuePolicy::update', function () {
    it('allows admin to update any queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->create(['service_id' => $this->service->id]);

        expect($this->policy->update($admin, $queue))->toBeTrue();
    });

    it('allows officer to update queue in their service', function () {
        $user = User::factory()->petugasUmum()->create();
        Officer::factory()->create(['user_id' => $user->id, 'service_id' => $this->service->id]);
        $queue = Queue::factory()->create(['service_id' => $this->service->id]);

        expect($this->policy->update($user, $queue))->toBeTrue();
    });

    it('denies masyarakat to update queue', function () {
        $user = User::factory()->create();
        $queue = Queue::factory()->forUser($user)->create(['service_id' => $this->service->id]);

        expect($this->policy->update($user, $queue))->toBeFalse();
    });
});

describe('QueuePolicy::delete', function () {
    it('allows only admin to delete queue', function () {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->petugasUmum()->create();
        $user = User::factory()->create();
        $queue = Queue::factory()->create(['service_id' => $this->service->id]);

        expect($this->policy->delete($admin, $queue))->toBeTrue()
            ->and($this->policy->delete($officer, $queue))->toBeFalse()
            ->and($this->policy->delete($user, $queue))->toBeFalse();
    });
});

describe('QueuePolicy::call', function () {
    it('allows officer to call queue in their service', function () {
        $user = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $this->service->id,
            'is_active' => true,
            'is_available' => true,
            'max_concurrent' => 1,
        ]);
        $queue = Queue::factory()->waiting()->create(['service_id' => $this->service->id]);

        expect($this->policy->call($user, $queue))->toBeTrue();
    });

    it('denies officer to call queue when at max capacity', function () {
        $user = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'service_id' => $this->service->id,
            'is_active' => true,
            'is_available' => true,
            'max_concurrent' => 1,
        ]);
        Queue::factory()->create([
            'service_id' => $this->service->id,
            'officer_id' => $officer->id,
            'status' => QueueStatus::Processing,
        ]);
        $newQueue = Queue::factory()->waiting()->create(['service_id' => $this->service->id]);

        expect($this->policy->call($user, $newQueue))->toBeFalse();
    });

    it('denies non-officer to call queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->waiting()->create(['service_id' => $this->service->id]);

        expect($this->policy->call($admin, $queue))->toBeFalse();
    });
});

describe('QueuePolicy::cancel', function () {
    it('allows admin to cancel any queue', function () {
        $admin = User::factory()->admin()->create();
        $queue = Queue::factory()->waiting()->create(['service_id' => $this->service->id]);

        expect($this->policy->cancel($admin, $queue))->toBeTrue();
    });

    it('allows user to cancel their own waiting queue', function () {
        $user = User::factory()->create();
        $queue = Queue::factory()->forUser($user)->waiting()->create(['service_id' => $this->service->id]);

        expect($this->policy->cancel($user, $queue))->toBeTrue();
    });

    it('denies user to cancel queue that is not waiting', function () {
        $user = User::factory()->create();
        $queue = Queue::factory()->forUser($user)->called()->create(['service_id' => $this->service->id]);

        expect($this->policy->cancel($user, $queue))->toBeFalse();
    });
});
