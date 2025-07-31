<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\SimpleNotification;

class HelloWorldBroadcast extends Command
{
    protected $signature = 'broadcast:hello-world {--times=0} {--interval=2}';
    protected $description = 'Broadcast hello world message repeatedly for testing WebSocket';

    public function handle()
    {
        // Default: 5 times, 10 seconds interval
        $times = $this->option('times') !== null ? (int) $this->option('times') : 30;
        $interval = $this->option('interval') !== null ? (int) $this->option('interval') : 5;

        if ($times <= 0)
            $times = 5;
        if ($interval <= 0)
            $interval = 10;

        $count = 0;

        $this->info("Broadcasting 'hello world' every {$interval}s for $times times...");

        while ($count < $times) {
            $user = User::find(23);
            if (!$user) {
                $this->error("User with ID 23 not found.");
                return;
            }
            $user->notify(new SimpleNotification(
                'Hello World',
                'This is a test message from the command line. Your account has been suspended'
            ));
            $count++;
            if ($count < $times) {
                sleep($interval);
            }
        }

        $this->info("Done broadcasting.");
    }
}