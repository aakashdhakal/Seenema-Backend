<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\SimpleNotification;
use Illuminate\Http\Request;
use App\Models\User;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'send_to' => 'required|in:all,specific,role',
            'user_ids' => 'required_if:send_to,specific|array',
            'user_ids.*' => 'exists:users,id',
            'target_role' => 'required_if:send_to,role|in:user,admin',
        ]);

        $title = $request->input('title');
        $message = $request->input('message');
        $sendTo = $request->input('send_to');

        $users = collect();

        switch ($sendTo) {
            case 'all':
                $users = User::all();
                break;

            case 'specific':
                $userIds = $request->input('user_ids');
                $users = User::whereIn('id', $userIds)->get();
                break;

            case 'role':
                $targetRole = $request->input('target_role');
                $users = User::where('role', $targetRole)->get();
                break;
        }

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found to send notifications to.'], 400);
        }

        foreach ($users as $user) {
            $user->notify(new SimpleNotification($title, $message));
        }

        return response()->json([
            'message' => 'Notifications sent successfully.',
            'recipients_count' => $users->count(),
            'send_to' => $sendTo,
        ]);
    }

    public function getNotifications(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notifications = $user->notifications()->paginate(10);

        return response()->json($notifications);
    }

    public function markAsRead(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id = $request->input('id');
        if (!$id) {
            return response()->json(['message' => 'Notification ID required'], 400);
        }

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function deleteNotification(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully.']);
    }

    public function deleteAllNotifications(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->notifications()->delete();

        return response()->json(['message' => 'All notifications deleted successfully.']);
    }
}