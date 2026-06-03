<?php

namespace App\Values;

readonly class Price
{
    public function __construct(
        public float $amount,
        public ?string $currency = null,
    ) {}

    public function format(int $decimals = 2): string
    {
        $formatted = number_format($this->amount, $decimals);

        return $this->currency !== null && $this->currency !== ''
            ? "{$this->currency} {$formatted}"
            : $formatted;
    }
}
