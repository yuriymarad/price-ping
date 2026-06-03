<?php

namespace App\Integrations\MarketData;

use App\Contracts\MarketDataProvider;
use App\Data\TickerData;
use App\Enums\ChartRange;
use App\Enums\MarketStatus;
use App\Values\TickerSymbol;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Override;

class CachedMarketDataProvider implements MarketDataProvider
{
    public function __construct(private readonly MarketDataProvider $inner) {}

    #[Override]
    public function getTickerData(TickerSymbol $symbol): ?TickerData
    {
        return $this->inner->getTickerData($symbol);
    }

    #[Override]
    public function getMarketStatus(TickerSymbol $symbol): ?MarketStatus
    {
        $key = "market.status.{$symbol->value}";

        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        try {
            return Cache::lock("{$key}:lock", 30)->block(15, fn () => Cache::remember(
                $key,
                now()->addSeconds((int) config('services.market_data.status_cache_seconds', 60)),
                fn () => $this->inner->getMarketStatus($symbol)
            ));
        } catch (LockTimeoutException) {
            return null;
        }
    }

    #[Override]
    public function getChartData(TickerSymbol $symbol, ChartRange $range = ChartRange::OneYear): ?array
    {
        $key = "market.chart.{$symbol->value}.{$range->value}";

        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        try {
            return Cache::lock("{$key}:lock", 30)->block(15, fn () => Cache::remember(
                $key,
                now()->addSeconds((int) config('services.market_data.cache_seconds', 600))->addSeconds(random_int(0, 60)),
                fn () => $this->inner->getChartData($symbol, $range)
            ));
        } catch (LockTimeoutException) {
            return null;
        }
    }
}
