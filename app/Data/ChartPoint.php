<?php

namespace App\Data;

readonly class ChartPoint
{
    public function __construct(
        public int $time,
        public float $value,
    ) {}
}
