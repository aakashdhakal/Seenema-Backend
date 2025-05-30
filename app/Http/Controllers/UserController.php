<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Function to get the authenticated user
    public function getAuthenticatedUser(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Return user information as JSON response
        return response()->json([
            'user' => $user,
        ], 200);
    }
}