<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use App\Models\Message;
use Illuminate\Support\Facades\Storage;

class EditMessage implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message,)
    {
        //
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('user:id,name');

        $data = $this->message->toArray();
        $data['user_name'] = $this->message->user?->name;
        $data['replyTo'] = $this->message->reply_to;

        if ($this->message->path) {
            $data['path'] = Storage::url($this->message->path);
            $data['image_url'] = $data['path'];
        }

        return $data;
    }

    public function broadcastAs(): string
    {
        return 'message.edit';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat->id),
        ];
    }
}
