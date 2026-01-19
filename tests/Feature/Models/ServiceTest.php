<?php

use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\ServiceSchedule;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Service Model', function () {
    it('can be created with factory', function () {
        $service = Service::factory()->create();

        expect($service)->toBeInstanceOf(Service::class)
            ->and($service->id)->toBeInt()
            ->and($service->code)->toBeString()
            ->and($service->name)->toBeString()
            ->and($service->prefix)->toBeString()
            ->and($service->is_active)->toBeBool();
    });

    it('can create umum service', function () {
        $service = Service::factory()->umum()->create();

        expect($service->code)->toBe('UMUM')
            ->and($service->name)->toBe('Pelayanan Umum')
            ->and($service->prefix)->toBe('A');
    });

    it('can create posbakum service', function () {
        $service = Service::factory()->posbakum()->create();

        expect($service->code)->toBe('POSBAKUM')
            ->and($service->name)->toBe('Pos Bantuan Hukum')
            ->and($service->prefix)->toBe('P');
    });

    it('can create pembayaran service', function () {
        $service = Service::factory()->pembayaran()->create();

        expect($service->code)->toBe('BAYAR')
            ->and($service->name)->toBe('Pembayaran')
            ->and($service->prefix)->toBe('B');
    });

    it('can create inactive service', function () {
        $service = Service::factory()->inactive()->create();

        expect($service->is_active)->toBeFalse();
    });
});

describe('Service Relationships', function () {
    it('has many officers', function () {
        $service = Service::factory()->create();
        $officers = Officer::factory()->count(2)->create(['service_id' => $service->id]);

        expect($service->officers)->toHaveCount(2)
            ->and($service->officers->first())->toBeInstanceOf(Officer::class);
    });

    it('has many queues', function () {
        $service = Service::factory()->create();
        Queue::factory()->count(3)->create(['service_id' => $service->id]);

        expect($service->queues)->toHaveCount(3)
            ->and($service->queues->first())->toBeInstanceOf(Queue::class);
    });

    it('has many schedules', function () {
        $service = Service::factory()->create();

        foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
            ServiceSchedule::factory()->forDay($dayOfWeek)->create(['service_id' => $service->id]);
        }

        expect($service->schedules)->toHaveCount(5)
            ->and($service->schedules->first())->toBeInstanceOf(ServiceSchedule::class);
    });
});

describe('Service Helper Methods', function () {
    it('counts active officers correctly', function () {
        $service = Service::factory()->create();

        Officer::factory()->create([
            'service_id' => $service->id,
            'is_active' => true,
            'is_available' => true,
        ]);

        Officer::factory()->create([
            'service_id' => $service->id,
            'is_active' => true,
            'is_available' => false,
        ]);

        Officer::factory()->create([
            'service_id' => $service->id,
            'is_active' => false,
            'is_available' => true,
        ]);

        expect($service->active_officers_count)->toBe(1);
    });

    it('counts today queue correctly', function () {
        $service = Service::factory()->create();

        Queue::factory()->count(5)->create([
            'service_id' => $service->id,
            'created_at' => now(),
        ]);

        Queue::factory()->count(3)->create([
            'service_id' => $service->id,
            'created_at' => now()->subDay(),
        ]);

        expect($service->today_queue_count)->toBe(5);
    });

    it('checks availability today', function () {
        $service = Service::factory()->create([
            'is_active' => true,
            'max_daily_queue' => 10,
        ]);

        expect($service->isAvailableToday())->toBeTrue();

        Queue::factory()->count(10)->create([
            'service_id' => $service->id,
            'created_at' => now(),
        ]);

        expect($service->isAvailableToday())->toBeFalse();
    });

    it('returns unavailable when inactive', function () {
        $service = Service::factory()->inactive()->create([
            'max_daily_queue' => 100,
        ]);

        expect($service->isAvailableToday())->toBeFalse();
    });
});
