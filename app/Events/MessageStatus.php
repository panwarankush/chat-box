<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageStatus implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $sender;
    public $receiver;

    public $status;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($sender, $receiver, $status)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->status = $status;
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('messageReceipt');
    }
}
