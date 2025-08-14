<?php

namespace App\Events;

use App\Models\Video;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoProcessingStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $video;
    public $status;
    public $message;
    public $resolutions;

    public function __construct(Video $video, string $status, string $message = '', array $resolutions = [])
    {
        $this->video = $video;
        $this->status = $status;
        $this->message = $message;
        $this->resolutions = $resolutions;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Channel for the specific user who owns the video
            new PrivateChannel('admin.notifications'),

            // new PrivateChannel('user.' . $this->video->user_id),

        ];
    }

    public function broadcastAs(): string
    {
        return 'video.processing.status';
    }

    public function broadcastWith(): array
    {
        return [
            'video_id' => $this->video->id,
            'video_title' => $this->video->title,
            'status' => $this->status,
            'message' => $this->message,
            'resolutions' => $this->resolutions,
            'timestamp' => now()->toISOString(),
        ];
    }
}