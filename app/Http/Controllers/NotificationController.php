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
            'user_id' => 'required|array|min:1',
            'user_id.*' => 'exists:users,id',
            'message' => 'required|string|max:255',
            'title' => 'required|string|max:100',
        ]);

        $userIds = $request->input('user_id');
        $message = $request->input('message');
        $title = $request->input('title');

        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            $user->notify(new SimpleNotification($title, $message));
        }

        return response()->json(['message' => 'Notifications sent successfully.']);
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

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
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