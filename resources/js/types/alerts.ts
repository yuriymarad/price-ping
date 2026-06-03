export type AlertRuleType = 'threshold' | 'percent_change';
export type TickerStatus = 'in_review' | 'setup_complete';
export type ThresholdDirection = 'above' | 'below';
export type PercentDirection = 'up' | 'down' | 'either';

export type AlertRuleProposal = {
    id: number;
    ticker_id: number;
    rule_type: AlertRuleType;
    is_one_shot: boolean;
    threshold_price: string | null;
    threshold_direction: ThresholdDirection | null;
    percent_value: string | null;
    period_hours: number | null;
    percent_direction: PercentDirection | null;
    cooldown_minutes: number;
    created_at: string;
    updated_at: string;
};

export type AlertRule = {
    id: number;
    ticker_id: number;
    rule_type: AlertRuleType;
    is_active: boolean;
    is_one_shot: boolean;
    threshold_price: string | null;
    threshold_direction: ThresholdDirection | null;
    percent_value: string | null;
    period_hours: number | null;
    percent_direction: PercentDirection | null;
    baseline_price: string | null;
    baseline_recorded_at: string | null;
    cooldown_minutes: number;
    last_alerted_at: string | null;
    created_at: string;
    updated_at: string;
};

export type Ticker = {
    id: number;
    symbol: string;
    long_name: string | null;
    currency: string | null;
    exchange_name: string | null;
    last_price: string | null;
    last_fetched_at: string | null;
    is_hot: boolean;
    is_in_portfolio: boolean;
    quantity: string | null;
    average_price: string | null;
    notes: string | null;
    status: TickerStatus;
    rules: AlertRule[];
    proposals: AlertRuleProposal[];
    created_at: string;
    updated_at: string;
};
