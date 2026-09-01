<?php

namespace App\Actions\AlertRules;

use App\Data\AlertRuleData;
use App\Models\AlertRule;

class UpdateAlertRuleAction
{
    public function handle(AlertRule $rule, AlertRuleData $data): void
    {
        $rule->applyChanges($data);
    }
}
