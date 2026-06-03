export type RuleLike = {
    rule_type: string;
    threshold_direction?: string | null;
    threshold_price?: string | null;
    percent_direction?: string | null;
    percent_value?: string | null;
    period_hours?: number | null;
};

export function ruleLabel(rule: RuleLike): string {
    if (rule.rule_type === 'threshold') {
        const dir = rule.threshold_direction === 'above' ? 'Above' : 'Below';
        return `${dir} $${Number(rule.threshold_price).toFixed(2)}`;
    }
    const dir = rule.percent_direction === 'up' ? 'Rise' : rule.percent_direction === 'down' ? 'Drop' : 'Move';
    return `${dir} ≥${Number(rule.percent_value).toFixed(2)}% in ${rule.period_hours}h`;
}
