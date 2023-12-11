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

class NewFriendEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;
    private $friend_id;

    public function __construct(User $user, $friend_id)
    {
        $this->user = $user;
        $this->friend_id = $friend_id;
    }

    // Привязка к каналу WebSocket
    public function broadcastOn()
    {
        return new PrivateChannel('Friendly-chat-'.$this->friend_id);
    }

    // Название события для данного канала
    public function broadcastAs() 
    {
        return 'NewFriend';
    }

    // Данные для отправки по WS
    public function broadcastWith()
    {
        return [
            'user' => $this->user,
        ];
    }
}
