import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { pnlPercent } from '@/lib/utils';
import type { Ticker } from '@/types';

const STORAGE_KEY = 'ticker-filters';
const SELECTED_KEY = 'selected-ticker-id';

function loadFilters(): string[] {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        }
    } catch {
        // ignore
    }
    return ['all'];
}

function loadSelectedTickerId(): number | null {
    try {
        const stored = localStorage.getItem(SELECTED_KEY);
        if (stored !== null) {
            const parsed = Number(stored);
            if (Number.isInteger(parsed)) return parsed;
        }
    } catch {
        // ignore
    }
    return null;
}

export function useTickerSelection(getTickers: () => Ticker[]) {
    const tickerSearch = ref('');
    const selectedTickerId = ref<number | null>(getTickers()[0]?.id ?? null);
    const tickerListRef = ref<HTMLElement | null>(null);
    const activeFilters = ref<string[]>(['all']);

    onMounted(() => {
        activeFilters.value = loadFilters();
        const storedId = loadSelectedTickerId();
        if (storedId !== null && getTickers().some((t) => t.id === storedId)) {
            selectedTickerId.value = storedId;
        }
    });

    watch(
        activeFilters,
        (val) => localStorage.setItem(STORAGE_KEY, JSON.stringify(val)),
        { deep: true },
    );

    watch(selectedTickerId, (val) => {
        if (val !== null) {
            localStorage.setItem(SELECTED_KEY, String(val));
        }
    });

    const RESETS_ON_SELECT: Record<string, string[]> = {
        watchlist: ['portfolio', 'pnl_up', 'pnl_down'],
        portfolio: ['watchlist'],
        pnl_up: ['pnl_down'],
        pnl_down: ['pnl_up'],
    };

    function toggleFilter(key: string) {
        if (key === 'all') {
            activeFilters.value = ['all'];
            return;
        }
        let current = activeFilters.value.filter((f) => f !== 'all');
        const idx = current.indexOf(key);
        if (idx === -1) {
            const toRemove = RESETS_ON_SELECT[key] ?? [];
            current = current.filter((f) => !toRemove.includes(f));
            current.push(key);
        } else {
            current.splice(idx, 1);
        }
        activeFilters.value = current.length > 0 ? current : ['all'];
    }

    const filteredTickers = computed(() => {
        const q = tickerSearch.value.trim().toLowerCase();
        let tickers = getTickers();

        if (q) {
            tickers = tickers.filter(
                (t) => t.symbol.toLowerCase().includes(q) || (t.long_name ?? '').toLowerCase().includes(q),
            );
        }

        if (activeFilters.value.includes('all')) return tickers;

        const pnl = (t: Ticker) => pnlPercent(t.last_price, t.average_price);

        return tickers.filter((t) => {
            for (const f of activeFilters.value) {
                if (f === 'watchlist' && t.is_in_portfolio) return false;
                if (f === 'portfolio' && !t.is_in_portfolio) return false;
                if (f === 'hot' && !t.is_hot) return false;
                if (f === 'pnl_down') {
                    const p = pnl(t);
                    if (!t.is_in_portfolio || p === null || p >= 0) return false;
                }
                if (f === 'pnl_up') {
                    const p = pnl(t);
                    if (!t.is_in_portfolio || p === null || p < 0) return false;
                }
            }
            return true;
        });
    });

    const setupCompleteTickers = computed(() =>
        filteredTickers.value.filter(
            (t) => t.status === 'setup_complete' || (t.status === 'in_review' && t.proposals.length > 0),
        ),
    );

    const inReviewTickers = computed(() =>
        filteredTickers.value.filter((t) => t.status === 'in_review' && t.proposals.length === 0),
    );

    const selectedTicker = computed<Ticker | null>(() => {
        const tickers = getTickers();
        return tickers.find((t) => t.id === selectedTickerId.value) ?? tickers[0] ?? null;
    });

    function selectTicker(ticker: Ticker) {
        selectedTickerId.value = ticker.id;
        nextTick(() => {
            tickerListRef.value
                ?.querySelector<HTMLElement>(`[data-ticker-id="${ticker.id}"]`)
                ?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        });
    }

    return {
        tickerSearch,
        tickerListRef,
        filteredTickers,
        setupCompleteTickers,
        inReviewTickers,
        selectedTicker,
        selectTicker,
        activeFilters,
        toggleFilter,
    };
}
