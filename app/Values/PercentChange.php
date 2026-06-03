<?php

namespace App\Values;

readonly class PercentChange
{
    public function __construct(
        public float $value,
    ) {}

    public static function between(float $from, float $to): self
    {
        return new self((($to - $from) / $from) * 100);
    }

    public function abs(): self
    {
        return new self(abs($this->value));
    }

    public function format(): string
    {
        return number_format($this->value, 2);
    }
}
