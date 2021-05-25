<?php

namespace FamilyTree365\LaravelGedcom\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GedComProgressSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $slug;
    public $total;
    public $complete;
    public $channel;

    /**
     * Create a new event instance.
     *
     * @param $slug
     * @param $total
     * @param $complete
     */
    public function __construct(
      $slug,
      $total,
      $complete,
      $channel = ['name' => 'gedcom-progress', 'eventName' => 'newMessage']
    )
    {
        $this->slug = $slug;
        $this->total = $total;
        $this->complete = $complete;
        $this->queue = 'low';

        $this->channel = $channel;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
          'slug' => $this->slug,
          'total' => $this->total,
          'complete' => $this->complete,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->channel['name']);
    }

    public function broadcastAs()
    {
        return $this->channel['eventName'];
    }
}
