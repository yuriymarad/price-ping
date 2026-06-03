<?php

namespace App\Enums;

enum ChartRange: string
{
    case OneDay = '1d';
    case OneMonth = '1mo';
    case SixMonths = '6mo';
    case OneYear = '1y';
    case TwoYears = '2y';
    case FiveYears = '5y';
    case TenYears = '10y';

    public function interval(): string
    {
        return match ($this) {
            self::OneDay => '5m',
            default => '1d',
        };
    }
}
