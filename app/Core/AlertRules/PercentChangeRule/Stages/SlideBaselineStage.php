<?php

namespace App\Core\AlertRules\PercentChangeRule\Stages;

use App\Data\PercentChangeEvaluationContext;
use App\Enums\PercentDirection;
use Closure;

class SlideBaselineStage
{
    public function handle(PercentChangeEvaluationContext $context, Closure $next): mixed
    {
        $baselinePrice = $context->rule->baseline->price;

        if ($baselinePrice == 0.0) {
            return $context;
        }

        $slidesAgainstDirection = match ($context->rule->percent_direction) {
            PercentDirection::Down => $context->currentPrice > $baselinePrice,
            PercentDirection::Up => $context->currentPrice < $baselinePrice,
            default => false,
        };

        if ($slidesAgainstDirection) {
            $context->rule->moveBaselinePriceTo($context->currentPrice);
        }

        return $next($context);
    }
}
