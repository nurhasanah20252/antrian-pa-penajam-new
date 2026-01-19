<?php

namespace Database\Seeders;

use App\Models\Officer;
use App\Models\Service;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ServiceSeeder::class);

        User::query()->updateOrCreate(
            ['email' => 'admin@pa-penajam.go.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        $services = Service::all();
        $roleMapping = [
            'UMUM' => UserRole::PetugasUmum,
            'POSBAKUM' => UserRole::PetugasPosbakum,
            'BAYAR' => UserRole::PetugasPembayaran,
        ];

        foreach ($services as $service) {
            $role = $roleMapping[$service->code] ?? UserRole::PetugasUmum;

            for ($i = 1; $i <= 2; $i++) {
                $user = User::query()->updateOrCreate(
                    ['email' => strtolower($service->code).".petugas{$i}@pa-penajam.go.id"],
                    [
                        'name' => "Petugas {$service->name} {$i}",
                        'password' => Hash::make('password'),
                        'role' => $role,
                        'is_active' => true,
                    ]
                );

                Officer::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'service_id' => $service->id,
                        'counter_number' => (string) $i,
                        'is_active' => true,
                        'is_available' => true,
                        'max_concurrent' => 1,
                    ]
                );
            }

            for ($day = 1; $day <= 5; $day++) {
                ServiceSchedule::query()->updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'open_time' => '08:00:00',
                        'close_time' => '16:00:00',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
