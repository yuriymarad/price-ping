export type Appearance =
    | 'light'
    | 'dark'
    | 'system'
    | 'midnight-neon'
    | 'obsidian-chrome';
export type ResolvedAppearance = 'light' | 'dark';

export type AppVariant = 'header' | 'sidebar';

export type MarketStatus = 'open' | 'pre' | 'post' | 'closed';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};
