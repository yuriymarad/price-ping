<?php

namespace App\Data;

use App\Enums\AlertRuleType;
use App\Enums\PercentDirection;
use App\Enums\ThresholdDirection;
use App\Models\AlertRuleProposal;

readonly class AlertRuleData
{
    public function __construct(
        public AlertRuleType $ruleType,
        public int $cooldownMinutes,
        public ?float $thresholdPrice,
        public ?ThresholdDirection $thresholdDirection,
        public ?float $percentValue,
        public ?int $periodHours,
        public ?PercentDirection $percentDirection,
        public bool $isOneShot,
    ) {}

    /**
     * @param  array<string, mixed>  $data  Validated request / MCP input (enum fields are strings).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ruleType: AlertRuleType::from($data['rule_type']),
            cooldownMinutes: (int) $data['cooldown_minutes'],
            thresholdPrice: isset($data['threshold_price']) ? (float) $data['threshold_price'] : null,
            thresholdDirection: isset($data['threshold_direction']) ? ThresholdDirection::from($data['threshold_direction']) : null,
            percentValue: isset($data['percent_value']) ? (float) $data['percent_value'] : null,
            periodHours: isset($data['period_hours']) ? (int) $data['period_hours'] : null,
            percentDirection: isset($data['percent_direction']) ? PercentDirection::from($data['percent_direction']) : null,
            isOneShot: (bool) ($data['is_one_shot'] ?? false),
        );
    }

    public static function fromProposal(AlertRuleProposal $proposal): self
    {
        return new self(
            ruleType: $proposal->rule_type,
            cooldownMinutes: $proposal->cooldown_minutes,
            thresholdPrice: $proposal->threshold_price !== null ? (float) $proposal->threshold_price : null,
            thresholdDirection: $proposal->threshold_direction,
            percentValue: $proposal->percent_value !== null ? (float) $proposal->percent_value : null,
            periodHours: $proposal->period_hours,
            percentDirection: $proposal->percent_direction,
            isOneShot: (bool) $proposal->is_one_shot,
        );
    }

    /**
     * @return array<string, mixed> Rule-definition attributes for create()/update().
     */
    public function toAttributes(): array
    {
        return [
            'rule_type' => $this->ruleType,
            'cooldown_minutes' => $this->cooldownMinutes,
            'threshold_price' => $this->thresholdPrice,
            'threshold_direction' => $this->thresholdDirection,
            'percent_value' => $this->percentValue,
            'period_hours' => $this->periodHours,
            'percent_direction' => $this->percentDirection,
            'is_one_shot' => $this->isOneShot,
        ];
    }
}
