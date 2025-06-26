<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public ChatMessage $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Broadcast op kanaal chat.{join_code}, ipv chat.{numeric_id}
     */
    public function broadcastOn(): Channel
    {
        return new Channel('chat.' . $this->message->event->join_code);
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'username'   => $this->message->user->name,
            'message'    => $this->message->message,
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}
