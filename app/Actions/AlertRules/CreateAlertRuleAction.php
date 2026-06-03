<?php

namespace App\Actions\AlertRules;

use App\Data\AlertRuleData;
use App\Models\AlertRule;
use App\Models\Ticker;

class CreateAlertRuleAction
{
    public function handle(Ticker $ticker, AlertRuleData $data): AlertRule
    {
        return $ticker->rules()->create($data->toAttributes());
    }
}
