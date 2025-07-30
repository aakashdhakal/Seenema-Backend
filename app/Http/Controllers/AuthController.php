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
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Make profile picture optional
        ]);

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Handle profile picture upload if it exists
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->storeAs('images/', 'public');
            $user->profile_picture = $path;
            $user->save();
        }

        $user->sendEmailVerificationNotification();
        Auth::login($user, remember: true);

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
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture,
            'role' => $user->role, // Include role in the response
            'is_email_verified' => $user->hasVerifiedEmail(),
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
            $adminEmail = env('GOOGLE_ADMIN_EMAIL');
            $appUrl = env('APP_URL', 'http://localhost:8000');
            $user = User::where('email', $googleUser->email)->first();
            if ($user) {
                // Update existing user
                $user->update([
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    "role" => $googleUser->email === $adminEmail ? 'admin' : 'user', // Set role based on email
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
                    'role' => $googleUser->email === $adminEmail ? 'admin' : 'user', // Set role based on email
                ]);
            }

            //mark email as verified
            $user->email_verified_at = now(); // Mark email as verified
            $user->save();

            // Add or update the profile picture from Google for both new and existing users
            if ($googleUser->avatar) {
                $imageContents = file_get_contents($googleUser->avatar);
                if ($imageContents) {
                    // FIX 1: Define a relative path for storing the file.
                    $relativePath = 'images/profile_pictures/' . $user->id . '_' . '.jpg';

                    // FIX 2: Use the relative path to store the file on the 'public' disk.
                    \Storage::disk('public')->put($relativePath, $imageContents);

                    // FIX 3: Store the full, publicly accessible URL in the database.
                    $user->profile_picture = \Storage::disk('public')->url($relativePath);
                    $user->save();
                }
            }

            Auth::login($user, remember: true);

            // Create a token for the user to be used by the frontend
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect back to frontend with the token
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/home');
        } catch (\Throwable $e) {
            \Log::error('Google Login Failed: ' . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=' . urlencode($e->getMessage()));
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
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/home');
        }

        // Mark the email as verified and fire the event
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        // Redirect to the login page on your frontend with a success message
        return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/home');
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
