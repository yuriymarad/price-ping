import { reactive, ref } from 'vue';
import type { AlertRule, AlertRuleType, PercentDirection, ThresholdDirection, Ticker } from '@/types';

export type RuleFormState = {
    rule_type: AlertRuleType;
    threshold_price: string | number;
    threshold_direction: ThresholdDirection;
    percent_value: string | number;
    period_hours: string | number;
    percent_direction: PercentDirection;
    cooldown_minutes: number;
    is_one_shot: boolean;
};

const blankRule: RuleFormState = {
    rule_type: 'threshold',
    threshold_price: '',
    threshold_direction: 'below',
    percent_value: '',
    period_hours: '',
    percent_direction: 'down',
    cooldown_minutes: 60,
    is_one_shot: false,
};

export function useRuleDialog() {
    const ruleDialogOpen = ref(false);
    const ruleDialogTicker = ref<Ticker | null>(null);
    const editingRule = ref<AlertRule | null>(null);
    const ruleForm = reactive<RuleFormState>({ ...blankRule });

    const bulkRuleDialogOpen = ref(false);
    const bulkRuleForm = reactive<RuleFormState>({ ...blankRule });

    function openAddRule(ticker: Ticker) {
        ruleDialogTicker.value = ticker;
        editingRule.value = null;
        Object.assign(ruleForm, blankRule);
        ruleDialogOpen.value = true;
    }

    function openEditRule(ticker: Ticker, rule: AlertRule) {
        ruleDialogTicker.value = ticker;
        editingRule.value = rule;
        Object.assign(ruleForm, {
            rule_type: rule.rule_type,
            threshold_price: rule.threshold_price !== null ? Number(rule.threshold_price) : '',
            threshold_direction: rule.threshold_direction ?? 'below',
            percent_value: rule.percent_value !== null ? Number(rule.percent_value) : '',
            period_hours: rule.period_hours !== null ? rule.period_hours : '',
            percent_direction: rule.percent_direction ?? 'down',
            cooldown_minutes: rule.cooldown_minutes,
            is_one_shot: rule.is_one_shot,
        });
        ruleDialogOpen.value = true;
    }

    function openBulkRule() {
        Object.assign(bulkRuleForm, blankRule);
        bulkRuleDialogOpen.value = true;
    }

    function buildRulePayload(form: RuleFormState): Record<string, string | number | boolean | null | undefined> {
        const data: Record<string, string | number | boolean | null | undefined> = {
            rule_type: form.rule_type,
            cooldown_minutes: Number(form.cooldown_minutes),
            is_one_shot: form.is_one_shot,
        };

        if (form.rule_type === 'threshold') {
            data.threshold_price = form.threshold_price !== '' ? Number(form.threshold_price) : null;
            data.threshold_direction = form.threshold_direction;
            data.percent_value = null;
            data.period_hours = null;
            data.percent_direction = null;
        } else {
            data.percent_value = form.percent_value !== '' ? Number(form.percent_value) : null;
            data.period_hours = form.period_hours !== '' ? Number(form.period_hours) : null;
            data.percent_direction = form.percent_direction;
            data.threshold_price = null;
            data.threshold_direction = null;
        }

        return data;
    }

    return {
        ruleDialogOpen,
        ruleDialogTicker,
        editingRule,
        ruleForm,
        bulkRuleDialogOpen,
        bulkRuleForm,
        openAddRule,
        openEditRule,
        openBulkRule,
        buildRulePayload,
    };
}
