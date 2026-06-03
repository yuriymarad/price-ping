<?php

namespace App\Console\Commands;

use App\Actions\MarketStatus\SyncMarketStatusAction;
use App\Values\TickerSymbol;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:broadcast-market-status-changes')]
#[Description('Poll the market status and broadcast a MarketStatusChanged event when it transitions.')]
class BroadcastMarketStatusChangesCommand extends Command
{
    public function handle(SyncMarketStatusAction $action): int
    {
        $symbol = new TickerSymbol(config('services.market_data.status_symbol', 'SPY'));
        $status = $action->handle($symbol);

        $this->info('Market status: '.($status?->value ?? 'unknown'));

        return self::SUCCESS;
    }
}
