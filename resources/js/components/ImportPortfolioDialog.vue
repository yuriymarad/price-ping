<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TickerController from '@/actions/App/Http/Controllers/TickerController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Props = {
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFileName = ref('');

const form = useForm<{ file: File | null }>({ file: null });

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.reset();
            form.clearErrors();
            selectedFileName.value = '';

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        }
    },
);

function chooseFile() {
    fileInput.value?.click();
}

function onFileSelected(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.file = file;
    selectedFileName.value = file?.name ?? '';
}

function submit() {
    form.post(TickerController.importPortfolio().url, {
        forceFormData: true,
        onSuccess: () => emit('update:open', false),
    });
}

const columns = [
    { name: 'Symbol', required: true, description: 'Ticker symbol' },
    { name: 'Description', required: false, description: 'Company name' },
    { name: 'Quantity', required: false, description: 'Shares held' },
    { name: 'OpenPrice', required: false, description: 'Average buy price' },
];
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>Import Portfolio</DialogTitle>
                <DialogDescription
                    >Upload a CSV exported from your
                    brokerage.</DialogDescription
                >
            </DialogHeader>

            <div class="space-y-3 text-sm">
                <div>
                    <p class="mb-1.5 font-medium">Expected columns</p>
                    <ul class="space-y-1 text-muted-foreground">
                        <li
                            v-for="column in columns"
                            :key="column.name"
                            class="flex items-center gap-2"
                        >
                            <code
                                class="rounded bg-muted px-1.5 py-0.5 text-xs text-foreground"
                                >{{ column.name }}</code
                            >
                            <span
                                :class="[
                                    'text-xs',
                                    column.required
                                        ? 'font-medium text-foreground'
                                        : 'text-muted-foreground',
                                ]"
                            >
                                {{ column.required ? 'required' : 'optional' }}
                            </span>
                            <span class="text-xs"
                                >— {{ column.description }}</span
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="mb-1.5 font-medium">Example</p>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-xs">
                            <thead class="bg-muted/50 text-muted-foreground">
                                <tr>
                                    <th
                                        class="px-2 py-1.5 text-left font-medium"
                                    >
                                        Symbol
                                    </th>
                                    <th
                                        class="px-2 py-1.5 text-left font-medium"
                                    >
                                        Description
                                    </th>
                                    <th
                                        class="px-2 py-1.5 text-left font-medium"
                                    >
                                        Quantity
                                    </th>
                                    <th
                                        class="px-2 py-1.5 text-left font-medium"
                                    >
                                        OpenPrice
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="font-mono">
                                <tr class="border-t">
                                    <td class="px-2 py-1.5">AAPL</td>
                                    <td class="px-2 py-1.5">Apple Inc.</td>
                                    <td class="px-2 py-1.5">100</td>
                                    <td class="px-2 py-1.5">150.25</td>
                                </tr>
                                <tr class="border-t">
                                    <td class="px-2 py-1.5">MSFT</td>
                                    <td class="px-2 py-1.5">Microsoft Corp.</td>
                                    <td class="px-2 py-1.5">50</td>
                                    <td class="px-2 py-1.5">300.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid gap-2">
                <input
                    ref="fileInput"
                    type="file"
                    accept=".csv"
                    class="hidden"
                    @change="onFileSelected"
                />
                <div class="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="chooseFile"
                        >Choose file</Button
                    >
                    <span
                        :class="[
                            'truncate text-sm',
                            selectedFileName
                                ? 'text-foreground'
                                : 'text-muted-foreground',
                        ]"
                    >
                        {{ selectedFileName || 'No file selected' }}
                    </span>
                </div>
                <InputError :message="form.errors.file" />
            </div>

            <DialogFooter>
                <Button
                    type="button"
                    variant="ghost"
                    @click="emit('update:open', false)"
                    >Cancel</Button
                >
                <Button
                    type="button"
                    :disabled="!form.file || form.processing"
                    @click="submit"
                    >Import</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
