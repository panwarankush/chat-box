<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoiceCallEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $eventReceiverId;
    public $payload;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($eventReceiverId, $payload)
    {
        $this->eventReceiverId = $eventReceiverId;
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('voice-call.' . $this->eventReceiverId);
    }


    public function broadcastWith(){
        return $this->payload;
        // [
        //     'voiceCallInfo' => $this->payload,
        //other data
        // ];
    }
}
