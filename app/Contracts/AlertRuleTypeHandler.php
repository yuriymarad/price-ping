<?php

namespace App\Contracts;

use App\Data\AlertRuleData;
use App\Models\AlertRule;
use App\Models\Ticker;
use Illuminate\Database\Eloquent\Builder;

interface AlertRuleTypeHandler
{
    public function evaluate(AlertRule $rule, float $currentPrice): bool;

    public function formatAlertBody(AlertRule $rule, Ticker $ticker, float $currentPrice): string;

    public function applyDuplicateScope(Builder $query, AlertRuleData $data): Builder;

    public function extraAlertUpdateData(float $price): array;
}
