<?php

namespace App\Enums;

enum PercentDirection: string
{
    case Up = 'up';
    case Down = 'down';
    case Either = 'either';

    public function isChangeValid(float $changePercent): bool
    {
        return match ($this) {
            self::Up => $changePercent > 0,
            self::Down => $changePercent < 0,
            self::Either => true,
        };
    }
}
