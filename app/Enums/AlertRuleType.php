<?php

namespace App\Enums;

enum AlertRuleType: string
{
    case Threshold = 'threshold';
    case PercentChange = 'percent_change';
}
