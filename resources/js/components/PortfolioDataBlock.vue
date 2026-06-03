<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Briefcase } from 'lucide-vue-next';
import TickerController from '@/actions/App/Http/Controllers/TickerController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { pnlPercent, pnlUsd } from '@/lib/utils';
import type { Ticker } from '@/types';

type Props = {
    ticker: Ticker;
};

const props = defineProps<Props>();

const form = reactive({
    is_in_portfolio: false,
    quantity: '',
    average_price: '',
    notes: '',
});

function syncForm() {
    form.is_in_portfolio = props.ticker.is_in_portfolio;
    form.quantity = props.ticker.quantity ?? '';
    form.average_price = props.ticker.average_price ?? '';
    form.notes = props.ticker.notes ?? '';
}

syncForm();
watch(() => props.ticker.id, syncForm);

function save() {
    router.patch(
        TickerController.updatePortfolio(props.ticker).url,
        {
            is_in_portfolio: form.is_in_portfolio,
            quantity: form.quantity || null,
            average_price: form.average_price || null,
            notes: form.notes || null,
        },
        { preserveScroll: true },
    );
}

const formPnlPercent = computed(() => pnlPercent(props.ticker.last_price, form.average_price));
const formPnlUsd = computed(() => pnlUsd(props.ticker.last_price, form.average_price, form.quantity));

const positionValue = computed(() => {
    const price = Number(props.ticker.last_price);
    const qty = Number(form.quantity);
    if (!price || !qty) return null;
    return price * qty;
});
</script>

<template>
    <div class="rounded-sm bg-muted/60">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2">
                <Briefcase class="h-4 w-4 text-muted-foreground" />
                <span class="font-medium text-sm">{{ ticker.symbol }} 
                    <span class="text-gray-400">in Portfolio</span> 
                </span>
            </div>
            <div v-if="form.is_in_portfolio && positionValue !== null" class="text-xs">
                <div class="text-muted-foreground text-right">
                    {{ ticker.currency }} {{ positionValue.toFixed(2) }}
                </div>
                <span v-if="formPnlPercent !== null" :class="['font-semibold tabular-nums', formPnlPercent >= 0 ? 'text-green-500' : 'text-red-500']">
                    {{ formPnlPercent >= 0 ? '+' : '' }}{{ formPnlPercent.toFixed(2) }}%
                    <span v-if="formPnlUsd !== null">({{ formPnlUsd >= 0 ? '+' : '' }}{{ formPnlUsd.toFixed(2) }})</span>
                </span>
            </div>
        </div>

        <div class="p-4 flex flex-col gap-3">
            <!-- In portfolio toggle -->
            <label class="flex items-center gap-2.5 cursor-pointer select-none w-fit">
                <button
                    type="button"
                    role="switch"
                    :aria-checked="form.is_in_portfolio"
                    :class="[
                        'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                        form.is_in_portfolio ? 'bg-indigo-500' : 'bg-input',
                    ]"
                    @click="form.is_in_portfolio = !form.is_in_portfolio"
                >
                    <span
                        :class="[
                            'pointer-events-none block h-4 w-4 rounded-full bg-background shadow-lg ring-0 transition-transform',
                            form.is_in_portfolio ? 'translate-x-4' : 'translate-x-0',
                        ]"
                    />
                </button>
                <span class="text-sm">In portfolio</span>
            </label>

            <template v-if="form.is_in_portfolio">
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground">Quantity</label>
                    <Input
                        v-model="form.quantity"
                        type="number"
                        min="0"
                        step="any"
                        placeholder="0"
                        class="h-8 text-sm"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground">Avg price</label>
                    <Input
                        v-model="form.average_price"
                        type="number"
                        min="0"
                        step="any"
                        placeholder="0.00"
                        class="h-8 text-sm"
                    />
                </div>
            </template>

            <div class="flex flex-col gap-1">
                <label class="text-xs text-muted-foreground">Notes</label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Add notes…"
                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none"
                />
            </div>

            <Button size="sm" variant="outline" class="h-8 w-fit" @click="save">Save</Button>
        </div>
    </div>
</template>
