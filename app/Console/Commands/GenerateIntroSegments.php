<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class GenerateIntroSegments extends Command
{
    protected $signature = 'video:segment-intro {--path= : Path to the intro video}';

    protected $description = 'Segment the intro video into multiple resolutions and generate HLS segments';

    public function handle()
    {
        $introPath = $this->option('path') ?? storage_path('app/public/videos/intro.mp4');

        if (!file_exists($introPath)) {
            $this->error("Intro video not found at path: $introPath");
            return 1;
        }

        $ffmpegPath = env('FFMPEG_PATH', 'ffmpeg');

        $profiles = [
            ['label' => '144p', 'resolution' => '256x144', 'bitrate' => '150k', 'audio_bitrate' => '48k'],
            ['label' => '240p', 'resolution' => '426x240', 'bitrate' => '400k', 'audio_bitrate' => '64k'],
            ['label' => '480p', 'resolution' => '854x480', 'bitrate' => '1000k', 'audio_bitrate' => '96k'],
            ['label' => '720p', 'resolution' => '1280x720', 'bitrate' => '2500k', 'audio_bitrate' => '128k'],
            ['label' => '1080p', 'resolution' => '1920x1080', 'bitrate' => '5000k', 'audio_bitrate' => '192k'],
            ['label' => '1440p', 'resolution' => '2560x1440', 'bitrate' => '10000k', 'audio_bitrate' => '256k'],
            ['label' => '2160p', 'resolution' => '3840x2160', 'bitrate' => '20000k', 'audio_bitrate' => '320k'],
        ];

        $introOutputDir = storage_path('app/public/videos/intro');

        foreach ($profiles as $profile) {
            $profileDir = $introOutputDir . '/' . $profile['label'];

            if (!file_exists($profileDir)) {
                mkdir($profileDir, 0755, true);
            }

            $playlistName = 'playlist.m3u8';

            $command = "$ffmpegPath -y -i \"$introPath\" "
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

            $this->info("Running: $command");

            $process = Process::run($command);

            if (!$process->successful()) {
                $this->error("FFmpeg failed for {$profile['label']} profile:");
                $this->error($process->errorOutput());
                return 1;
            }
        }

        $this->info('Intro video segmented successfully!');
        return 0;
    }
}
