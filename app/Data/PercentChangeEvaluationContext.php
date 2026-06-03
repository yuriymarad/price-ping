<?php

namespace App\Data;

use App\Models\AlertRule;

class PercentChangeEvaluationContext
{
    public bool $passed = false;

    public function __construct(
        public readonly AlertRule $rule,
        public readonly float $currentPrice,
    ) {}
}
