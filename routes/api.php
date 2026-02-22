<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\JwksController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['ok' => true]));

Route::get('/.well-known/jwks.json', JwksController::class);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);

    Route::post('/email/resend-verification', [EmailVerificationController::class, 'resend']);
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::middleware('auth.jwt')->group(function () {
    Route::get('/me', MeController::class);
});
