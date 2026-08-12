<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Consumer\AuthController as ConsumerAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('admin.login');
});

Route::prefix('consumer')->group(function () {
    Route::post('/register', [ConsumerAuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('consumer.register');

    Route::post('/login', [ConsumerAuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('consumer.login');
});
