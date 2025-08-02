<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WatchList;

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


    public function updateProgress(Request $request)
    {
        $request->validate([
            'currentTime' => 'required|numeric|min:0',
            'videoId' => 'required|exists:videos,id',
        ]);

        $user = Auth::user();
        $video = Video::findOrFail($request->input('videoId'));
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

        return response()->json(['message' => 'Video removed from watch history.'], 200);
    }

    public function clearHistory()
    {
        $user = Auth::user();
        $user->watchHistory()->delete();

        return response()->json(['message' => 'Watch history cleared successfully.']);
    }

    public function removeFormContinueWatching($videoId)
    {
        $user = Auth::user();

        // Update the continue_watching flag to false
        $history = WatchHistory::where('user_id', $user->id)
            ->where('video_id', $videoId)
            ->first();

        if ($history) {
            $history->continue_watching = false;
            $history->save();
        }

        return response()->json(['message' => 'Video removed from continue watching list.'], 200);
    }

    public function getUserStats()
    {
        $user = Auth::user();

        // Get total watch time
        $totalWatchTime = WatchHistory::where('user_id', $user->id)
            ->sum('watched_duration');

        // Get total videos watched
        $totalVideosWatched = WatchHistory::where('user_id', $user->id)
            ->count();



        // Get number of incomplete videos (not finished)
        $incompleteVideosCount = WatchHistory::where('user_id', $user->id)
            ->whereNull('finished_at')
            ->count();

        $totalSavedVideos = WatchList::where('user_id', $user->id)
            ->count();

        return response()->json([
            'total_watch_time' => $totalWatchTime,
            'total_videos_watched' => $totalVideosWatched,
            'incomplete_videos_count' => $incompleteVideosCount,
            'total_saved_videos' => $totalSavedVideos,
        ]);
    }
}