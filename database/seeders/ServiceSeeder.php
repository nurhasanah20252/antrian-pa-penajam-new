<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'UMUM',
                'name' => 'Pelayanan Umum',
                'description' => 'Layanan umum untuk berbagai keperluan administrasi pengadilan seperti pendaftaran perkara, pengambilan akta, dan informasi umum',
                'prefix' => 'A',
                'average_time' => 15,
                'max_daily_queue' => 100,
                'is_active' => true,
                'requires_documents' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'POSBAKUM',
                'name' => 'Pos Bantuan Hukum',
                'description' => 'Layanan konsultasi hukum gratis untuk masyarakat tidak mampu',
                'prefix' => 'P',
                'average_time' => 30,
                'max_daily_queue' => 50,
                'is_active' => true,
                'requires_documents' => false,
                'sort_order' => 2,
            ],
            [
                'code' => 'BAYAR',
                'name' => 'Pembayaran',
                'description' => 'Layanan pembayaran biaya perkara dan administrasi lainnya',
                'prefix' => 'B',
                'average_time' => 10,
                'max_daily_queue' => 80,
                'is_active' => true,
                'requires_documents' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
