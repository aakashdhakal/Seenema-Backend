<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class VideoController extends Controller
{


    public function splitVideosToSegments($videoPath, $outputDir)
    {
        // Example values for variables, replace with actual logic or parameters as needed
        $res = '1280:720'; // Example resolution
        $chunkDuration = 10; // Example chunk duration in seconds
        $ffmpegPath = env('FFMPEG_PATH'); // Path to ffmpeg binary

        $ffmpegCommand = "$ffmpegPath -i \"$videoPath\" -codec:v libx264 -codec:a aac -hls_time 10 -hls_playlist_type vod -hls_segment_type fmp4 -start_number 0 -hls_list_size 0 -f hls -hls_segment_filename \"$outputDir/segment%03d.m4s\" \"$outputDir/manifest.m3u8\"";


        $process = Process::timeout(600)->run($ffmpegCommand);
        if ($process->successful()) {
            return true; // Video split successfully
        } else {

            return $process->errorOutput(); // Error in splitting video
        }
    }


    // Function handle video upload
    public function videoUpload(Request $request)
    {
        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $videoId = rand(100000, 999999); // Generate a random video ID, you can also use UUID
            $videoPath = $request->file('video')->storeAs("videos/$videoId", "$videoId." . $request->file('video')->getClientOriginalExtension(), 'public');
            // Split video into HLS segments (.ts) and generate manifest.m3u8
            $outputDir = storage_path("app/public/videos/$videoId");
            $inputPath = storage_path("app/public/$videoPath"); // Convert to absolute path
            $splitResult = $this->splitVideosToSegments($inputPath, $outputDir);
            if ($splitResult === true) {

                // Store video metadata in the database or perform any other necessary actions
                $video = new Video();
                $video->id = $videoId; // Use UUID as the primary key
                $video->title = $request->input('title', 'Untitled Video');
                $video->description = $request->input('description', '');
                $video->slug = Str::slug($video->title);
                $video->manifest_path = "";
                $video->bitrates = []; // You can populate this with actual bitrate data if needed
                $video->segment_sizes = []; // You can populate this with actual segment sizes if needed
                // Set paths for manifest and segments
                $video->thumbnail_path = ''; // Set thumbnail path if available
                $video->duration = 0; // Set duration if available
                $video->user_id = "1"; // Assuming user is authenticated
                $video->category = $request->input('category', 'default'); // Set category if provided
                $video->status = Video::STATUS_PROCESSING; // Set initial status
                $video->save();

                return response()->json(['message' => 'Video uploaded and processed successfully.', 'video_id' => $videoId], 200);
            } else {
                return response()->json(['message' => "Error processing video, $splitResult"], 500);
            }
        }
    }

    public function getVideo($videoId)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video) {
            return response()->json($video, 200);
        } else {
            return response()->json(['message' => 'Video not found'], 404);
        }
    }

    public function getInit($videoId)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video && Storage::disk('public')->exists("videos/$videoId/init.mp4")) {
            return response()->file(storage_path("app/public/videos/$videoId/init.mp4"));
        } else {
            return response()->json(['message' => 'Manifest not found'], 404);
        }
    }

    public function getSegment($videoId, $segment)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video && Storage::disk('public')->exists("videos/$videoId/$segment")) {
            return response()->file(storage_path("app/public/videos/$videoId/$segment"));
        } else {
            return response()->json(['message' => 'Segment not found'], 404);
        }
    }

    public function getManifest($videoId)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video && Storage::disk('public')->exists("videos/$videoId/manifest.m3u8")) {
            return response()->file(storage_path("app/public/videos/$videoId/manifest.m3u8"));
        } else {
            return response()->json(['message' => 'Manifest not found'], 404);
        }
    }
}