<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { RuleFormState } from '@/composables/useRuleDialog';

type Props = {
    form: RuleFormState;
    errors: Record<string, string | undefined>;
};

defineProps<Props>();
</script>

<template>
    <div class="grid gap-2">
        <Label>Direction</Label>
        <Select v-model="form.percent_direction">
            <SelectTrigger>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="down">Drop (price falls by %)</SelectItem>
                <SelectItem value="up">Rise (price climbs by %)</SelectItem>
                <SelectItem value="either">Either direction</SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.percent_direction" />
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div class="grid gap-2">
            <Label>Change (%)</Label>
            <Input v-model="form.percent_value" type="number" step="0.01" min="0.01" placeholder="5.00" required />
            <InputError :message="errors.percent_value" />
        </div>
        <div class="grid gap-2">
            <Label>Over (hours)</Label>
            <Input v-model="form.period_hours" type="number" min="1" placeholder="10" required />
            <InputError :message="errors.period_hours" />
        </div>
    </div>
</template>
