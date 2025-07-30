<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\SimpleNotification;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Function to get the authenticated user
    public function getAuthenticatedUser(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();
        $returnedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture,
            'role' => $user->role, // Include role if needed
        ];
        // Send notification every 5 seconds (for demonstration only; not recommended in production)
        // WARNING: This will send a notification every time this endpoint is called.
        // To implement a true "every 5 seconds" notification, use a scheduled job or queue worker.
        $user->notify(new SimpleNotification(
            'Welcome to the application',
            'You have successfully logged in.'
        ));

        // Return user information as JSON response
        return response()->json([
            'user' => $returnedUser,
        ], 200);

    }

    public function getAllUsers(Request $request)
    {
        // Get all users
        $users = User::all();

        // Return users information as JSON response
        return response()->json([
            'users' => $users,
        ], 200);
    }
}