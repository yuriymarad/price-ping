<?php

namespace App\Events;

use App\Enums\MarketStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarketStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly MarketStatus $status) {}

    public function broadcastOn(): Channel
    {
        return new Channel('markets');
    }

    public function broadcastAs(): string
    {
        return 'market.status.changed';
    }

    public function broadcastWith(): array
    {
        return ['status' => $this->status->value];
    }
}
