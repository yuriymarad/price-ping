<?php

namespace App\Core\AlertRules\PercentChangeRule\Stages;

use App\Data\PercentChangeEvaluationContext;
use App\Values\PriceBaseline;
use Closure;

class ResetExpiredBaselineStage
{
    public function handle(PercentChangeEvaluationContext $context, Closure $next): mixed
    {
        $needsReset = $context->rule->baseline === null
            || $context->rule->baseline->isExpired($context->rule->period_hours);

        if ($needsReset) {
            $context->rule->update(['baseline' => new PriceBaseline($context->currentPrice, now())]);

            return $context;
        }

        return $next($context);
    }
}
