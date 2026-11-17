<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\PixelforgeWebhookController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::middleware('auth')->group(function () {
    Route::get('/generations/create', [
        GenerationController::class,
        'create'
    ])->name('generations.create');

    Route::post('/generations/estimate', [
        GenerationController::class,
        'estimate'
    ])->name('generations.estimate');

    Route::post('/generations', [
        GenerationController::class,
        'store'
    ])->name('generations.store');

    Route::get('/generations', [GenerationController::class, 'index'])
    ->name('generations.index');
});

Route::post(
    '/webhooks/pixelforge',
    PixelforgeWebhookController::class
    )->name('webhooks.pixelforge');