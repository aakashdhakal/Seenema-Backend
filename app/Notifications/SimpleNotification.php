<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SimpleNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $title;
    public $body;

    public function __construct($title = "Notification", $body = "You have a new notification.")
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'user_id' => $notifiable->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->body,
            'user_id' => $notifiable->id
        ]);
    }
    public function broadcastAs(): string
    {
        return 'notification';
    }
    public function broadcastType(): string
    {
        return 'broadcast.message';
    }

}
