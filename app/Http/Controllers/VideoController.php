<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Jobs\ProcessVideo;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class VideoController extends Controller
{

    public function getVideoDuration($videoPath)
    {
        $ffprobePath = env('FFPROBE_PATH', 'ffprobe'); // fallback to 'ffprobe' if no env set

        // Escape path safely to avoid injection
        $escapedPath = escapeshellarg($videoPath);

        $command = "$ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $escapedPath";

        $duration = trim(shell_exec($command));

        // Convert to float for DB storage
        return floatval($duration);
    }



    // Function handle video upload
    public function videoUpload(Request $request)
    {
        if ($request->hasFile('video') && $request->file('video')->isValid()) {

            $appUrl = env('APP_URL');

            $videoId = rand(100000, 999999);
            $videoPath = $request->file('video')->storeAs("videos/$videoId", "$videoId." . $request->file('video')->getClientOriginalExtension(), 'public');


            $posterExtension = $request->file('poster')->getClientOriginalExtension();
            $posterPath = $request->file('poster')->storeAs("images/$videoId/poster", "$videoId.$posterExtension", 'public');

            $backdropExtension = $request->file('backdrop')->getClientOriginalExtension();
            $backdropPath = $request->file('backdrop')->storeAs("images/$videoId/backdrop", "$videoId.$backdropExtension", 'public');

            // Create video record with PROCESSING status
            $video = new Video();
            $video->id = $videoId;
            $video->title = $request->input('title', 'video' . $videoId);
            $video->description = $request->input('description', '');
            $video->slug = Str::slug($video->title);
            $video->thumbnail_path = "$appUrl/storage/$posterPath";
            $video->backdrop_path = "$appUrl/storage/$backdropPath";
            $video->user_id = Auth::id(); // Assuming you have user authentication
            $video->content_rating = $request->input('content_rating', 'PG'); // Default to PG if not provided
            $video->description = $request->input('description', '');
            $video->slug = Str::slug($video->title);
            $video->thumbnail_path = "$appUrl/storage/$posterPath";
            $video->backdrop_path = "$appUrl/storage/$backdropPath";
            $video->user_id = Auth::id(); // Assuming you have user authentication
            $video->content_rating = $request->input('content_rating', 'PG'); // Default to PG if not provided

            $video->language = $request->input('language', 'en'); // Default to English if not provided
            $video->release_year = $request->input('releaseYear', null); // Optional release date

            $video->status = Video::STATUS_PROCESSING; // Set to processing
            $video->duration = $this->getVideoDuration(storage_path("app/public/$videoPath"));



            $video->save();

            // Dispatch the job to process video in background
            $outputDir = storage_path("app/public/videos/$videoId");
            $inputPath = storage_path("app/public/$videoPath");

            ProcessVideo::dispatch($videoId, $inputPath, $outputDir);

            return response()->json([
                'message' => 'Video uploaded successfully. Processing started in background.',
                'video_id' => $videoId,
                'status' => 'processing'
            ], 200);
        }

        return response()->json(['message' => 'No valid video file uploaded'], 400);
    }

    public function getVideo($videoId)
    {
        // Eager load the relationships to include tags, genres, and people (credits).
        // findOrFail automatically handles the 404 case if the video is not found.
        $video = Video::with(['tags', 'genres', 'people'])->findOrFail($videoId);

        return response()->json($video, 200);
    }

    public function getVideoStatus($videoId)
    {
        $video = Video::find($videoId);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        return response()->json([
            'video_id' => $videoId,
            'status' => $video->status,
            'title' => $video->title,
            'resolutions' => $video->resolutions
        ]);
    }
    public function getRecommendations($id)
    {
        $currentVideo = Video::findOrFail($id);
        $limit = 10; // Max number of recommendations to return

        // --- Strategy 1: Collaborative Filtering ---
        // Find users who watched the current video.
        $usersWhoWatchedThis = WatchHistory::where('video_id', $currentVideo->id)
            ->pluck('user_id');

        // Find other videos those users have watched.
        $coWatchedVideoIds = WatchHistory::whereIn('user_id', $usersWhoWatchedThis)
            ->where('video_id', '!=', $currentVideo->id) // Exclude the current video
            ->pluck('video_id')
            ->countBy() // Counts occurrences of each video_id
            ->sortDesc() // Sorts by the most co-watched
            ->keys(); // Get the video IDs

        $recommendations = Video::whereIn('id', $coWatchedVideoIds)
            ->where('id', '!=', $currentVideo->id) // Final check to exclude current video
            ->take($limit)
            ->get();

        // --- Strategy 2: Content-Based Filtering (Fallback) ---
        // If collaborative filtering yields too few results, fill with videos from the same category.
        if ($recommendations->count() < $limit) {
            $remainingLimit = $limit - $recommendations->count();
            $alreadyFetchedIds = $recommendations->pluck('id')->push($currentVideo->id);

            $categoryVideos = Video::where('category', $currentVideo->category)
                ->whereNotIn('id', $alreadyFetchedIds) // Exclude already recommended and current video
                ->inRandomOrder() // Add some variety
                ->take($remainingLimit)
                ->get();

            // Merge the two collections
            $recommendations = $recommendations->merge($categoryVideos);
        }

        return response()->json($recommendations);
    }

    public function getAllVideos()
    {
        $videos = Video::with('user', 'tags', 'genres', 'people')->get();
        return response()->json($videos);
    }

    public function deleteVideo($videoId)
    {
        $video = Video::find($videoId);
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }
        $directoryPath = "videos/{$videoId}";

        if (Storage::disk('public')->exists($directoryPath)) {
            $deleted = Storage::disk('public')->deleteDirectory($directoryPath);

            if (!$deleted) {
                return response()->json(['message' => 'Failed to delete video files from storage. Check permissions.'], 500);
            }
        }
        $video->delete();

        return response()->json(['message' => 'Video deleted successfully'], 200);
    }

}