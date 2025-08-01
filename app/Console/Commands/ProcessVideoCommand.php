<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessVideo;

class ProcessVideoCommand extends Command
{
    protected $signature = 'video:process {videoId} {inputPath} {outputDir}';
    protected $description = 'Manually dispatch the ProcessVideo job for testing';

    public function handle()
    {
        $videoId = $this->argument('videoId');
        $inputPath = $this->argument('inputPath');
        $outputDir = $this->argument('outputDir');

        ProcessVideo::dispatch($videoId, $inputPath, $outputDir);

        $this->info("ProcessVideo job dispatched for video ID: $videoId");
        return 0;
    }
}