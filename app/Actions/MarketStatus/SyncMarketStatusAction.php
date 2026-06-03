<?php

namespace App\Actions\MarketStatus;

use App\Contracts\MarketDataProvider;
use App\Enums\MarketStatus;
use App\Events\MarketStatusChanged;
use App\Values\TickerSymbol;
use Illuminate\Support\Facades\Cache;

class SyncMarketStatusAction
{
    private const LAST_BROADCAST_KEY = 'market.status.last_broadcast';

    public function __construct(private readonly MarketDataProvider $provider) {}

    public function handle(TickerSymbol $symbol): ?MarketStatus
    {
        $current = $this->provider->getMarketStatus($symbol);

        if ($current === null) {
            return null;
        }

        $previous = Cache::get(self::LAST_BROADCAST_KEY);

        if ($previous === $current->value) {
            return $current;
        }

        Cache::forever(self::LAST_BROADCAST_KEY, $current->value);
        MarketStatusChanged::dispatch($current);

        return $current;
    }
}
