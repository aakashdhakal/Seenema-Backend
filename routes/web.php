<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the API!'
    ]);
});

Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

//test email templete
Route::get('/test-email', function () {
    return view('vendor.notifications.email', [
        'greeting' => 'Welcome to StreamFlow!',
        'level' => 'success', // or 'error'
        'introLines' => [
            'Thank you for joining our streaming platform.',
            'Your account has been successfully created and verified.',
            'You can now start uploading and sharing your amazing videos with the world.'
        ],
        'actionText' => 'Get Started',
        'actionUrl' => url('/dashboard'),
        'outroLines' => [
            'If you have any questions or need assistance, our support team is here to help.',
            'Start your streaming journey today and connect with your audience.'
        ],
        'salutation' => 'Happy streaming!'
    ]);
});

