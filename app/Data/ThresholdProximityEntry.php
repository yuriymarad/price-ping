<?php

namespace App\Data;

use App\Models\AlertRule;
use App\Models\Ticker;
use App\Values\PercentChange;

readonly class ThresholdProximityEntry
{
    public function __construct(
        public Ticker $ticker,
        public AlertRule $rule,
        public PercentChange $distancePct,
    ) {}
}
