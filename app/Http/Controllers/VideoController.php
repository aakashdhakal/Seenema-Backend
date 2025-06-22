<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;


class VideoController extends Controller
{
    public function splitVideosToSegments($videoPath, $outputDir)
    {
        try {
            echo "Splitting video into segments...";
            $ffmpegPath = env('FFMPEG_PATH'); // Path to ffmpeg binary
            $ffprobePath = env('FFPROBE_PATH', 'ffprobe'); // Path to ffprobe binary

            // Step 1: Get original video resolution
            $ffprobeCmd = "$ffprobePath -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0:s=x \"$videoPath\"";
            $resolutionOutput = shell_exec($ffprobeCmd);
            [$originalWidth, $originalHeight] = explode('x', trim($resolutionOutput));
            $originalHeight = (int) $originalHeight;

            // Step 2: Define quality profiles (ascending order)
            $profiles = [
                [
                    'label' => '144p',
                    'resolution' => '256x144',
                    'bitrate' => '150k',
                    'audio_bitrate' => '48k',
                    'height' => 144,
                ],
                [
                    'label' => '240p',
                    'resolution' => '426x240',
                    'bitrate' => '400k',
                    'audio_bitrate' => '64k',
                    'height' => 240,
                ],
                [
                    'label' => '480p',
                    'resolution' => '854x480',
                    'bitrate' => '1000k',
                    'audio_bitrate' => '96k',
                    'height' => 480,
                ],
                [
                    'label' => '720p',
                    'resolution' => '1280x720',
                    'bitrate' => '2500k',
                    'audio_bitrate' => '128k',
                    'height' => 720,
                ],
                [
                    'label' => '1080p',
                    'resolution' => '1920x1080',
                    'bitrate' => '5000k',
                    'audio_bitrate' => '192k',
                    'height' => 1080,
                ],
                [
                    'label' => '4k',
                    'resolution' => '3840x2160',
                    'bitrate' => '10000k',
                    'audio_bitrate' => '256k',
                    'height' => 2160,
                ],
            ];

            $masterPlaylist = "#EXTM3U\n#EXT-X-VERSION:7\n";
            $resolutions = [];

            foreach ($profiles as $profile) {
                // Skip if resolution is higher than the original
                if ($profile['height'] > $originalHeight) {
                    continue;
                }
                $resolutions[] = $profile['label'];
                $profileDir = $outputDir . '/' . $profile['label'];
                if (!file_exists($profileDir)) {
                    mkdir($profileDir, 0755, true);
                }

                $playlistName = "playlist.m3u8";
                $segmentName = "segment_%03d.m4s";

                $ffmpegCommand = "$ffmpegPath -y -i \"$videoPath\" "
                    . "-vf \"scale={$profile['resolution']}\" "
                    . "-c:v libx264 -preset veryfast -profile:v main -crf 23 -b:v {$profile['bitrate']} "
                    . "-c:a aac -b:a {$profile['audio_bitrate']} -ac 2 "
                    . "-hls_time 5 "
                    . "-hls_playlist_type vod "
                    . "-hls_segment_type fmp4 "
                    . "-hls_fmp4_init_filename \"init.mp4\" "
                    . "-hls_segment_filename \"$profileDir/segment_%03d.m4s\" "
                    . "-start_number 0 "
                    . "-hls_list_size 0 "
                    . "-f hls \"$profileDir/$playlistName\"";

                $process = Process::timeout(1200)->run($ffmpegCommand);

                if ($process->successful()) {
                    // Inject segment sizes
                    $playlistPath = "$profileDir/$playlistName";
                    $playlistContent = file_get_contents($playlistPath);
                    $lines = explode("\n", $playlistContent);
                    $newLines = [];

                    foreach ($lines as $line) {
                        $newLines[] = $line;
                        if (preg_match('/^segment_\d+\.m4s$/', trim($line))) {
                            $segmentFile = $profileDir . '/' . trim($line);
                            if (file_exists($segmentFile)) {
                                $size = filesize($segmentFile);
                                $newLines[] = "#EXT-X-SEGMENT-SIZE:$size";
                            }
                        }
                    }

                    file_put_contents($playlistPath, implode("\n", $newLines));

                    $bandwidth = intval($profile['bitrate']) * 1000;
                    $masterPlaylist .= "#EXT-X-STREAM-INF:BANDWIDTH=$bandwidth,RESOLUTION={$profile['resolution']}\n";
                    $masterPlaylist .= "{$profile['label']}/$playlistName\n";
                } else {
                    Log::error("FFmpeg failed for {$profile['label']}", [
                        'error' => $process->errorOutput(),
                        'command' => $ffmpegCommand,
                    ]);
                }
            }

            file_put_contents($outputDir . '/manifest.m3u8', $masterPlaylist);

            return [
                'status' => true,
                'resolutions' => $resolutions,
                'master_playlist' => $outputDir . '/manifest.m3u8',
            ];
        } catch (\Throwable $e) {
            Log::error('Error in splitVideosToSegments', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

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
        set_time_limit(0);
        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $videoId = rand(100000, 999999); // Generate a random video ID, you can also use UUID
            $videoPath = $request->file('video')->storeAs("videos/$videoId", "$videoId." . $request->file('video')->getClientOriginalExtension(), 'public');
            // Split video into HLS segments (.ts) and generate manifest.m3u8
            $outputDir = storage_path("app/public/videos/$videoId");
            $inputPath = storage_path("app/public/$videoPath"); // Convert to absolute path
            $splitResult = $this->splitVideosToSegments($inputPath, $outputDir);
            if ($splitResult['status'] == true) {

                // Store video metadata in the database or perform any other necessary actions
                $video = new Video();
                $video->id = $videoId; // Use UUID as the primary key
                $video->title = $request->input('title', 'video' . $videoId); // Default title if not provided
                $video->description = $request->input('description', '');
                $video->slug = Str::slug($video->title);
                $video->resolutions = $splitResult['resolutions']; // You can populate this with actual bitrate data if needed
                // Set paths for manifest and segments
                $video->thumbnail_path = ''; // Set thumbnail path if available
                $video->duration = $this->getVideoDuration($inputPath); // Set duration if available
                $video->user_id = "1"; // Assuming user is authenticated
                $video->category = $request->input('category', 'default'); // Set category if provided
                $video->status = Video::STATUS_READY; // Set initial status
                $video->save();

                return response()->json(['message' => 'Video uploaded and processed successfully.', 'video_id' => $videoId], 200);
            } else {
                return response()->json(['message' => 'Error processing video, ' . $splitResult['error']], 500);
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

    public function getInit($videoId, $resolution)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video) {
            $initSegmentPath = storage_path("app/public/videos/$videoId/$resolution/init.mp4");
            if (Storage::disk('public')->exists("videos/$videoId/$resolution/init.mp4")) {
                return response()->file($initSegmentPath);
            } else {
                return response()->json(['message' => 'Init segment not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Video not found'], 404);
        }
    }

    public function getSegment($videoId, $resolution, $segment)
    {
        $video = Video::where('id', $videoId)->first();

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        $relativePath = "videos/$videoId/$resolution/$segment";
        $absolutePath = storage_path("app/public/$relativePath");

        if (!Storage::disk('public')->exists($relativePath)) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        // 💡 Get size to prevent Transfer-Encoding: chunked
        $fileSize = filesize($absolutePath);

        return response()->stream(function () use ($absolutePath) {
            readfile($absolutePath);
        }, 200, [
            'Content-Type' => 'video/mp4',
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=31536000',
            'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
        ]);
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
    public function getManifestByResolution($videoId, $resolution)
    {
        $video = Video::where('id', $videoId)->first();
        if ($video) {
            $manifestPath = storage_path("app/public/videos/$videoId/$resolution/playlist.m3u8");
            if (Storage::disk('public')->exists("videos/$videoId/$resolution/playlist.m3u8")) {
                return response()->file($manifestPath);
            } else {
                return response()->json(['message' => 'Manifest for the specified resolution not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Video not found'], 404);
        }
    }

    public function getSegmentSizes($segment, $videoId)
    {
        $video = Video::where('id', $videoId)->first();
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        // Define available resolutions (should match your encoding profiles)
        //g get resolution from the folders
        $resolutions = ['144p', '240p', '480p', '720p', '1080p'];
        $sizes = [];

        foreach ($resolutions as $resolution) {
            $segmentPath = storage_path("app/public/videos/$videoId/$resolution/$segment");
            if (Storage::disk('public')->exists("videos/$videoId/$resolution/$segment")) {
                $sizes[$resolution] = filesize($segmentPath);
            } else {
                $sizes[$resolution] = null; // or you can skip this resolution
            }
        }

        return response()->json(['sizes' => $sizes], 200);
    }

    public function getIntroVideo($resolution)
    {
        $introPath = storage_path("app/public/videos/intro/$resolution/segment_000.m4s");
        if (Storage::disk('public')->exists("videos/intro/$resolution/segment_000.m4s")) {
            return response()->file($introPath);
        } else {
            return response()->json(['message' => 'Intro video not found'], 404);
        }
    }

    public function getIntroInit($resolution)
    {
        $introPath = storage_path("app/public/videos/intro/$resolution/init.mp4");
        if (Storage::disk('public')->exists("videos/intro/$resolution/init.mp4")) {
            return response()->file($introPath);
        } else {
            return response()->json(['message' => 'Intro init segment not found'], 404);
        }
    }

    public function getIntroManifest($resolution)
    {
        $introManifestPath = storage_path("app/public/videos/intro/$resolution/playlist.m3u8");
        if (Storage::disk('public')->exists("videos/intro/$resolution/playlist.m3u8")) {
            return response()->file($introManifestPath);
        } else {
            return response()->json(['message' => 'Intro manifest not found'], 404);
        }
    }
}