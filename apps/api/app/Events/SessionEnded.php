<?php

namespace App\Events;

use App\Models\TelemedicineSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TelemedicineSession $session;

    /**
     * Create a new event instance.
     */
    public function __construct(TelemedicineSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('telemedicine.' . $this->session->session_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'session.ended';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->session_id,
            'status' => $this->session->status,
            'ended_at' => $this->session->ended_at?->toISOString(),
            'recording_url' => $this->session->recording_url,
        ];
    }
}
