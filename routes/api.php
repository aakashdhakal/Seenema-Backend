<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laravel\Socialite\Facades\Socialite;

Route::get('/ping', function () {
    return response()->json([
        'message' => 'Pong from Laravel!'
    ]);
});

// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/logout', [AuthController::class, 'logout']);
// Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/user', [AuthController::class, 'getAuthenticatedUser'])->middleware('auth:sanctum');

