<?php

namespace App\Enums;

enum ThresholdDirection: string
{
    case Above = 'above';
    case Below = 'below';

    public function isMet(float $price, float $threshold): bool
    {
        return match ($this) {
            self::Above => $price > $threshold,
            self::Below => $price < $threshold,
        };
    }
}
