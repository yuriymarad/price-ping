<?php

namespace App\Actions\AlertRules;

use App\Data\AlertRuleData;
use App\Models\AlertRule;

class UpdateAlertRuleAction
{
    public function handle(AlertRule $rule, AlertRuleData $data): void
    {
        $attributes = $data->toAttributes();

        if ($data->periodHours !== $rule->period_hours) {
            $attributes['baseline'] = null;
        }

        $rule->update($attributes);
    }
}
