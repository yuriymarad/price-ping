<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PercentChangeRuleFields from '@/components/PercentChangeRuleFields.vue';
import ThresholdRuleFields from '@/components/ThresholdRuleFields.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { RuleFormState } from '@/composables/useRuleDialog';

type Props = {
    form: RuleFormState;
    errors: Record<string, string | undefined>;
    uniqueId: string;
};

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-2">
            <Label>Rule Type</Label>
            <Select v-model="form.rule_type">
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="threshold">Price Threshold</SelectItem>
                    <SelectItem value="percent_change">Percent Change</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.rule_type" />
        </div>

        <ThresholdRuleFields v-if="form.rule_type === 'threshold'" :form="form" :errors="errors" />
        <PercentChangeRuleFields v-else-if="form.rule_type === 'percent_change'" :form="form" :errors="errors" />

        <div class="grid gap-2">
            <Label>Cooldown (minutes)</Label>
            <Input v-model="form.cooldown_minutes" type="number" min="1" placeholder="60" required />
            <p class="text-xs text-muted-foreground">Minimum wait before re-alerting after a trigger.</p>
            <InputError :message="errors.cooldown_minutes" />
        </div>

        <div class="flex items-center gap-2">
            <Checkbox :id="`is_one_shot_${uniqueId}`" v-model="form.is_one_shot" />
            <Label :for="`is_one_shot_${uniqueId}`">One-shot (disable after first trigger)</Label>
        </div>
    </div>
</template>
