<?php

namespace App\Core\AlertRules\ThresholdRule;

use App\Contracts\AlertRuleTypeHandler;
use App\Data\AlertRuleData;
use App\Enums\ThresholdDirection;
use App\Models\AlertRule;
use App\Models\Ticker;
use App\Values\Price;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ThresholdRuleHandler implements AlertRuleTypeHandler
{
    #[Override]
    public function evaluate(AlertRule $rule, float $currentPrice): bool
    {
        return $rule->threshold_direction->isMet($currentPrice, (float) $rule->threshold_price);
    }

    #[Override]
    public function formatAlertBody(AlertRule $rule, Ticker $ticker, float $currentPrice): string
    {
        return sprintf(
            'Price %s %s (current: %s)',
            $rule->threshold_direction === ThresholdDirection::Above ? 'rose above' : 'dropped below',
            new Price((float) $rule->threshold_price, $ticker->currency)->format(),
            new Price($currentPrice, $ticker->currency)->format(),
        );
    }

    #[Override]
    public function applyDuplicateScope(Builder $query, AlertRuleData $data): Builder
    {
        return $query
            ->where('threshold_price', $data->thresholdPrice)
            ->where('threshold_direction', $data->thresholdDirection);
    }

    #[Override]
    public function extraAlertUpdateData(float $price): array
    {
        return [];
    }
}
