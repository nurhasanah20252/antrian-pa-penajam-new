<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use App\QueueStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Officer Model', function () {
    it('can be created with factory', function () {
        $officer = Officer::factory()->create();

        expect($officer)->toBeInstanceOf(Officer::class)
            ->and($officer->id)->toBeInt()
            ->and($officer->counter_number)->toBeInt()
            ->and($officer->is_active)->toBeBool()
            ->and($officer->is_available)->toBeBool()
            ->and($officer->max_concurrent)->toBeInt();
    });

    it('can create posbakum officer', function () {
        $officer = Officer::factory()->forPosbakum()->create();

        expect($officer->service->code)->toBe('POSBAKUM')
            ->and($officer->user->role->value)->toBe('petugas_posbakum');
    });

    it('can create pembayaran officer', function () {
        $officer = Officer::factory()->forPembayaran()->create();

        expect($officer->service->code)->toBe('BAYAR')
            ->and($officer->user->role->value)->toBe('petugas_pembayaran');
    });

    it('can create unavailable officer', function () {
        $officer = Officer::factory()->unavailable()->create();

        expect($officer->is_available)->toBeFalse();
    });

    it('can create inactive officer', function () {
        $officer = Officer::factory()->inactive()->create();

        expect($officer->is_active)->toBeFalse();
    });
});

describe('Officer Relationships', function () {
    it('belongs to user', function () {
        $user = User::factory()->petugasUmum()->create();
        $officer = Officer::factory()->create(['user_id' => $user->id]);

        expect($officer->user)->toBeInstanceOf(User::class)
            ->and($officer->user->id)->toBe($user->id);
    });

    it('belongs to service', function () {
        $service = Service::factory()->create();
        $officer = Officer::factory()->create(['service_id' => $service->id]);

        expect($officer->service)->toBeInstanceOf(Service::class)
            ->and($officer->service->id)->toBe($service->id);
    });
});

describe('Officer Helper Methods', function () {
    it('counts current processing queues', function () {
        $officer = Officer::factory()->create();

        expect($officer->current_queue_count)->toBe(0);

        Queue::factory()->count(2)->create([
            'officer_id' => $officer->id,
            'status' => QueueStatus::Processing,
        ]);

        Queue::factory()->create([
            'officer_id' => $officer->id,
            'status' => QueueStatus::Completed,
        ]);

        $officer->refresh();
        expect($officer->current_queue_count)->toBe(2);
    });

    it('can accept queue when available and under limit', function () {
        $officer = Officer::factory()->create([
            'is_active' => true,
            'is_available' => true,
            'max_concurrent' => 2,
        ]);

        expect($officer->canAcceptQueue())->toBeTrue();

        Queue::factory()->create([
            'officer_id' => $officer->id,
            'status' => QueueStatus::Processing,
        ]);

        expect($officer->canAcceptQueue())->toBeTrue();

        Queue::factory()->create([
            'officer_id' => $officer->id,
            'status' => QueueStatus::Processing,
        ]);

        expect($officer->canAcceptQueue())->toBeFalse();
    });

    it('cannot accept queue when unavailable', function () {
        $officer = Officer::factory()->unavailable()->create([
            'is_active' => true,
            'max_concurrent' => 10,
        ]);

        expect($officer->canAcceptQueue())->toBeFalse();
    });

    it('cannot accept queue when inactive', function () {
        $officer = Officer::factory()->inactive()->create([
            'is_available' => true,
            'max_concurrent' => 10,
        ]);

        expect($officer->canAcceptQueue())->toBeFalse();
    });
});
