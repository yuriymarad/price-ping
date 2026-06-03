<?php

namespace Database\Factories;

use App\Enums\AlertRuleType;
use App\Enums\PercentDirection;
use App\Enums\ThresholdDirection;
use App\Models\AlertRule;
use App\Models\Ticker;
use App\Values\PriceBaseline;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertRule>
 */
class AlertRuleFactory extends Factory
{
    /**
     * Define the model's default state: an active, repeating threshold rule.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticker_id' => Ticker::factory(),
            'rule_type' => AlertRuleType::Threshold,
            'is_active' => true,
            'is_one_shot' => false,
            'threshold_price' => $this->faker->randomFloat(4, 1, 1000),
            'threshold_direction' => ThresholdDirection::Above,
            'percent_value' => null,
            'period_hours' => null,
            'percent_direction' => null,
            'cooldown_minutes' => 60,
            'last_alerted_at' => null,
        ];
    }

    public function threshold(float $price, ThresholdDirection $direction): static
    {
        return $this->state(fn (array $attributes): array => [
            'rule_type' => AlertRuleType::Threshold,
            'threshold_price' => $price,
            'threshold_direction' => $direction,
            'percent_value' => null,
            'period_hours' => null,
            'percent_direction' => null,
        ]);
    }

    public function percentChange(float $percentValue, int $periodHours, PercentDirection $direction): static
    {
        return $this->state(fn (array $attributes): array => [
            'rule_type' => AlertRuleType::PercentChange,
            'percent_value' => $percentValue,
            'period_hours' => $periodHours,
            'percent_direction' => $direction,
            'threshold_price' => null,
            'threshold_direction' => null,
        ]);
    }

    public function withBaseline(float $price, ?CarbonInterface $recordedAt = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'baseline' => new PriceBaseline($price, $recordedAt ?? now()),
        ]);
    }

    public function withoutBaseline(): static
    {
        return $this->state(fn (array $attributes): array => [
            'baseline' => null,
        ]);
    }

    public function oneShot(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_one_shot' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function alertedAt(CarbonInterface $at): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_alerted_at' => $at,
        ]);
    }
}
