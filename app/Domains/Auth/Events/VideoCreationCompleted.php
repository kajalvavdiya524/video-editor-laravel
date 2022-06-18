<?php

namespace App\Domains\Auth\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCreationCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $process_body;
    public $video_creation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($video_creation, $process_body)
    {
        $this->video_creation = $video_creation;
        $this->process_body = $process_body;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        \Log::info($this->video_creation->user_id);
        return new Channel('video-creation.'.$this->video_creation->user_id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        if ($this->process_body['status'] == 'started')
            return 'video-creation-started';
        else if ($this->process_body['status'] == 'working')
            return 'video-creation-working';
        else if ($this->process_body['status'] == 'OK')
            return 'video-creation-completed';
        else
            return 'video-creation-failed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'status' => isset($this->process_body['status']) ? $this->process_body['status'] : '',
            'percent' => isset($this->process_body['percent']) ? $this->process_body['percent'] : '',
            'error' => isset($this->process_body['error']) ? $this->process_body['error'] : '',
            'task_id' => isset($this->process_body['task_id']) ? $this->process_body['task_id'] : ''
        ];
    }
}
