export type CategoryType = {
    id: string;
    name: string;
    prompt_logic: string;
    x_value: string | null;
    is_active: boolean;
    should_alert: boolean;
    created_at: string;
    updated_at: string;
};

export type ImpactedTicker = {
    ticker: string;
    company: string;
    impact_confidence: number;
};

export type DeepAnalysisStatus = 'pending' | 'ready' | 'failed';

export type DeepAnalysis = {
    id: string;
    news_item_id: string;
    status: DeepAnalysisStatus;
    content: string | null;
    confidence: number | null;
    tickers: ImpactedTicker[] | null;
    error: string | null;
    created_at: string;
    updated_at: string;
};

export type NewsStatus = 'unprocessed' | 'triaged' | 'no_impact' | 'failed';

export type NewsItem = {
    id: string;
    title: string;
    link: string;
    author: string | null;
    image_url: string | null;
    pub_date: string;
    category_id: string | null;
    status: NewsStatus;
    category?: CategoryType | null;
    deep_analysis?: DeepAnalysis | null;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};
