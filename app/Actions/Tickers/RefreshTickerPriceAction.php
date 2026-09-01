<?php

namespace App\Actions\Tickers;

use App\Contracts\MarketDataProvider;
use App\Values\TickerSymbol;
use Illuminate\Support\Facades\Log;

class RefreshTickerPriceAction
{
    public function __construct(private readonly MarketDataProvider $marketDataProvider) {}

    public function handle(array $tickers): array
    {
        $refreshed = [];

        foreach ($tickers as $ticker) {
            $tickerData = $this->marketDataProvider->getTickerData(new TickerSymbol($ticker->symbol));

            if ($tickerData === null) {
                Log::warning('RefreshTickerPriceAction: could not fetch price.', ['symbol' => $ticker->symbol]);

                continue;
            }

            $ticker->recordMarketPrice($tickerData);

            $refreshed[] = [
                'id' => $ticker->id,
                'price' => $tickerData->regularMarketPrice,
                'fetched_at' => $ticker->last_fetched_at->toIso8601String(),
            ];
        }

        return $refreshed;
    }
}
