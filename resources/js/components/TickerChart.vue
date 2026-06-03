<script setup lang="ts">
import { AreaSeries, LineStyle, createChart } from 'lightweight-charts';
import type { IChartApi, IPriceLine, ISeriesApi, UTCTimestamp } from 'lightweight-charts';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import TickerController from '@/actions/App/Http/Controllers/TickerController';
import type { Ticker } from '@/types';

type Range = '1d' | '1mo' | '6mo' | '1y' | '2y' | '5y' | '10y';

type Props = {
    ticker: Ticker;
};

const props = defineProps<Props>();

const chartContainer = ref<HTMLElement | null>(null);
const loading = ref(true);
const fetchError = ref(false);
const selectedRange = ref<Range>('6mo');

const ranges: { value: Range; label: string }[] = [
    { value: '1d', label: '1d' },
    { value: '1mo', label: '1mo' },
    { value: '6mo', label: '6mo' },
    { value: '1y', label: '1y' },
    { value: '2y', label: '2y' },
    { value: '5y', label: '5y' },
    { value: '10y', label: '10y' },
];

let chart: IChartApi | null = null;
let series: ISeriesApi<'Area'> | null = null;
let priceLines: IPriceLine[] = [];
let resizeObserver: ResizeObserver | null = null;

function setupChart() {
    if (!chartContainer.value) return;

    chart = createChart(chartContainer.value, {
        layout: {
            background: { color: 'transparent' },
            textColor: '#6b7280',
            attributionLogo: false,
        },
        grid: {
            vertLines: { visible: false },
            horzLines: { visible: false },
        },
        timeScale: {
            borderColor: 'rgba(255, 255, 255, 0.1)',
            timeVisible: true,
            secondsVisible: false,
        },
        rightPriceScale: {
            borderColor: 'rgba(255, 255, 255, 0.1)',
            textColor: '#fff',
        },
        crosshair: {
            vertLine: { color: 'rgba(255, 255, 255, 0.2)' },
            horzLine: { color: 'rgba(255, 255, 255, 0.2)' },
        },
        width: chartContainer.value.clientWidth,
        height: 360,
    });

    series = chart.addSeries(AreaSeries, {
        lineColor: '#f97316',
        topColor: 'rgba(249, 115, 22, 0.25)',
        bottomColor: 'rgba(249, 115, 22, 0.0)',
        lineWidth: 2,
        priceLineVisible: false,
        lastValueVisible: false,
    });

    resizeObserver = new ResizeObserver(() => {
        if (chart && chartContainer.value) {
            chart.applyOptions({ width: chartContainer.value.clientWidth });
        }
    });
    resizeObserver.observe(chartContainer.value);
}

function removePriceLines() {
    if (!series) return;
    for (const line of priceLines) {
        series.removePriceLine(line);
    }
    priceLines = [];
}

function addThresholdLines() {
    if (!series) return;
    for (const rule of props.ticker.rules) {
        if (rule.rule_type !== 'threshold' || rule.threshold_price === null) continue;
        const isBelow = rule.threshold_direction === 'below';
        const price = Number(rule.threshold_price);
        const line = series.createPriceLine({
            price,
            color: isBelow ? '#ef4444' : '#22c55e',
            lineWidth: 1,
            lineStyle: LineStyle.Dashed,
            axisLabelVisible: true,
            title: isBelow ? `🔔 Below $${price.toFixed(2)}` : `🔔 Above $${price.toFixed(2)}`,
        });
        priceLines.push(line);
    }

    if (props.ticker.last_price) {
        const current = Number(props.ticker.last_price);
        priceLines.push(
            series.createPriceLine({
                price: current,
                color: '#f97316',
                lineWidth: 1,
                lineStyle: LineStyle.Dashed,
                axisLabelVisible: true,
                title: 'Current',
            }),
        );
    }

    if (props.ticker.is_in_portfolio && props.ticker.average_price) {
        const avg = Number(props.ticker.average_price);
        const line = series.createPriceLine({
            price: avg,
            color: '#f59e0b',
            lineWidth: 1,
            lineStyle: LineStyle.Dashed,
            axisLabelVisible: true,
            title: `Average`,
        });
        priceLines.push(line);
    }
}

async function fetchAndRender() {
    if (!series) return;
    loading.value = true;
    fetchError.value = false;
    try {
        const url = TickerController.chartData(props.ticker, { query: { range: selectedRange.value } }).url;
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed');

        const data: { time: number; value: number }[] = await response.json();

        series.setData(data.map((d) => ({ time: d.time as UTCTimestamp, value: d.value })));
        chart?.timeScale().fitContent();

        removePriceLines();
        addThresholdLines();
    } catch {
        fetchError.value = true;
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    setupChart();
    await fetchAndRender();
});

onUnmounted(() => {
    resizeObserver?.disconnect();
    chart?.remove();
});

watch(
    () => props.ticker.id,
    async () => {
        removePriceLines();
        series?.setData([]);
        await fetchAndRender();
    },
);

watch(selectedRange, async () => {
    removePriceLines();
    series?.setData([]);
    await fetchAndRender();
});

watch(
    () => [
        props.ticker.rules
            .filter((r) => r.rule_type === 'threshold')
            .map((r) => `${r.id}:${r.threshold_price}:${r.threshold_direction}`)
            .join(','),
        props.ticker.is_in_portfolio,
        props.ticker.average_price,
        props.ticker.last_price,
    ],
    () => {
        removePriceLines();
        addThresholdLines();
    },
);
</script>

<template>
    <div class="rounded-sm bg-muted/60 overflow-hidden">
        <div class="px-4 pt-4 pb-1 flex items-center gap-2">
            <span class="font-bold text-sm">
                {{ ticker.symbol }}
                <span class="text-gray-400">Price History</span>
            </span>
            <div class="flex items-center gap-1 ml-4">
                <button
                    v-for="r in ranges"
                    :key="r.value"
                    :class="[
                        'px-2 py-0.5 rounded text-xs font-medium transition-colors',
                        selectedRange === r.value
                            ? 'bg-indigo-500 text-white'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent',
                    ]"
                    @click="selectedRange = r.value"
                >
                    {{ r.label }}
                </button>
            </div>
        </div>
        <div class="relative">
            <div ref="chartContainer" class="w-full" />
            <div
                v-if="loading"
                class="absolute inset-0 h-[260px] bg-muted/20 animate-pulse"
            />
            <div
                v-else-if="fetchError"
                class="absolute inset-0 h-[260px] flex items-center justify-center text-sm text-muted-foreground"
            >
                Failed to load price data
            </div>
        </div>
    </div>
</template>
