<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AlertRuleController from '@/actions/App/Http/Controllers/AlertRuleController';
import RuleFormFields from '@/components/RuleFormFields.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { RuleFormState } from '@/composables/useRuleDialog';

type Props = {
    open: boolean;
    tickerCount: number;
    form: RuleFormState;
    buildPayload: (form: RuleFormState) => Record<string, string | number | boolean | null | undefined>;
};

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-sm">
            <DialogHeader>
                <DialogTitle>Apply Rule to All Tickers</DialogTitle>
            </DialogHeader>
            <Form
                :action="AlertRuleController.storeForAll().url"
                method="post"
                v-slot="{ errors, processing }"
                :transform="(_data) => buildPayload(form)"
                class="space-y-4"
                @success="emit('update:open', false)"
            >
                <RuleFormFields :form="form" :errors="errors" unique-id="bulk" />
                <DialogFooter>
                    <Button type="button" variant="ghost" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="processing">Apply to All {{ tickerCount }} Tickers</Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
