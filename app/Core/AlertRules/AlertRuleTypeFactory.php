<?php

namespace App\Core\AlertRules;

use App\Contracts\AlertRuleTypeHandler;
use App\Core\AlertRules\PercentChangeRule\PercentChangeRuleHandler;
use App\Core\AlertRules\ThresholdRule\ThresholdRuleHandler;
use App\Enums\AlertRuleType;

class AlertRuleTypeFactory
{
    public function __construct(
        private ThresholdRuleHandler $thresholdRuleHandler,
        private PercentChangeRuleHandler $percentChangeRuleHandler,
    ) {}

    public function make(AlertRuleType $type): AlertRuleTypeHandler
    {
        return match ($type) {
            AlertRuleType::Threshold => $this->thresholdRuleHandler,
            AlertRuleType::PercentChange => $this->percentChangeRuleHandler,
        };
    }
}
