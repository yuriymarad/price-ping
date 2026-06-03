<?php

namespace App\Actions\AlertRules;

use App\Jobs\CheckAlertRuleBatchJob;
use App\Models\AlertRule;

class QueueAlertRulesCheckAction
{
    /**
     * @param  array<int, array{id: int, price: float}>  $refreshed
     */
    public function handle(array $refreshed): void
    {
        $priceMap = collect($refreshed)->keyBy('id');

        AlertRule::query()
            ->active()
            ->whereIn('ticker_id', $priceMap->keys()->all())
            ->chunkById(50, function ($rules) use ($priceMap): void {
                CheckAlertRuleBatchJob::dispatch($rules, $priceMap->all());
            });
    }
}
