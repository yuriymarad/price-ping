<?php

namespace App\Console\Commands;

use App\Actions\PriceDigests\SendPriceDigestAction;
use App\Core\PriceDigests\ThresholdProximityPriceDigest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-threshold-proximity-digest')]
#[Description('Send a daily digest of tickers closest to their price threshold targets')]
class SendThresholdProximityPriceDigestCommand extends Command
{
    public function handle(SendPriceDigestAction $action, ThresholdProximityPriceDigest $thresholdProximityPriceDigest): int
    {
        if ($action->handle($thresholdProximityPriceDigest)) {
            $this->info('Threshold proximity digest sent.');
        } else {
            $this->info('No tickers within 10% of threshold — skipping digest.');
        }

        return self::SUCCESS;
    }
}
