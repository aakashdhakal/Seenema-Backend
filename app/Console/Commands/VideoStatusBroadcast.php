<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;
use App\Events\VideoProcessingStatusChanged;

class VideoStatusBroadcast extends Command
{
    protected $signature = 'broadcast:test-event {videoId?}';
    protected $description = 'Broadcast a test VideoProcessingStatusChanged event';

    public function handle()
    {
        $video = Video::find($this->argument('videoId')) ?? Video::first();
        if (!$video) {
            $this->error('No video found in the database.');
            return 1;
        }

        broadcast(new VideoProcessingStatusChanged(
            $video,
            'ready',
            'This is a test WebSocket message!',
            ['144p', '240p', '480p']
        ));

        $this->info('Test event broadcasted!');
        return 0;
    }
}