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
use App\Events\VideoProcessingStatusChanged;


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
    public function createVideoEntry(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'poster' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'backdrop' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'original_filename' => 'required|string|max:255',
            'releaseYear' => 'nullable|integer',
            'contentRating' => 'nullable|string',
            'visibility' => 'required|string',
            'language' => 'required|string',
        ]);

        $appUrl = env('APP_URL');
        // Use UUID for a more robust and collision-resistant unique ID
        $videoId = random_int(100000, 999999); // Generate a random 6-digit number

        $posterPath = $request->file('poster')->store("images/{$videoId}/poster", 'public');
        $backdropPath = $request->file('backdrop')->store("images/{$videoId}/backdrop", 'public');

        $video = new Video();
        $video->id = $videoId;
        $video->title = $validated['title'];
        $video->description = $validated['description'] ?? '';
        $video->slug = Str::slug($validated['title']) . '-' . $videoId;
        $video->original_filename = $validated['original_filename'];
        $video->thumbnail_path = "$appUrl/storage/$posterPath";
        $video->backdrop_path = "$appUrl/storage/$backdropPath";
        $video->user_id = Auth::id();
        $video->status = Video::STATUS_UPLOADING; // New status
        $video->release_year = $validated['releaseYear'];
        $video->content_rating = $validated['contentRating'];
        $video->visibility = $validated['visibility'];
        $video->language = $validated['language'];
        $video->save();

        return response()->json(['video_id' => $video->id], 201);
    }

    /**
     * Step 2: Receive and assemble file chunks.
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'video_id' => 'required|string|exists:videos,id',
            'chunk' => 'required|file',
        ]);

        $video = Video::findOrFail($request->video_id);
        $chunk = $request->file('chunk');
        $videoDirectory = "videos/{$video->id}";
        $finalPath = "{$videoDirectory}/{$video->id}";

        // --- MEMORY-SAFE CHUNK APPENDING ---
        // Get the path to the temporary uploaded chunk file
        $chunkPath = $chunk->getRealPath();

        // Get the full destination path on the 'public' disk
        $destinationPath = Storage::disk('public')->path($finalPath);

        // Ensure the destination directory exists
        Storage::disk('public')->makeDirectory($videoDirectory);

        // Open the destination file in append mode and the chunk in read mode
        $destinationStream = fopen($destinationPath, 'a');
        $sourceStream = fopen($chunkPath, 'r');

        // Copy the contents of the chunk stream to the destination stream
        stream_copy_to_stream($sourceStream, $destinationStream);

        // Close the file handles
        fclose($sourceStream);
        fclose($destinationStream);
        // --- END OF MEMORY-SAFE CODE ---

        if ($request->boolean('is_last')) {
            $fullPath = Storage::disk('public')->path($finalPath);
            $outputDir = Storage::disk('public')->path($videoDirectory);

            $video->status = Video::STATUS_PROCESSING;
            $video->duration = $this->getVideoDuration($fullPath);
            $video->save();
            // 1. Notify: Processing has started
            broadcast(new VideoProcessingStatusChanged($video, 'processing', 'Processing started...'))->toOthers();
            ProcessVideo::dispatch($video->id, $fullPath, $outputDir);

            return response()->json(['message' => 'Upload complete, processing started.']);
        }

        return response()->json(['message' => 'Chunk uploaded successfully.']);
    }

    public function getVideoBySlug($slug)
    {
        // Eager load the relationships to include tags, genres, and people (credits).
        // findOrFail automatically handles the 404 case if the video is not found.
        $video = Video::with(['tags', 'genres', 'people'])->where('slug', $slug)->firstOrFail();

        return response()->json($video, 200);
    }

    public function getVideoById($id)
    {
        // Eager load the relationships to include tags, genres, and people (credits).
        // findOrFail automatically handles the 404 case if the video is not found.
        $video = Video::with(['tags', 'genres', 'people'])->findOrFail($id);

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
    // Content-based recommendation: finds videos from the same category as the given video
    public function getContentBasedRecommendations($videoId, $limit = 10)
    {
        $currentVideo = Video::findOrFail($videoId);
        $alreadyFetchedIds = [$currentVideo->id];

        $categoryVideos = Video::where('category', $currentVideo->category)
            ->whereNotIn('id', $alreadyFetchedIds)
            ->where('status', Video::STATUS_READY)
            ->inRandomOrder()
            ->take($limit)
            ->get();

        return $categoryVideos;
    }

    // Collaborative recommendation: finds videos watched by users who watched a given video
    public function getCollaborativeRecommendations($videoId, $limit = 10)
    {
        $currentVideo = Video::findOrFail($videoId);

        // Find users who watched the current video
        $usersWhoWatchedThis = WatchHistory::where('video_id', $currentVideo->id)
            ->pluck('user_id');

        // Find other videos those users have watched
        $coWatchedVideoIds = WatchHistory::whereIn('user_id', $usersWhoWatchedThis)
            ->where('video_id', '!=', $currentVideo->id)
            ->pluck('video_id')
            ->countBy()
            ->sortDesc()
            ->keys();

        $recommendations = Video::whereIn('id', $coWatchedVideoIds)
            ->where('id', '!=', $currentVideo->id)
            ->where('status', Video::STATUS_READY)
            ->take($limit)
            ->get();

        return $recommendations;
    }

    // Main recommendation endpoint: uses a random video from user's watch history
    public function getRecommendations()
    {
        $userId = Auth::id();
        $limit = 10;

        // Get a random video id from user's watch history
        $randomVideoId = WatchHistory::where('user_id', $userId)
            ->inRandomOrder()
            ->value('video_id');

        if (!$randomVideoId) {
            // Fallback: just return random ready videos
            $videos = Video::where('status', Video::STATUS_READY)
                ->inRandomOrder()
                ->take($limit)
                ->get();
            return response()->json($videos);
        }

        // Try collaborative recommendations first
        $recommendations = $this->getCollaborativeRecommendations($randomVideoId, $limit);

        // If not enough, fill with content-based
        if ($recommendations->count() < $limit) {
            $remainingLimit = $limit - $recommendations->count();
            $alreadyFetchedIds = $recommendations->pluck('id')->push($randomVideoId)->toArray();

            $contentBased = Video::where('category', Video::find($randomVideoId)->category)
                ->whereNotIn('id', $alreadyFetchedIds)
                ->where('status', Video::STATUS_READY)
                ->inRandomOrder()
                ->take($remainingLimit)
                ->get();

            $recommendations = $recommendations->merge($contentBased);
        }

        return response()->json($recommendations);
    }

    public function getAllVideos()
    {
        $videos = Video::with('user', 'tags', 'genres', 'people')->get();
        return response()->json($videos);
    }

    public function getFeaturedVideo()
    {
        $video = Video::where('status', Video::STATUS_READY)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$video) {
            return response()->json(['message' => 'No featured video found'], 404);
        }

        return response()->json($video, 200);
    }

    public function getTrendingVideos()
    {
        // For a Netflix-style "Trending Now", we can use recently added videos.
        // For now, per request, we return a random selection of ready videos.
        $videos = Video::with('user', 'tags', 'genres', 'people')
            ->where('status', Video::STATUS_READY)
            ->inRandomOrder()
            ->take(10)
            ->get();

        if ($videos->isEmpty()) {
            return response()->json([], 200);
        }

        return response()->json($videos, 200);
    }

    public function getPopularVideos()
    {
        // For a Netflix-style "Popular on Netflix", this could be based on a more complex algorithm.
        // For now, per request, we return a different random selection of ready videos.
        $videos = Video::with('user', 'tags', 'genres', 'people')
            ->where('status', Video::STATUS_READY)
            ->inRandomOrder()
            ->take(10)
            ->get();

        if ($videos->isEmpty()) {
            return response()->json([], 200);
        }

        return response()->json($videos, 200);
    }

    public function getNewReleases()
    {
        // For a Netflix-style "New Releases", we can fetch videos added in the last 30 days.
        $videos = Video::with('user', 'tags', 'genres', 'people')
            ->where('status', Video::STATUS_READY)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        if ($videos->isEmpty()) {
            return response()->json([], 200);
        }

        return response()->json($videos, 200);
    }

    public function getVideoByCategory($category)
    {
        $videos = Video::with('user', 'tags', 'genres', 'people')
            ->where('status', Video::STATUS_READY)
            ->whereHas('genres', function ($query) use ($category) {
                // Assuming the genres table has a 'name' or 'slug' column to filter by.
                // Using 'name' as an example.
                $query->where('name', $category);
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        if ($videos->isEmpty()) {
            return response()->json(['message' => 'No videos found for this category'], 404);
        }

        return response()->json($videos, 200);
    }

    //get continue watching
    public function getContinueWatching()
    {
        $userId = Auth::id();
        $watchHistory = WatchHistory::with('video')
            ->where('user_id', $userId)
            ->whereHas('video', function ($query) {
                $query->where('status', Video::STATUS_READY);
            })
            // Use whereRaw to compare columns from the watch_histories and related videos table
            ->whereRaw('watched_duration < (SELECT duration FROM videos WHERE videos.id = watch_histories.video_id) - 20')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($watchHistory, 200);
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