<?php

namespace Nuclear\Hierarchy\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nuclear\Reactor\Auth\User;
use Illuminate\Database\Eloquent\Collection;

class ContentsBulkDestroyed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The content collection.
     *
     * @var Collection
     */
    public $contents;

    /**
     * The content instance.
     *
     * @var \Nuclear\Reactor\Auth\User
     */
    public $user;
 

    /**
     * Create a new event instance.
     *
     * @param Collection $contents
     * @param User $user
     * @return void
     */
    public function __construct(Collection $contents, User $user)
    {
        $this->contents = $contents;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('content-events');
    }

}
