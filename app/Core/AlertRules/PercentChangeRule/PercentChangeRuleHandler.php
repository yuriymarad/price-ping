<?php

namespace App\Core\AlertRules\PercentChangeRule;

use App\Contracts\AlertRuleTypeHandler;
use App\Core\AlertRules\PercentChangeRule\Stages\EvaluatePercentThresholdStage;
use App\Core\AlertRules\PercentChangeRule\Stages\ResetExpiredBaselineStage;
use App\Core\AlertRules\PercentChangeRule\Stages\SlideBaselineStage;
use App\Data\AlertRuleData;
use App\Data\PercentChangeEvaluationContext;
use App\Enums\PercentDirection;
use App\Models\AlertRule;
use App\Models\Ticker;
use App\Values\Price;
use App\Values\PriceBaseline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use Override;

class PercentChangeRuleHandler implements AlertRuleTypeHandler
{
    #[Override]
    public function evaluate(AlertRule $rule, float $currentPrice): bool
    {
        $context = app(Pipeline::class)
            ->send(new PercentChangeEvaluationContext($rule, $currentPrice))
            ->through([
                ResetExpiredBaselineStage::class,
                SlideBaselineStage::class,
                EvaluatePercentThresholdStage::class,
            ])
            ->thenReturn();

        return $context->passed;
    }

    #[Override]
    public function formatAlertBody(AlertRule $rule, Ticker $ticker, float $currentPrice): string
    {
        return sprintf(
            'Price moved ≥%s%% %s in %sh (current: %s, baseline: %s)',
            rtrim(rtrim((string) $rule->percent_value, '0'), '.'),
            $rule->percent_direction === PercentDirection::Either ? '(any direction)' : $rule->percent_direction->value,
            $rule->period_hours,
            new Price($currentPrice, $ticker->currency)->format(),
            new Price($rule->baseline?->price ?? 0.0, $ticker->currency)->format(),
        );
    }

    #[Override]
    public function applyDuplicateScope(Builder $query, AlertRuleData $data): Builder
    {
        return $query
            ->where('percent_value', $data->percentValue)
            ->where('period_hours', $data->periodHours)
            ->where('percent_direction', $data->percentDirection);
    }

    #[Override]
    public function extraAlertUpdateData(float $price): array
    {
        return ['baseline' => new PriceBaseline($price, now())];
    }
}
