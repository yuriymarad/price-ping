<script setup lang="ts">
import { computed } from 'vue';
import type { AlertRule, Ticker } from '@/types';

type Props = {
    tickers: Ticker[];
};

const props = defineProps<Props>();

const emit = defineEmits<{
    selectTicker: [ticker: Ticker];
}>();

type ProximityEntry = {
    ticker: Ticker;
    rule: AlertRule;
    distancePct: number;
};

function buildEntries(direction: 'above' | 'below'): ProximityEntry[] {
    const entries: ProximityEntry[] = [];

    for (const ticker of props.tickers) {
        if (!ticker.last_price) {
            continue;
        }
        const lastPrice = Number(ticker.last_price);

        for (const rule of ticker.rules) {
            if (rule.rule_type !== 'threshold' || rule.threshold_price === null || rule.threshold_direction !== direction) {
                continue;
            }
            const thresholdPrice = Number(rule.threshold_price);
            const rawPct = ((lastPrice - thresholdPrice) / thresholdPrice) * 100;
            const distancePct = direction === 'below' ? rawPct : -rawPct;
            entries.push({ ticker, rule, distancePct });
        }
    }

    return entries.filter((e) => Math.abs(e.distancePct) <= 20).sort((a, b) => Math.abs(a.distancePct) - Math.abs(b.distancePct));
}

const belowEntries = computed(() => buildEntries('below'));
const aboveEntries = computed(() => buildEntries('above'));

function distanceClass(distancePct: number): string {
    const abs = Math.abs(distancePct);
    if (abs < 5) {
        return 'text-green-600 dark:text-green-400 font-semibold';
    }
    if (abs < 20) {
        return 'text-amber-500 dark:text-amber-400';
    }
    return 'text-muted-foreground';
}

function formatDistancePct(distancePct: number): string {
    return `${distancePct.toFixed(2)}%`;
}

function rulePrice(rule: AlertRule): string {
    return `$${Number(rule.threshold_price).toFixed(2)}`;
}
</script>

<template>
    <div
        v-if="belowEntries.length > 0 || aboveEntries.length > 0"
        class="grid gap-4 sm:grid-cols-2"
    >
        <div v-if="belowEntries.length > 0" class="flex flex-col gap-3 rounded-sm p-4 bg-amber-500/5">
            <span class="flex items-center gap-1 text-sm font-semibold">
                <span class="text-red-500">▼</span>
                Close to buy zone
            </span>
            <!-- max-h-13 clips to 2 rows of chips; farthest (sorted last) overflow and hide -->
            <div class="flex flex-wrap items-center gap-x-1 gap-y-1.5 overflow-hidden max-h-13">
                <template v-for="(entry, i) in belowEntries" :key="`${entry.ticker.id}-${entry.rule.id}`">
                    <span v-if="i > 0" class="text-xs text-muted-foreground/40">·</span>
                    <button
                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs cursor-pointer hover:bg-accent/50 transition-colors"
                        @click="emit('selectTicker', entry.ticker)"
                    >
                        <a
                            :href="`https://www.tradingview.com/symbols/${entry.ticker.symbol}/?timeframe=6M`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold hover:underline"
                            @click.stop
                        >{{ entry.ticker.symbol }}</a>
                        <span class="text-muted-foreground">{{ rulePrice(entry.rule) }}</span>
                        <span :class="['tabular-nums', distanceClass(entry.distancePct)]">
                            {{ formatDistancePct(entry.distancePct) }}
                        </span>
                    </button>
                </template>
            </div>
        </div>

        <div v-if="aboveEntries.length > 0" class="flex flex-col gap-3 rounded-sm p-4 bg-green-500/5">
            <span class="flex items-center gap-1 text-sm font-semibold">
                <span class="text-green-500">▲</span>
                Close to sell zone
            </span>
            <!-- max-h-13 clips to 2 rows of chips; farthest (sorted last) overflow and hide -->
            <div class="flex flex-wrap items-center gap-x-1 gap-y-1.5 overflow-hidden max-h-13">
                <template v-for="(entry, i) in aboveEntries" :key="`${entry.ticker.id}-${entry.rule.id}`">
                    <span v-if="i > 0" class="text-xs text-muted-foreground/40">·</span>
                    <button
                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs cursor-pointer hover:bg-accent/50 transition-colors"
                        @click="emit('selectTicker', entry.ticker)"
                    >
                        <a
                            :href="`https://www.tradingview.com/symbols/${entry.ticker.symbol}/?timeframe=6M`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold hover:underline"
                            @click.stop
                        >{{ entry.ticker.symbol }}</a>
                        <span class="text-muted-foreground">{{ rulePrice(entry.rule) }}</span>
                        <span :class="['tabular-nums', distanceClass(entry.distancePct)]">
                            {{ formatDistancePct(entry.distancePct) }}
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>
