<?php

use App\Http\Controllers\VideoController;
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
Route::post('/upload-video', [VideoController::class, 'videoUpload']);
Route::get('/video/{id}', [VideoController::class, 'getVideo']);
Route::get('/video/', [VideoController::class, 'getVideoSegments']);
Route::get('/init/{id}', [VideoController::class, 'getInit']);
Route::get('/segment/{videoId}/{segment}', [VideoController::class, 'getSegment']);
Route::get('/manifest/{videoId}', [VideoController::class, 'getManifest']);
