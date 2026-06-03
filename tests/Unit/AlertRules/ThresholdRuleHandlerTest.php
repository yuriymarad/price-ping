<?php

namespace Tests\Unit\AlertRules;

use App\Core\AlertRules\ThresholdRule\ThresholdRuleHandler;
use App\Data\AlertRuleData;
use App\Enums\AlertRuleType;
use App\Enums\ThresholdDirection;
use App\Models\AlertRule;
use App\Models\Ticker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThresholdRuleHandlerTest extends TestCase
{
    use RefreshDatabase;

    private ThresholdRuleHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = app(ThresholdRuleHandler::class);
    }

    public function test_above_triggers_when_price_rises_past_threshold(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        $this->assertTrue($this->handler->evaluate($rule, 105.0));
    }

    public function test_above_does_not_trigger_at_the_threshold(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        // Strict inequality: a price exactly equal to the threshold must not fire.
        $this->assertFalse($this->handler->evaluate($rule, 100.0));
    }

    public function test_above_does_not_trigger_when_price_is_below_threshold(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        $this->assertFalse($this->handler->evaluate($rule, 95.0));
    }

    public function test_below_triggers_when_price_drops_past_threshold(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Below)
            ->create();

        $this->assertTrue($this->handler->evaluate($rule, 95.0));
    }

    public function test_below_does_not_trigger_at_the_threshold(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Below)
            ->create();

        $this->assertFalse($this->handler->evaluate($rule, 100.0));
    }

    public function test_below_does_not_trigger_when_price_is_above_threshold(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Below)
            ->create();

        $this->assertFalse($this->handler->evaluate($rule, 105.0));
    }

    public function test_format_alert_body_for_above_rule(): void
    {
        $ticker = Ticker::factory()->create(['currency' => 'USD']);
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        $this->assertSame(
            'Price rose above USD 100.00 (current: USD 105.00)',
            $this->handler->formatAlertBody($rule, $ticker, 105.0),
        );
    }

    public function test_format_alert_body_for_below_rule(): void
    {
        $ticker = Ticker::factory()->create(['currency' => 'USD']);
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Below)
            ->create();

        $this->assertSame(
            'Price dropped below USD 100.00 (current: USD 95.00)',
            $this->handler->formatAlertBody($rule, $ticker, 95.0),
        );
    }

    public function test_apply_duplicate_scope_matches_only_same_price_and_direction(): void
    {
        $ticker = Ticker::factory()->create();

        $match = AlertRule::factory()
            ->for($ticker)
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        // Differs by price.
        AlertRule::factory()
            ->for($ticker)
            ->threshold(120.0, ThresholdDirection::Above)
            ->create();

        // Differs by direction.
        AlertRule::factory()
            ->for($ticker)
            ->threshold(100.0, ThresholdDirection::Below)
            ->create();

        $data = AlertRuleData::fromArray([
            'rule_type' => AlertRuleType::Threshold->value,
            'cooldown_minutes' => 60,
            'threshold_price' => 100.0,
            'threshold_direction' => ThresholdDirection::Above->value,
        ]);

        $scoped = $this->handler->applyDuplicateScope(AlertRule::query(), $data)->get();

        $this->assertCount(1, $scoped);
        $this->assertTrue($scoped->first()->is($match));
    }

    public function test_extra_alert_update_data_is_empty(): void
    {
        $this->assertSame([], $this->handler->extraAlertUpdateData(123.45));
    }
}
