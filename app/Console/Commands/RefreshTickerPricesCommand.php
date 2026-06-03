<?php

namespace App\Console\Commands;

use App\Actions\Tickers\QueueTickerPriceRefreshAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:refresh-ticker-prices')]
#[Description('Trigger an async price refresh and alert check for all tickers.')]
class RefreshTickerPricesCommand extends Command
{
    public function handle(QueueTickerPriceRefreshAction $action): int
    {
        $action->handle();
        $this->info('Price refresh triggered.');

        return self::SUCCESS;
    }
}
