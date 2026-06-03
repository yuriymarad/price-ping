<?php

namespace Tests\Unit\AlertRules;

use App\Core\AlertRules\PercentChangeRule\PercentChangeRuleHandler;
use App\Enums\PercentDirection;
use App\Models\AlertRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PercentChangeRuleHandlerTest extends TestCase
{
    use RefreshDatabase;

    private PercentChangeRuleHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-05-27 12:00:00'));
        $this->handler = app(PercentChangeRuleHandler::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_null_baseline_sets_baseline_and_does_not_trigger(): void
    {
        $rule = AlertRule::factory()
            ->percentChange(5, 24, PercentDirection::Either)
            ->withoutBaseline()
            ->create();

        $triggered = $this->handler->evaluate($rule, 100.0);

        $this->assertFalse($triggered);
        $this->assertEqualsWithDelta(100.0, $rule->fresh()->baseline->price, 0.0001);
        $this->assertTrue($rule->fresh()->baseline->recordedAt->equalTo(now()));
    }

    public function test_expired_baseline_is_reset_to_current_price_and_does_not_trigger(): void
    {
        $rule = AlertRule::factory()
            ->percentChange(5, 2, PercentDirection::Either)
            ->withBaseline(100.0, now()->subHours(3))
            ->create();

        // 110 would clear the 5% threshold, but the expired baseline resets first.
        $triggered = $this->handler->evaluate($rule, 110.0);

        $this->assertFalse($triggered);
        $this->assertEqualsWithDelta(110.0, $rule->fresh()->baseline->price, 0.0001);
    }

    public function test_either_direction_triggers_when_magnitude_meets_threshold(): void
    {
        $rule = AlertRule::factory()
            ->percentChange(5, 24, PercentDirection::Either)
            ->withBaseline(100.0)
            ->create();

        $this->assertTrue($this->handler->evaluate($rule, 105.0));
    }

    public function test_does_not_trigger_when_change_is_below_threshold(): void
    {
        $rule = AlertRule::factory()
            ->percentChange(5, 24, PercentDirection::Either)
            ->withBaseline(100.0)
            ->create();

        $this->assertFalse($this->handler->evaluate($rule, 102.0));
    }

    public function test_up_direction_triggers_on_rise_from_baseline(): void
    {
        $rule = AlertRule::factory()
            ->percentChange(5, 24, PercentDirection::Up)
            ->withBaseline(100.0)
            ->create();

        $this->assertTrue($this->handler->evaluate($rule, 106.0));
        // Price rose, so the Up baseline is not slid.
        $this->assertEqualsWithDelta(100.0, $rule->fresh()->baseline->price, 0.0001);
    }

    public function test_up_direction_slides_baseline_down_on_drop_and_does_not_trigger(): void
    {
        $rule = AlertRule::factory()
            ->percentChange(5, 24, PercentDirection::Up)
            ->withBaseline(100.0)
            ->create();

        // An Up rule tracks the trough: the baseline slides down to the new low,
        // so the measured rise is zero and nothing fires.
        $this->assertFalse($this->handler->evaluate($rule, 90.0));
        $this->assertEqualsWithDelta(90.0, $rule->fresh()->baseline->price, 0.0001);
    }

    public function test_down_direction_slides_baseline_up_on_rise_and_does_not_trigger(): void
    {
        $rule = AlertRule::factory()
            ->percentChange(5, 24, PercentDirection::Down)
            ->withBaseline(100.0)
            ->create();

        // A Down rule tracks the peak: the baseline slides up to the new high.
        $this->assertFalse($this->handler->evaluate($rule, 110.0));
        $this->assertEqualsWithDelta(110.0, $rule->fresh()->baseline->price, 0.0001);
    }
}
