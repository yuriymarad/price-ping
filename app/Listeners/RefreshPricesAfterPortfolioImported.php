<?php

namespace App\Listeners;

use App\Actions\Tickers\QueueTickerPriceRefreshAction;
use App\Events\PortfolioImported;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefreshPricesAfterPortfolioImported implements ShouldQueue
{
    public function __construct(private readonly QueueTickerPriceRefreshAction $action) {}

    public function handle(PortfolioImported $event): void
    {
        $this->action->handle($event->tickerIds);
    }
}
