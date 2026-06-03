<?php

namespace App\Events;

use App\Data\TriggeredAlert;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertsTriggered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  TriggeredAlert[]  $triggered
     */
    public function __construct(public readonly array $triggered = []) {}
}
