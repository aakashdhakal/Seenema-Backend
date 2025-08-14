<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\SimpleNotification;

class UserController extends Controller
{
    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date|before:today',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email', 'phone', 'bio', 'gender', 'dob', 'address']);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                $oldPath = str_replace('/storage/', '', parse_url($user->profile_picture, PHP_URL_PATH));
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $file = $request->file('profile_picture');
            $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('images/profile_pictures', $filename, 'public');
            $data['profile_picture'] = Storage::disk('public')->url($path);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }

    //suspend user account
    public function changeUserStatus(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:active,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $targetUser = User::find($request->input('user_id'));
        $targetUser->status = $request->input('status');
        $targetUser->save();

        $message = $targetUser->status === 'suspended'
            ? 'Your account has been suspended by the admin. Please contact support for more information.'
            : 'Your account has been reactivated by the admin.';
        $title = $targetUser->status === 'suspended'
            ? 'Account Suspended'
            : 'Account Reactivated';

        $targetUser->notify(
            new SimpleNotification(
                $title,
                $message
            )
        );

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $targetUser
        ]);
    }

    //change role of the user
    public function changeUserRole(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:user,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $targetUser = User::find($request->input('user_id'));
        $targetUser->role = $request->input('role');
        $targetUser->save();

        $title = $targetUser->role === 'admin'
            ? 'Role Updated to Admin'
            : 'Role Updated to User';

        $message = $targetUser->role === 'admin'
            ? 'You have been granted admin privileges by the admin.'
            : 'Your admin privileges have been revoked by the admin. Please contact support for more information.';

        $targetUser->notify(
            new SimpleNotification(
                $title,
                $message
            )
        );

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $targetUser
        ]);
    }
}