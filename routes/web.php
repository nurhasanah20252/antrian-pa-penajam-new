<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OfficerController as AdminOfficerController;
use App\Http\Controllers\Admin\QueueController as AdminQueueController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\Officer\QueueController;
use App\Http\Controllers\VoiceAnnouncementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('display', [DisplayController::class, 'index'])->name('display');
Route::get('display/voice', [VoiceAnnouncementController::class, 'show'])->name('display.voice');

Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [KioskController::class, 'index'])->name('index');
    Route::post('/', [KioskController::class, 'store'])->name('store');
    Route::get('tiket/{queue}', [KioskController::class, 'tiket'])->name('tiket');
});

Route::prefix('antrian')->name('antrian.')->group(function () {
    Route::get('daftar', [AntrianController::class, 'create'])->name('daftar');
    Route::post('daftar', [AntrianController::class, 'store'])->name('store');
    Route::get('tiket/{queue}', [AntrianController::class, 'tiket'])->name('tiket');
    Route::get('status', [AntrianController::class, 'status'])->name('status');
    Route::get('status/{number}', [AntrianController::class, 'cekStatus'])->name('cek-status');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'active', 'role:petugas_umum,petugas_posbakum,petugas_pembayaran'])
    ->prefix('officer')
    ->name('officer.')
    ->group(function () {
        Route::get('queues', [QueueController::class, 'index'])->name('queues.index');
        Route::get('queues/{queue}', [QueueController::class, 'show'])->name('queues.show');
        Route::post('queues/call-next', [QueueController::class, 'callNext'])->name('queues.call-next');
        Route::post('queues/{queue}/call', [QueueController::class, 'call'])->name('queues.call');
        Route::post('queues/{queue}/recall', [QueueController::class, 'recall'])->name('queues.recall');
        Route::post('queues/{queue}/process', [QueueController::class, 'process'])->name('queues.process');
        Route::post('queues/{queue}/complete', [QueueController::class, 'complete'])->name('queues.complete');
        Route::post('queues/{queue}/skip', [QueueController::class, 'skip'])->name('queues.skip');
        Route::post('queues/{queue}/transfer', [QueueController::class, 'transfer'])->name('queues.transfer');
    });

Route::middleware(['auth', 'active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::resource('services', ServiceController::class);
        Route::resource('officers', AdminOfficerController::class);
        Route::resource('queues', AdminQueueController::class)->only(['index', 'show', 'destroy']);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

require __DIR__.'/settings.php';
