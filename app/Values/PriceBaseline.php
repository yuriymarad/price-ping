<?php

namespace App\Values;

use Carbon\CarbonInterface;

readonly class PriceBaseline
{
    public function __construct(
        public float $price,
        public CarbonInterface $recordedAt,
    ) {}

    public function isExpired(int $periodHours): bool
    {
        return $this->recordedAt->copy()->addHours($periodHours)->isPast();
    }
}
