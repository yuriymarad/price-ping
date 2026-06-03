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
        <Select v-model="form.threshold_direction">
            <SelectTrigger>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="below">Below (price drops under)</SelectItem>
                <SelectItem value="above">Above (price rises over)</SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.threshold_direction" />
    </div>
    <div class="grid gap-2">
        <Label>Price ($)</Label>
        <Input v-model="form.threshold_price" type="number" step="0.01" min="0" placeholder="100.00" required />
        <InputError :message="errors.threshold_price" />
    </div>
</template>
