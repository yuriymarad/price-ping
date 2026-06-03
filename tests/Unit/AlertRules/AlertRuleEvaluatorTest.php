<?php

namespace Tests\Unit\AlertRules;

use App\Core\AlertRules\AlertRuleEvaluator;
use App\Data\TriggeredAlert;
use App\Enums\ThresholdDirection;
use App\Models\AlertRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AlertRuleEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    private AlertRuleEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->evaluator = app(AlertRuleEvaluator::class);
    }

    public function test_returns_triggered_alert_for_passing_rule(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        $priceMap = new Collection([$rule->ticker_id => ['price' => 150.0]]);

        $triggered = $this->evaluator->evaluate(new Collection([$rule]), $priceMap);

        $this->assertCount(1, $triggered);
        $this->assertInstanceOf(TriggeredAlert::class, $triggered[0]);
        $this->assertSame($rule->id, $triggered[0]->ruleId);
        $this->assertSame($rule->ticker_id, $triggered[0]->tickerId);
        $this->assertEqualsWithDelta(150.0, $triggered[0]->currentPrice, 0.0001);
    }

    public function test_skips_rule_without_a_price_entry(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        $triggered = $this->evaluator->evaluate(new Collection([$rule]), new Collection);

        $this->assertSame([], $triggered);
    }

    public function test_skips_rule_on_cooldown_even_when_threshold_is_met(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->alertedAt(now())
            ->create(['cooldown_minutes' => 60]);

        $priceMap = new Collection([$rule->ticker_id => ['price' => 150.0]]);

        $triggered = $this->evaluator->evaluate(new Collection([$rule]), $priceMap);

        $this->assertSame([], $triggered);
    }
}
