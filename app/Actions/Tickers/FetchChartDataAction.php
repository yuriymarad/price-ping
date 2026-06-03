<?php

namespace App\Actions\Tickers;

use App\Contracts\MarketDataProvider;
use App\Data\ChartPoint;
use App\Enums\ChartRange;
use App\Values\TickerSymbol;

class FetchChartDataAction
{
    public function __construct(
        private readonly MarketDataProvider $marketDataProvider,
    ) {}

    /**
     * @return ChartPoint[]|null
     */
    public function handle(TickerSymbol $symbol, ChartRange $chartRange): ?array
    {
        return $this->marketDataProvider->getChartData($symbol, $chartRange);
    }
}
