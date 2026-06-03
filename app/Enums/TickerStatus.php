<?php

namespace App\Enums;

enum TickerStatus: string
{
    case InReview = 'in_review';
    case SetupComplete = 'setup_complete';
}
