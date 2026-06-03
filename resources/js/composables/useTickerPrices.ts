import { useEchoPublic } from '@laravel/echo-vue';
import { ref, type Ref } from 'vue';

export type LivePrice = {
    price: number;
    fetchedAt: string;
};

type Payload = {
    updates: Array<{ id: number; price: number; fetched_at: string }>;
};

const livePrices = ref<Map<number, LivePrice>>(new Map());

export function useTickerPrices(): Ref<Map<number, LivePrice>> {
    if (!import.meta.env.SSR) {
        useEchoPublic<Payload>(
            'tickers',
            '.ticker.prices.refreshed',
            (payload) => {
                const next = new Map(livePrices.value);
                for (const update of payload.updates) {
                    next.set(update.id, {
                        price: update.price,
                        fetchedAt: update.fetched_at,
                    });
                }
                livePrices.value = next;
            },
        );
    }

    return livePrices;
}
