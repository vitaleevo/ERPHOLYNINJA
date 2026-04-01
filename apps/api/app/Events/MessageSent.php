<?php

namespace App\Events;

use App\Models\TelemedicineChat;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TelemedicineChat $chat;
    public array $sender;

    /**
     * Create a new event instance.
     */
    public function __construct(TelemedicineChat $chat)
    {
        $this->chat = $chat;
        
        // Carregar informações do remetente
        if ($chat->user) {
            $this->sender = [
                'id' => $chat->user->id,
                'name' => $chat->user->name,
                'avatar' => $chat->user->avatar,
                'role' => $chat->user->role,
            ];
        } else {
            $this->sender = [
                'id' => null,
                'name' => 'Sistema',
                'role' => 'system',
            ];
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('telemedicine.' . $this->chat->session_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->chat->id,
            'session_id' => $this->chat->session_id,
            'message' => $this->chat->message,
            'type' => $this->chat->type,
            'file_url' => $this->chat->file_url,
            'file_name' => $this->chat->file_name,
            'is_read' => $this->chat->is_read,
            'created_at' => $this->chat->created_at->toISOString(),
            'sender' => $this->sender,
        ];
    }
}
