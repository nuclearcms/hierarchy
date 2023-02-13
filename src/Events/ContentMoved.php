<?php

namespace Nuclear\Hierarchy\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nuclear\Hierarchy\Content;
use Nuclear\Reactor\Auth\User;

class ContentMoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The content instance.
     *
     * @var \Nuclear\Hierarchy\Content
     */
    public $content;

    /**
     * The content instance.
     *
     * @var \Nuclear\Reactor\Auth\User
     */
    public $user;
 

    /**
     * Create a new event instance.
     *
     * @param Content $content
     * @param User $user
     * @return void
     */
    public function __construct(Content $content, User $user)
    {
        $this->content = $content;
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
