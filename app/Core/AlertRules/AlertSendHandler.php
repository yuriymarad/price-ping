<?php

namespace App\Core\AlertRules;

use App\Contracts\NotificationSender;
use App\Data\OutgoingNotification;
use App\Data\TriggeredAlert;
use App\Models\AlertRule;
use App\Models\Ticker;

class AlertSendHandler
{
    public function __construct(
        private readonly NotificationSender $notificationSender,
        private readonly AlertRuleTypeFactory $factory,
    ) {}

    /**
     * @param  TriggeredAlert[]  $triggered
     */
    public function process(array $triggered): void
    {
        $ruleIds = array_map(fn (TriggeredAlert $a) => $a->ruleId, $triggered);
        $tickerIds = array_map(fn (TriggeredAlert $a) => $a->tickerId, $triggered);
        $rules = AlertRule::whereIn('id', $ruleIds)->get()->keyBy('id');
        $tickers = Ticker::whereIn('id', $tickerIds)->get()->keyBy('id');

        foreach ($triggered as $alert) {
            $rule = $rules->get($alert->ruleId);
            $ticker = $tickers->get($alert->tickerId);
            $price = $alert->currentPrice;

            if ($rule === null || $ticker === null) {
                continue;
            }

            $ruleType = $this->factory->make($rule->rule_type);
            $name = $ticker->long_name ?? $ticker->symbol;

            $this->notificationSender->send(new OutgoingNotification(
                title: "📈 Price Alert: {$ticker->symbol} ({$name})",
                body: $ruleType->formatAlertBody($rule, $ticker, $price),
            ));

            $rule->recordAlert($ruleType->extraAlertUpdateData($price));
        }
    }
}
