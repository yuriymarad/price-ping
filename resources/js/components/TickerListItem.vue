<script setup lang="ts">
import { computed } from 'vue';
import { Briefcase, Flame, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate, pnlPercent, pnlUsd } from '@/lib/utils';
import type { Ticker } from '@/types';

type Props = {
    ticker: Ticker;
    isSelected: boolean;
    draggable?: boolean;
};

const props = defineProps<Props>();
defineEmits<{
    select: [];
    delete: [];
    'toggle-hot': [];
    'toggle-portfolio': [];
    dragstart: [event: DragEvent];
    dragend: [event: DragEvent];
}>();

const tickerPnlPercent = computed(() => pnlPercent(props.ticker.last_price, props.ticker.average_price));
const tickerPnlUsd = computed(() => pnlUsd(props.ticker.last_price, props.ticker.average_price, props.ticker.quantity));
</script>

<template>
    <div
        :data-ticker-id="ticker.id"
        :draggable="draggable ?? false"
        :class="[
            'rounded-sm border p-2 cursor-pointer transition-colors hover:bg-accent/40 select-none',
            isSelected ? 'border-indigo-500 bg-indigo-500/10' : 'border-border',
            draggable ? 'cursor-grab active:cursor-grabbing' : '',
        ]"
        @click="$emit('select')"
        @dragstart="$emit('dragstart', $event)"
        @dragend="$emit('dragend', $event)"
    >
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <div class="flex items-center gap-0.5 flex-wrap">
                    <a
                        :href="`https://www.tradingview.com/symbols/${ticker.symbol}/?timeframe=6M`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-bold text-sm leading-tight hover:underline"
                        @click.stop
                    >{{ ticker.symbol }}</a>
                    <Button
                        size="icon"
                        variant="ghost"
                        :class="['h-5 w-5 shrink-0', ticker.is_hot ? 'text-orange-500' : 'text-muted-foreground hover:text-orange-400']"
                        @click.stop="$emit('toggle-hot')"
                        @dragstart.stop
                    >
                        <Flame class="size-3" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        :class="['h-5 w-5 shrink-0', ticker.is_in_portfolio ? 'text-green-500' : 'text-muted-foreground hover:text-green-400']"
                        @click.stop="$emit('toggle-portfolio')"
                        @dragstart.stop
                    >
                        <Briefcase class="size-3" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        class="h-5 w-5 shrink-0 text-destructive hover:text-destructive"
                        @click.stop="$emit('delete')"
                        @dragstart.stop
                    >
                        <Trash2 class="size-3" />
                    </Button>
                </div>
                <p class="text-xs text-muted-foreground truncate">
                    {{ ticker.long_name }}
                </p>
                <Badge
                    v-if="ticker.proposals?.length > 0"
                    class="mt-1 text-xs px-1.5 py-0 h-4 bg-violet-500/10 text-violet-400 border-violet-500/20 hover:bg-violet-500/20"
                >
                    AI Suggest
                </Badge>
            </div>
            <div class="text-right shrink-0">
                <template v-if="ticker.is_in_portfolio && ticker.quantity && ticker.last_price">
                    <div class="relative group inline-block">
                        <span class="text-xs font-semibold whitespace-nowrap cursor-default">
                            {{ ticker.currency }} {{ (Number(ticker.quantity) * Number(ticker.last_price)).toFixed(2) }}
                        </span>
                        <div
                            v-if="ticker.last_fetched_at"
                            class="absolute right-0 top-full mt-1 z-10 hidden group-hover:block bg-popover text-popover-foreground text-xs rounded px-2 py-1 shadow-md whitespace-nowrap border border-border"
                        >
                            {{ formatDate(ticker.last_fetched_at) }}
                        </div>
                    </div>
                    <div
                        v-if="tickerPnlPercent !== null"
                        :class="['text-xs font-medium whitespace-nowrap', tickerPnlPercent >= 0 ? 'text-green-500' : 'text-red-500']"
                    >
                        {{ tickerPnlPercent >= 0 ? '+' : '' }}{{ tickerPnlPercent.toFixed(2) }}%
                        <span v-if="tickerPnlUsd !== null">({{ tickerPnlUsd >= 0 ? '+' : '' }}{{ tickerPnlUsd.toFixed(2) }})</span>
                    </div>
                </template>
                <template v-else>
                    <div v-if="ticker.last_price" class="relative group inline-block">
                        <span class="text-xs font-semibold whitespace-nowrap cursor-default">
                            {{ ticker.currency }} {{ Number(ticker.last_price).toFixed(2) }}
                        </span>
                        <div
                            v-if="ticker.last_fetched_at"
                            class="absolute right-0 top-full mt-1 z-10 hidden group-hover:block bg-popover text-popover-foreground text-xs rounded px-2 py-1 shadow-md whitespace-nowrap border border-border"
                        >
                            {{ formatDate(ticker.last_fetched_at) }}
                        </div>
                    </div>
                    <div class="text-xs font-normal text-muted-foreground">last price</div>
                </template>
            </div>
        </div>
    </div>
</template>
