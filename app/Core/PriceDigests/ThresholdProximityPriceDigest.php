<?php

namespace App\Core\PriceDigests;

use App\Contracts\PriceDigestBuilder;
use App\Data\OutgoingNotification;
use App\Data\ThresholdProximityEntry;
use App\Enums\AlertRuleType;
use App\Enums\ThresholdDirection;
use App\Models\Ticker;
use App\Values\PercentChange;
use App\Values\Price;
use Override;

class ThresholdProximityPriceDigest implements PriceDigestBuilder
{
    #[Override]
    public function build(): ?OutgoingNotification
    {
        ['below' => $below, 'above' => $above] = $this->gather();

        if ($below === [] && $above === []) {
            return null;
        }

        return new OutgoingNotification(
            title: '📊 Daily Threshold Proximity',
            body: $this->format($below, $above),
        );
    }

    /** @return array{below: ThresholdProximityEntry[], above: ThresholdProximityEntry[]} */
    private function gather(): array
    {
        $tickers = Ticker::withPrice()
            ->with(['rules' => fn ($rule) => $rule->active()->ofType(AlertRuleType::Threshold)])
            ->get();

        $groups = [ThresholdDirection::Below->value => [], ThresholdDirection::Above->value => []];

        foreach ($tickers as $ticker) {
            foreach ($ticker->rules as $rule) {
                if ($rule->threshold_price === null) {
                    continue;
                }

                $rawPct = PercentChange::between((float) $rule->threshold_price, (float) $ticker->last_price);
                $distancePct = $rule->threshold_direction === ThresholdDirection::Above
                    ? new PercentChange(-$rawPct->value)
                    : $rawPct;

                if (abs($distancePct->value) > 10) {
                    continue;
                }

                $groups[$rule->threshold_direction->value][] = new ThresholdProximityEntry($ticker, $rule, $distancePct);
            }
        }

        foreach ($groups as &$group) {
            usort($group, fn ($a, $b) => abs($a->distancePct->value) <=> abs($b->distancePct->value));
        }

        return [
            'below' => $groups[ThresholdDirection::Below->value],
            'above' => $groups[ThresholdDirection::Above->value],
        ];
    }

    /**
     * @param  ThresholdProximityEntry[]  $belowEntries
     * @param  ThresholdProximityEntry[]  $aboveEntries
     */
    private function format(array $belowEntries, array $aboveEntries): string
    {
        $lines = [];

        if ($belowEntries !== []) {
            $lines[] = '🔻 Close to buy zone:';
            foreach ($belowEntries as $entry) {
                $lines[] = sprintf(
                    '• %s  %s  %s%%',
                    $entry->ticker->symbol,
                    new Price((float) $entry->rule->threshold_price, $entry->ticker->currency)->format(),
                    $entry->distancePct->format(),
                );
            }
        }

        if ($aboveEntries !== []) {
            if ($lines !== []) {
                $lines[] = '';
            }
            $lines[] = '🔺 Close to sell zone:';
            foreach ($aboveEntries as $entry) {
                $lines[] = sprintf(
                    '• %s  %s  %s%%',
                    $entry->ticker->symbol,
                    new Price((float) $entry->rule->threshold_price, $entry->ticker->currency)->format(),
                    $entry->distancePct->format(),
                );
            }
        }

        return implode("\n", $lines);
    }
}
