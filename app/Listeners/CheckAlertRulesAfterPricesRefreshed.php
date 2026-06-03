<?php

namespace App\Listeners;

use App\Actions\AlertRules\QueueAlertRulesCheckAction;
use App\Events\TickerPricesRefreshed;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckAlertRulesAfterPricesRefreshed implements ShouldQueue
{
    public string $queue = 'default';

    public int $tries = 1;

    public function __construct(private readonly QueueAlertRulesCheckAction $action) {}

    public function handle(TickerPricesRefreshed $event): void
    {
        $this->action->handle($event->refreshed);
    }
}
