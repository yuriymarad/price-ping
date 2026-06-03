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
import type { AlertRule, Ticker } from '@/types';

type Props = {
    open: boolean;
    ticker: Ticker | null;
    editingRule: AlertRule | null;
    form: RuleFormState;
    buildPayload: (form: RuleFormState) => Record<string, string | number | boolean | null | undefined>;
};

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{ editingRule ? 'Edit Rule' : 'Add Rule' }}
                    <span v-if="ticker" class="text-muted-foreground font-normal"> — {{ ticker.symbol }}</span>
                </DialogTitle>
            </DialogHeader>

            <!-- Edit Rule Form -->
            <Form
                v-if="editingRule"
                :action="AlertRuleController.update(editingRule).url"
                method="put"
                v-slot="{ errors, processing }"
                :transform="(_data) => buildPayload(form)"
                class="space-y-4"
                @success="emit('update:open', false)"
            >
                <RuleFormFields :form="form" :errors="errors" unique-id="edit" />
                <DialogFooter>
                    <Button type="button" variant="ghost" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="processing">Save</Button>
                </DialogFooter>
            </Form>

            <!-- Create Rule Form -->
            <Form
                v-else-if="ticker"
                :action="AlertRuleController.store(ticker).url"
                method="post"
                v-slot="{ errors, processing }"
                :transform="(_data) => buildPayload(form)"
                class="space-y-4"
                @success="emit('update:open', false)"
            >
                <RuleFormFields :form="form" :errors="errors" unique-id="create" />
                <DialogFooter>
                    <Button type="button" variant="ghost" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="processing">Create</Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
