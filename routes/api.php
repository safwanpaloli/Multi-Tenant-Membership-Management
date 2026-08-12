<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\MembershipController;
use App\Http\Controllers\Api\Consumer\AuthController as ConsumerAuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Consumer\MembershipController as ConsumerMembershipController;
use App\Http\Controllers\Api\Admin\MembershipPurchaseController;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('admin.login');

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::apiResource('memberships', MembershipController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);

        Route::get('/membership-purchases', [MembershipPurchaseController::class, 'index'])
            ->name('admin.membership-purchases.index');
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
            
        Route::get('/me/memberships', [ConsumerMembershipController::class, 'purchases'])
            ->name('consumer.memberships.purchases');
        
        Route::post('/memberships/{membership}/purchase', [ConsumerMembershipController::class, 'purchase'])
            ->name('consumer.memberships.purchase');
    });
});

Route::post('/webhooks/payments/{provider}', [\App\Http\Controllers\Api\WebhookController::class, 'handlePayment'])
    ->name('webhooks.payments');