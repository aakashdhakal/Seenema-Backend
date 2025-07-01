<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Verified;


class AuthController extends Controller
{

    //login function
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if credentials are correct
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // If authentication passed
        $user = auth()->user();

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return token + user info
        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    //register function
    public function register(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $user->sendEmailVerificationNotification();

        // Return success response
        return response()->json(['message' => 'User registered successfully'], 201);

    }

    //logout function
    public function logout(Request $request)
    {
        Auth::logout();
        // Revoke the token that was used to authenticate the request
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Return success response
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    //refresh function
    public function refresh(Request $request)
    {

    }

    public function getAuthenticatedUser(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Return user information as JSON response
        return response()->json([
            'user' => $user,
        ], 200);
    }

    // Function to handle Google authentication 
    public function redirectToGoogle()
    {
        // Just return the URL for frontend
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->email)->first();
            if ($user) {
                // Update existing user
                $user->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'email_verified_at' => now(), // Mark email as verified
                ]);
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'password' => bcrypt(Str::random(16)), // temp password to satisfy not-null
                    'email_verified_at' => now(), // Mark email as verified
                ]);
            }

            Auth::login($user, remember: true);

            // Redirect back to frontend dashboard
            return redirect('http://localhost:3000/home');
        } catch (\Throwable $e) {
            return redirect('http://localhost:3000/login?error=GoogleLoginFailed' . $e->getMessage());
        }
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);
        // Check if user exists and the hash is correct
        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/email-verification-failed');
        }
        // Check if the email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?verified=1');
        }

        // Mark the email as verified and fire the event
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        // Redirect to the login page on your frontend with a success message
        return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?verified=1');
    }

    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();
        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return response()->json([
                'message' => 'Verification link sent!',
                'status' => 200
            ]);
        }
        return response()->json([
            'message' => 'Email already verified or user not authenticated.',
            'status' => 400
        ], 400);
    }

}
