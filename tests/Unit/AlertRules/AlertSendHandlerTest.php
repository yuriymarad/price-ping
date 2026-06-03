<?php

namespace Tests\Unit\AlertRules;

use App\Contracts\NotificationSender;
use App\Core\AlertRules\AlertSendHandler;
use App\Data\OutgoingNotification;
use App\Data\TriggeredAlert;
use App\Enums\PercentDirection;
use App\Enums\ThresholdDirection;
use App\Models\AlertRule;
use App\Models\Ticker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AlertSendHandlerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Recording fake that captures every notification handed to the sender.
     */
    private NotificationSender $sender;

    private AlertSendHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-05-27 12:00:00'));

        $this->sender = new class implements NotificationSender
        {
            /** @var OutgoingNotification[] */
            public array $sent = [];

            public function send(OutgoingNotification $message): bool
            {
                $this->sent[] = $message;

                return true;
            }
        };

        $this->app->instance(NotificationSender::class, $this->sender);
        $this->handler = app(AlertSendHandler::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_sends_notification_and_records_threshold_alert(): void
    {
        $ticker = Ticker::factory()->create([
            'symbol' => 'AAPL',
            'long_name' => 'Apple Inc.',
            'currency' => 'USD',
        ]);
        $rule = AlertRule::factory()
            ->for($ticker)
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        $this->handler->process([
            new TriggeredAlert(ruleId: $rule->id, tickerId: $ticker->id, currentPrice: 105.0),
        ]);

        $this->assertCount(1, $this->sender->sent);
        $this->assertSame('📈 Price Alert: AAPL (Apple Inc.)', $this->sender->sent[0]->title);
        $this->assertSame(
            'Price rose above USD 100.00 (current: USD 105.00)',
            $this->sender->sent[0]->body,
        );
        $this->assertTrue($rule->fresh()->last_alerted_at->equalTo(now()));
    }

    public function test_skips_alert_when_rule_is_missing(): void
    {
        $ticker = Ticker::factory()->create();

        $this->handler->process([
            new TriggeredAlert(ruleId: 999, tickerId: $ticker->id, currentPrice: 105.0),
        ]);

        $this->assertSame([], $this->sender->sent);
    }

    public function test_skips_alert_when_ticker_is_missing(): void
    {
        $rule = AlertRule::factory()
            ->threshold(100.0, ThresholdDirection::Above)
            ->create();

        $this->handler->process([
            new TriggeredAlert(ruleId: $rule->id, tickerId: 999, currentPrice: 105.0),
        ]);

        $this->assertSame([], $this->sender->sent);
        // The alert was skipped before recordAlert could run.
        $this->assertNull($rule->fresh()->last_alerted_at);
    }

    public function test_records_extra_update_data_for_percent_change_rule(): void
    {
        $ticker = Ticker::factory()->create(['currency' => 'USD']);
        $rule = AlertRule::factory()
            ->for($ticker)
            ->percentChange(5, 24, PercentDirection::Either)
            ->withBaseline(100.0)
            ->create();

        $this->handler->process([
            new TriggeredAlert(ruleId: $rule->id, tickerId: $ticker->id, currentPrice: 106.0),
        ]);

        $this->assertCount(1, $this->sender->sent);
        // extraAlertUpdateData() resets the baseline to the price that triggered the alert.
        $this->assertEqualsWithDelta(106.0, $rule->fresh()->baseline->price, 0.0001);
    }

    public function test_deactivates_one_shot_rule_after_sending(): void
    {
        $ticker = Ticker::factory()->create();
        $rule = AlertRule::factory()
            ->for($ticker)
            ->threshold(100.0, ThresholdDirection::Above)
            ->oneShot()
            ->create();

        $this->handler->process([
            new TriggeredAlert(ruleId: $rule->id, tickerId: $ticker->id, currentPrice: 105.0),
        ]);

        $this->assertCount(1, $this->sender->sent);
        $this->assertFalse($rule->fresh()->is_active);
    }

    public function test_processes_multiple_triggered_alerts(): void
    {
        $tickerA = Ticker::factory()->create();
        $tickerB = Ticker::factory()->create();
        $ruleA = AlertRule::factory()->for($tickerA)->threshold(100.0, ThresholdDirection::Above)->create();
        $ruleB = AlertRule::factory()->for($tickerB)->threshold(50.0, ThresholdDirection::Above)->create();

        $this->handler->process([
            new TriggeredAlert(ruleId: $ruleA->id, tickerId: $tickerA->id, currentPrice: 105.0),
            new TriggeredAlert(ruleId: $ruleB->id, tickerId: $tickerB->id, currentPrice: 55.0),
        ]);

        $this->assertCount(2, $this->sender->sent);
        $this->assertTrue($ruleA->fresh()->last_alerted_at->equalTo(now()));
        $this->assertTrue($ruleB->fresh()->last_alerted_at->equalTo(now()));
    }

    public function test_does_nothing_for_empty_triggered_array(): void
    {
        $this->handler->process([]);

        $this->assertSame([], $this->sender->sent);
    }
}
