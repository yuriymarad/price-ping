import { usePage } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import { Lock, Moon, Sun, Sunrise } from 'lucide-vue-next';
import { computed, ref, type Component, type ComputedRef } from 'vue';
import type { MarketStatus } from '@/types';

export type MarketTheme = {
    label: string;
    icon: Component;
    glowClass: string;
    pillClass: string;
};

const pillClass = 'bg-white/10 text-white ring-1 ring-inset ring-white/15';

const glow = (color: string): string =>
    `bg-radial-[at_50%_5%] ${color} via-15% to-transparent to-95%`;

const themes: Record<MarketStatus, MarketTheme> = {
    open: {
        label: 'Market Open',
        icon: Sun,
        glowClass: glow('from-emerald-900/20 via-emerald-600/10'),
        pillClass,
    },
    pre: {
        label: 'Extended Morning',
        icon: Sunrise,
        glowClass: glow('from-yellow-900/20 via-yellow-00/10'),
        pillClass,
    },
    post: {
        label: 'Extended Evening',
        icon: Moon,
        glowClass: glow('from-sky-900/20 via-sky-600/10'),
        pillClass,
    },
    closed: {
        label: 'Market Closed',
        icon: Lock,
        glowClass: glow('from-red-900/20 via-red-600/10'),
        pillClass,
    },
};

const liveStatus = ref<MarketStatus | null>(null);
let seeded = false;

export function useMarketStatus(): ComputedRef<MarketTheme | null> {
    const page = usePage();
    
    if (!seeded) {
        seeded = true;
        liveStatus.value = (page.props.marketStatus as MarketStatus | undefined) ?? null;
    }

    if (!import.meta.env.SSR) {
        useEchoPublic<{ status: MarketStatus }>(
            'markets',
            '.market.status.changed',
            (payload) => {
                liveStatus.value = payload.status;
            },
        );
    }

    return computed(() => (liveStatus.value ? themes[liveStatus.value] : null));
}
