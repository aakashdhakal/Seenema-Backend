<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\HelloWorldEvent;

class HelloWorldBroadcast extends Command
{
    protected $signature = 'broadcast:hello-world {--times=0} {--interval=2}';
    protected $description = 'Broadcast hello world message repeatedly for testing WebSocket';

    public function handle()
    {
        $times = (int) $this->option('times');
        $interval = (int) $this->option('interval');
        $count = 0;

        $this->info("Broadcasting 'hello world' every {$interval}s" . ($times ? " for $times times" : " (infinite)") . "...");

        while ($times === 0 || $count < $times) {
            broadcast(new HelloWorldEvent("hello world #" . ($count + 1)));
            $this->info("Broadcasted hello world #" . ($count + 1));
            $count++;
            sleep($interval);
        }

        $this->info("Done broadcasting.");
    }
}