<?php

namespace App\Models;

use App\Enums\AlertRuleType;
use App\Enums\PercentDirection;
use App\Enums\ThresholdDirection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticker_id', 'rule_type', 'is_one_shot',
    'threshold_price', 'threshold_direction',
    'percent_value', 'period_hours', 'percent_direction',
    'cooldown_minutes',
])]
class AlertRuleProposal extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rule_type' => AlertRuleType::class,
            'is_one_shot' => 'boolean',
            'threshold_direction' => ThresholdDirection::class,
            'percent_direction' => PercentDirection::class,
            'threshold_price' => 'decimal:4',
            'percent_value' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Ticker, $this>
     */
    public function ticker(): BelongsTo
    {
        return $this->belongsTo(Ticker::class, 'ticker_id');
    }
}
