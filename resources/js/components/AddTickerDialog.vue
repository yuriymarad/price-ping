<script setup lang="ts">
import { reactive, watch } from 'vue';
import { Form } from '@inertiajs/vue3';
import TickerController from '@/actions/App/Http/Controllers/TickerController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const form = reactive({ symbol: '' });

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.symbol = '';
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-sm">
            <DialogHeader>
                <DialogTitle>Add Ticker</DialogTitle>
            </DialogHeader>
            <Form
                :action="TickerController.store().url"
                method="post"
                v-slot="{ errors, processing }"
                :transform="() => ({ symbol: form.symbol.toUpperCase().trim() })"
                class="space-y-4"
                @success="emit('update:open', false)"
            >
                <div class="grid gap-2">
                    <Label for="symbol">Ticker Symbol</Label>
                    <Input
                        id="symbol"
                        v-model="form.symbol"
                        placeholder="AAPL"
                        autocomplete="off"
                        class="uppercase"
                        required
                    />
                    <InputError :message="errors.symbol" />
                </div>
<DialogFooter>
                    <Button type="button" variant="ghost" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="processing">Add</Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
