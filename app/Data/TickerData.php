<?php

namespace App\Data;

readonly class TickerData
{
    public function __construct(
        public float $regularMarketPrice,
        public ?string $longName = null,
        public ?string $currency = null,
        public ?string $exchangeName = null,
    ) {}
}
