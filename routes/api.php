<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\MembershipController;
use App\Http\Controllers\Api\Consumer\AuthController as ConsumerAuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Consumer\MembershipController as ConsumerMembershipController;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('admin.login');

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::apiResource('memberships', MembershipController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
    });
});

Route::prefix('consumer')->group(function () {
    Route::post('/register', [ConsumerAuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('consumer.register');

    Route::post('/login', [ConsumerAuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('consumer.login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/memberships', [ConsumerMembershipController::class, 'index'])
            ->name('consumer.memberships.index');
    });
});
