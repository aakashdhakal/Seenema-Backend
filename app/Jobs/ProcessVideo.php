<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use App\Events\VideoProcessingStatusChanged;
use Log;

class ProcessVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoId;
    protected $inputPath;
    protected $outputDir;

    public function __construct($videoId, $inputPath, $outputDir)
    {
        $this->videoId = $videoId;
        $this->inputPath = $inputPath;
        $this->outputDir = $outputDir;
    }

    public function handle(): void
    {
        $video = Video::find($this->videoId);
        if (!$video) {
            Log::error("Video not found for processing: {$this->videoId}");
            return;
        }

        try {
            Log::info("Starting video processing for video ID: {$this->videoId}");
            // 1. Notify: Processing has started
            broadcast(new VideoProcessingStatusChanged($video, 'processing', 'Processing started...'))->toOthers();

            $splitResult = $this->splitVideosToSegments($this->inputPath, $this->outputDir);

            if ($splitResult['status']) {
                $video->status = Video::STATUS_READY;
                $video->resolutions = $splitResult['resolutions'];
                $video->save();

                // 2. Notify: Processing was successful
                broadcast(new VideoProcessingStatusChanged($video, 'ready', 'Your video is ready!'))->toOthers();
                Log::info("Video processing completed for video ID: {$this->videoId}");

            } else {
                $video->status = Video::STATUS_FAILED;
                $video->save();

                // 3. Notify: Processing failed
                broadcast(new VideoProcessingStatusChanged($video, 'failed', 'Processing failed.'))->toOthers();
                Log::error("Video processing failed for video ID: {$this->videoId}", [
                    'error' => $splitResult['error']
                ]);
            }
        } catch (\Exception $e) {
            $video->status = Video::STATUS_FAILED;
            $video->save();

            // 4. Notify: A critical error occurred
            broadcast(new VideoProcessingStatusChanged($video, 'failed', 'A critical error occurred.'))->toOthers();
            Log::error("Exception in video processing job for video ID: {$this->videoId}", [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function splitVideosToSegments($videoPath, $outputDir)
    {
        try {
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
                    'label' => '2160p',
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
                    . "-c:v libx264 -preset slow -profile:v main -b:v {$profile['bitrate']} "
                    . "-c:a aac -b:a {$profile['audio_bitrate']} -ac 2 "
                    . "-hls_time 5 "
                    . "-hls_playlist_type vod "
                    . "-hls_segment_type fmp4 "
                    . "-hls_fmp4_init_filename \"init.mp4\" "
                    . "-hls_segment_filename \"$profileDir/$segmentName\" "
                    . "-start_number 0 "
                    . "-hls_list_size 0 "
                    . "-f hls \"$profileDir/$playlistName\"";

                $process = Process::timeout(3600)->run($ffmpegCommand);

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

                    $bandwidth = (int) ($profile['bitrate']) * (str_contains($profile['bitrate'], 'k') ? 1000 : 1);
                    $masterPlaylist .= "#EXT-X-STREAM-INF:BANDWIDTH=$bandwidth,RESOLUTION={$profile['resolution']}\n";
                    $masterPlaylist .= "{$profile['label']}/$playlistName\n";
                } else {
                    Log::error("FFmpeg failed for {$profile['label']}", [
                        'error' => $process->errorOutput(),
                        'command' => $ffmpegCommand,
                    ]);
                    echo "Error processing {$profile['label']}: " . $process->errorOutput();
                    return [
                        'status' => false,
                        'error' => "FFmpeg failed for {$profile['label']}: " . $process->errorOutput(),
                    ];

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
}