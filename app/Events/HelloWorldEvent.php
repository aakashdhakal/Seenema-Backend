<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HelloWorldEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message = "hello world")
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Use a public channel for easy testing
        return new Channel('test-channel');
    }

    public function broadcastAs()
    {
        return 'hello.world';
    }
}