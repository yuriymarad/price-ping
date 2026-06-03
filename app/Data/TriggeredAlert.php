<?php

namespace App\Data;

readonly class TriggeredAlert
{
    public function __construct(
        public int $ruleId,
        public int $tickerId,
        public float $currentPrice,
    ) {}
}
