<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class MessageSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;
    private $friend_id;
    private $message;

    public function __construct(User $user, $friend_id, $message)
    {
        $this->user = $user;
        $this->friend_id = $friend_id;
        $this->message = $message;
    }

    // Привязка к каналу WebSocket
    public function broadcastOn()
    {
        return new PrivateChannel('Friendly-chat-'.$this->friend_id);
    }

    // Название события для данного канала
    public function broadcastAs() 
    {
        return 'MessageSent';
    }

    // Данные для отправки по WS
    public function broadcastWith()
    {
        return [
            'user' => $this->user,
            'message' => $this->message,
        ];
    }
}
