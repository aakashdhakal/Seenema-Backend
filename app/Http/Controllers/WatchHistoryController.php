<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchHistoryController extends Controller
{

    public function getHistory(Request $request)
    {
        $user = Auth::user();

        $history = WatchHistory::where('user_id', $user->id)
            ->with([
                'video' => function ($query) {
                    // Select only the necessary fields from the video model
                    $query->select('id', 'title', 'slug', 'thumbnail_path', 'duration');
                }
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(15); // Paginate with 15 items per page

        return response()->json($history);
    }


    public function getResumePoint($videoId)
    {
        $user = Auth::user();

        $history = WatchHistory::where('user_id', $user->id)
            ->where('video_id', $videoId)
            ->first();

        return response()->json([
            'watched_duration' => $history ? $history->watched_duration : 0,
        ]);
    }


    public function updateProgress(Request $request, $videoId)
    {
        $request->validate([
            'currentTime' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $video = Video::findOrFail($videoId);
        $currentTime = $request->input('currentTime');

        // Use updateOrCreate to handle both new and existing records efficiently
        $history = WatchHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'video_id' => $video->id,
            ],
            [
                'watched_duration' => $currentTime,
            ]
        );

        // Check if the video is considered finished
        // (e.g., watched duration is within 10 seconds of the total duration)
        if ($video->duration > 0 && ($video->duration - $currentTime) < 10) {
            $history->finished_at = now();
            $history->save();
        }

        return response()->json([
            'message' => 'Watch progress updated successfully.',
            'watched_duration' => $history->watched_duration,
        ]);
    }


    public function removeHistoryItem($videoId)
    {
        $user = Auth::user();

        WatchHistory::where('user_id', $user->id)
            ->where('video_id', $videoId)
            ->delete();

        return response()->json(['message' => 'Video removed from watch history.']);
    }

    public function clearHistory()
    {
        $user = Auth::user();
        $user->watchHistory()->delete();

        return response()->json(['message' => 'Watch history cleared successfully.']);
    }
}