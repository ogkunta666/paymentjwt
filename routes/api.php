<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Test route
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working correctly',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    // Order routes
    Route::apiResource('orders', OrderController::class);

    // Payment routes
    Route::apiResource('payments', PaymentController::class);
});
