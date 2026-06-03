<?php

namespace App\Contracts;

use App\Data\ChartPoint;
use App\Data\TickerData;
use App\Enums\ChartRange;
use App\Enums\MarketStatus;
use App\Values\TickerSymbol;

interface MarketDataProvider
{
    public function getTickerData(TickerSymbol $symbol): ?TickerData;

    public function getMarketStatus(TickerSymbol $symbol): ?MarketStatus;

    /**
     * @return ChartPoint[]|null
     */
    public function getChartData(TickerSymbol $symbol, ChartRange $range = ChartRange::OneYear): ?array;
}
