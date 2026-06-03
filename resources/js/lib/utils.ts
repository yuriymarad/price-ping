import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function pnlPercent(price: number | string | null | undefined, avg: number | string | null | undefined): number | null {
    const p = Number(price);
    const a = Number(avg);
    if (!p || !a) return null;
    return ((p - a) / a) * 100;
}

export function pnlUsd(
    price: number | string | null | undefined,
    avg: number | string | null | undefined,
    qty: number | string | null | undefined,
): number | null {
    const p = Number(price);
    const a = Number(avg);
    const q = Number(qty);
    if (!p || !a || !q) return null;
    return (p - a) * q;
}

export function formatDate(date: string | null): string {
    if (!date) {
        return 'Never';
    }
    return new Date(date).toLocaleString('en-GB');
}
