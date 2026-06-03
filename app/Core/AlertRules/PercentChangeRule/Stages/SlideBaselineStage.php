<?php

namespace App\Core\AlertRules\PercentChangeRule\Stages;

use App\Data\PercentChangeEvaluationContext;
use App\Enums\PercentDirection;
use App\Values\PriceBaseline;
use Closure;

class SlideBaselineStage
{
    public function handle(PercentChangeEvaluationContext $context, Closure $next): mixed
    {
        $baseline = $context->rule->baseline->price;

        if ($baseline == 0.0) {
            return $context;
        }

        if ($context->rule->percent_direction === PercentDirection::Down && $context->currentPrice > $baseline) {
            $context->rule->update(['baseline' => new PriceBaseline($context->currentPrice, $context->rule->baseline->recordedAt)]);
        }

        if ($context->rule->percent_direction === PercentDirection::Up && $context->currentPrice < $baseline) {
            $context->rule->update(['baseline' => new PriceBaseline($context->currentPrice, $context->rule->baseline->recordedAt)]);
        }

        return $next($context);
    }
}
