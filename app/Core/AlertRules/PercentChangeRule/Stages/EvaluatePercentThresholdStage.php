<?php

namespace App\Core\AlertRules\PercentChangeRule\Stages;

use App\Data\PercentChangeEvaluationContext;
use App\Values\PercentChange;
use Closure;

class EvaluatePercentThresholdStage
{
    public function handle(PercentChangeEvaluationContext $context, Closure $next): mixed
    {
        $changePercent = PercentChange::between($context->rule->baseline->price, $context->currentPrice);

        if ($changePercent->abs()->value < (float) $context->rule->percent_value) {
            return $context;
        }

        $context->passed = $context->rule->percent_direction->isChangeValid($changePercent->value);

        return $next($context);
    }
}
