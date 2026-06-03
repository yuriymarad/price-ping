<?php

namespace App\Models;

use App\Casts\PriceBaselineCast;
use App\Enums\AlertRuleType;
use App\Enums\PercentDirection;
use App\Enums\ThresholdDirection;
use Database\Factories\AlertRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticker_id', 'rule_type', 'is_active', 'is_one_shot',
    'threshold_price', 'threshold_direction',
    'percent_value', 'period_hours', 'percent_direction',
    'baseline',
    'cooldown_minutes', 'last_alerted_at',
])]
class AlertRule extends Model
{
    /** @use HasFactory<AlertRuleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rule_type' => AlertRuleType::class,
            'is_active' => 'boolean',
            'is_one_shot' => 'boolean',
            'threshold_direction' => ThresholdDirection::class,
            'percent_direction' => PercentDirection::class,
            'threshold_price' => 'decimal:4',
            'percent_value' => 'decimal:4',
            'baseline' => PriceBaselineCast::class,
            'last_alerted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Ticker, $this>
     */
    public function ticker(): BelongsTo
    {
        return $this->belongsTo(Ticker::class, 'ticker_id');
    }

    public function isOnCooldown(): bool
    {
        return $this->last_alerted_at !== null
            && $this->last_alerted_at->diffInMinutes(now()) < $this->cooldown_minutes;
    }

    public function recordAlert(array $extra = []): void
    {
        $updates = array_merge(['last_alerted_at' => now()], $extra);

        if ($this->is_one_shot) {
            $updates['is_active'] = false;
        }

        $this->update($updates);
    }

    public function toggle(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }

    #[Scope]
    public function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    public function ofType(Builder $query, AlertRuleType $type): void
    {
        $query->where('rule_type', $type);
    }
}
