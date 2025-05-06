<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('jwt.auth')->group(function () {
    // User profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Server management
    Route::get('/servers', [ServerController::class, 'index']);
    Route::get('/servers/recommended', [ServerController::class, 'getRecommended']);
    
    // Connection management
    Route::post('/connections', [ConnectionController::class, 'connect']);
    Route::put('/connections/{id}', [ConnectionController::class, 'disconnect']);
    Route::get('/connections', [ConnectionController::class, 'getUserConnections']);
    
    // Subscription management
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'subscribe']);
    Route::get('/subscriptions/active', [SubscriptionController::class, 'getActive']);
});

// Admin routes
Route::middleware(['jwt.auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/servers', [AdminController::class, 'servers']);
    Route::post('/servers', [AdminController::class, 'createServer']);
    Route::get('/connections', [AdminController::class, 'connections']);
    Route::get('/stats', [AdminController::class, 'stats']);
});
