<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sender;
    public $receiver;

    public $senderName;

    public $msgTime;

    public $msgType;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $sender, $receiver, $senderName, $msgTime, $msgType)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->senderName = $senderName;
        $this->msgTime = $msgTime;
        $this->msgType = $msgType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('chat');
    }
}
