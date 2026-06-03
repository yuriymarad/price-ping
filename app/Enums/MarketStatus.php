<?php

namespace App\Enums;

enum MarketStatus: string
{
    case Open = 'open';
    case PreMarket = 'pre';
    case AfterHours = 'post';
    case Closed = 'closed';
}
