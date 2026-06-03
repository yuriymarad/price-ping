<?php

namespace App\Jobs;

use App\Actions\Tickers\RefreshTickerPriceAction;
use App\Events\TickerPricesRefreshed;
use App\Models\Ticker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshTickerPriceBatchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(private readonly array $tickerIds)
    {
        $this->onQueue('prices');
    }

    public function handle(RefreshTickerPriceAction $action): void
    {
        $tickers = Ticker::whereIn('id', $this->tickerIds)->get()->all();
        $refreshed = $action->handle($tickers);

        if (! empty($refreshed)) {
            TickerPricesRefreshed::dispatch($refreshed);
        }
    }
}
