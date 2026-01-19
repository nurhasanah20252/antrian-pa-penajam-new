<?php

use App\Http\Controllers\Api\DisplayBoardController;
use App\Http\Controllers\Api\QueueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Queue API Routes
|--------------------------------------------------------------------------
|
| API endpoints for kiosk and online queue registration.
|
*/

Route::prefix('v1')->group(function () {
    // Public endpoints (no auth required)
    Route::get('/services', [QueueController::class, 'services'])
        ->name('api.services');

    Route::post('/queues', [QueueController::class, 'store'])
        ->name('api.queues.store');

    Route::get('/queues/{queue}', [QueueController::class, 'show'])
        ->name('api.queues.show');

    // Display board (public)
    Route::get('/display', [DisplayBoardController::class, 'index'])
        ->name('api.display.index');

    Route::get('/display/{service}', [DisplayBoardController::class, 'service'])
        ->name('api.display.service');

    // Authenticated endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/queues/{queue}/cancel', [QueueController::class, 'cancel'])
            ->name('api.queues.cancel');
    });
});
