<?php

namespace App\Core\AlertRules;

use App\Data\TriggeredAlert;
use Illuminate\Support\Collection;

class AlertRuleEvaluator
{
    public function __construct(private readonly AlertRuleTypeFactory $factory) {}

    /**
     * @return TriggeredAlert[]
     */
    public function evaluate(Collection $rules, Collection $priceMap): array
    {
        $triggered = [];

        foreach ($rules as $rule) {
            $entry = $priceMap->get($rule->ticker_id);

            if ($entry === null) {
                continue;
            }

            $price = (float) $entry['price'];

            if (! $rule->isOnCooldown() && $this->factory->make($rule->rule_type)->evaluate($rule, $price)) {
                $triggered[] = new TriggeredAlert(
                    ruleId: $rule->id,
                    tickerId: $rule->ticker_id,
                    currentPrice: $price,
                );
            }
        }

        return $triggered;
    }
}
