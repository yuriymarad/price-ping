<?php

namespace App\Actions\Tickers;

use App\Contracts\MarketDataProvider;
use App\Models\Ticker;
use App\Values\TickerSymbol;

class CreateTickerAction
{
    public function __construct(private readonly MarketDataProvider $marketDataProvider) {}

    public function handle(TickerSymbol $symbol): ?Ticker
    {
        $tickerData = $this->marketDataProvider->getTickerData($symbol);

        if ($tickerData === null) {
            return null;
        }

        $ticker = Ticker::query()->create([
            'symbol' => $symbol->value,
            'long_name' => $tickerData->longName,
            'currency' => $tickerData->currency,
            'exchange_name' => $tickerData->exchangeName,
            'last_price' => $tickerData->regularMarketPrice,
            'last_fetched_at' => now(),
        ]);

        return $ticker;
    }
}
