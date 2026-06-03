<?php

namespace App\Actions\Tickers;

use App\Jobs\RefreshTickerPriceBatchJob;
use App\Models\Ticker;

class QueueTickerPriceRefreshAction
{
    /**
     * @param  int[]  $tickerIds
     */
    public function handle(array $tickerIds = []): void
    {
        $query = Ticker::query();

        if (! empty($tickerIds)) {
            $query->whereIn('id', $tickerIds);
        }

        $query->chunkById(10, function ($chunk): void {
            RefreshTickerPriceBatchJob::dispatch($chunk->modelKeys());
        });
    }
}
