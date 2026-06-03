import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TickerController from '@/actions/App/Http/Controllers/TickerController';
import type { Ticker, TickerStatus } from '@/types';

export function useKanban(getTickers: () => Ticker[]) {
    const draggingTickerId = ref<number | null>(null);
    const dragOverColumn = ref<TickerStatus | null>(null);

    function onDragStart(ticker: Ticker) {
        draggingTickerId.value = ticker.id;
    }

    function onDragEnd() {
        draggingTickerId.value = null;
        dragOverColumn.value = null;
    }

    function onDragOver(event: DragEvent, column: TickerStatus) {
        event.preventDefault();
        dragOverColumn.value = column;
    }

    function onDragLeave() {
        dragOverColumn.value = null;
    }

    function onDrop(targetStatus: TickerStatus) {
        const id = draggingTickerId.value;
        draggingTickerId.value = null;
        dragOverColumn.value = null;
        if (id === null) {
            return;
        }
        const ticker = getTickers().find((t) => t.id === id);
        if (!ticker || ticker.status === targetStatus) {
            return;
        }
        router.patch(
            TickerController.updateStatus(ticker).url,
            { status: targetStatus },
            { preserveScroll: true, preserveState: true },
        );
    }

    return { draggingTickerId, dragOverColumn, onDragStart, onDragEnd, onDragOver, onDragLeave, onDrop };
}
