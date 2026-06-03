<?php

namespace App\Listeners;

use App\Core\AlertRules\AlertSendHandler;
use App\Events\AlertsTriggered;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTriggeredAlerts implements ShouldQueue
{
    public string $queue = 'alerts';

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(private readonly AlertSendHandler $alertSendHandler) {}

    public function handle(AlertsTriggered $event): void
    {
        $this->alertSendHandler->process($event->triggered);
    }
}
