<?php
// filepath: d:\xampp\htdocs\project\backend\routes/channels.php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('admin.notifications', function ($user) {
    \Log::info('Auth check', ['user' => $user]);
    return true;
});
Broadcast::channel('test-channel', function ($user) {
    return true; // Allow all users to listen for testing purposes
});