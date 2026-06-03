<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TickerPricesRefreshed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, array{id: int, price: float, fetched_at: string}>  $refreshed
     */
    public function __construct(public readonly array $refreshed = []) {}

    public function broadcastOn(): Channel
    {
        return new Channel('tickers');
    }

    public function broadcastAs(): string
    {
        return 'ticker.prices.refreshed';
    }

    /**
     * @return array{updates: array<int, array{id: int, price: float, fetched_at: string}>}
     */
    public function broadcastWith(): array
    {
        return ['updates' => $this->refreshed];
    }
}
