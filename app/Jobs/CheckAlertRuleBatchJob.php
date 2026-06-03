<?php

namespace App\Jobs;

use App\Core\AlertRules\AlertRuleEvaluator;
use App\Events\AlertsTriggered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class CheckAlertRuleBatchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(
        private readonly Collection $rules,
        private readonly array $priceMap,
    ) {
        $this->onQueue('rules');
    }

    public function handle(AlertRuleEvaluator $evaluator): void
    {
        $triggered = $evaluator->evaluate($this->rules, collect($this->priceMap));

        if (! empty($triggered)) {
            AlertsTriggered::dispatch($triggered);
        }
    }
}
