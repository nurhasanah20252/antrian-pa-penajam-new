<?php

namespace App\Providers;

use App\Services\FonnteService;
use Illuminate\Support\ServiceProvider;

class FonnteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FonnteService::class, function () {
            return new FonnteService(
                apiKey: config('services.fonnte.api_key', ''),
                baseUrl: config('services.fonnte.base_url', 'https://api.fonnte.com')
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
