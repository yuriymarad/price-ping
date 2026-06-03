<?php

namespace App\Actions\AlertRules;

use App\Core\AlertRules\AlertRuleTypeFactory;
use App\Data\AlertRuleData;
use App\Models\Ticker;

class CreateAlertRuleForAllTickersAction
{
    public function __construct(private readonly AlertRuleTypeFactory $factory) {}

    public function handle(AlertRuleData $data): array
    {
        $ruleType = $data->ruleType;
        $created = 0;
        $total = 0;

        Ticker::query()->chunkById(100, function ($tickers) use ($ruleType, $data, &$created, &$total) {
            foreach ($tickers as $ticker) {
                $total++;
                $query = $ticker->rules()->ofType($ruleType)->getQuery();
                $query = $this->factory->make($ruleType)->applyDuplicateScope($query, $data);

                if ($query->exists()) {
                    continue;
                }

                $ticker->rules()->create($data->toAttributes());
                $created++;
            }
        });

        return ['created' => $created, 'skipped' => $total - $created];
    }
}
