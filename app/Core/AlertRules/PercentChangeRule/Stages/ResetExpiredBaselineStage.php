<?php

namespace App\Core\AlertRules\PercentChangeRule\Stages;

use App\Data\PercentChangeEvaluationContext;
use Closure;

class ResetExpiredBaselineStage
{
    public function handle(PercentChangeEvaluationContext $context, Closure $next): mixed
    {
        if ($context->rule->baselineNeedsReset()) {
            $context->rule->resetBaseline($context->currentPrice);

            return $context;
        }

        return $next($context);
    }
}
