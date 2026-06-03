<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Bell,
    Briefcase,
    Eye,
    Flame,
    Plus,
    Sparkles,
    TrendingDown,
    TrendingUp,
    Upload,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AlertRuleController from '@/actions/App/Http/Controllers/AlertRuleController';
import AlertRuleProposalController from '@/actions/App/Http/Controllers/AlertRuleProposalController';
import TickerController from '@/actions/App/Http/Controllers/TickerController';
import AddEditRuleDialog from '@/components/AddEditRuleDialog.vue';
import AddTickerDialog from '@/components/AddTickerDialog.vue';
import AlertRuleCard from '@/components/AlertRuleCard.vue';
import BulkRuleDialog from '@/components/BulkRuleDialog.vue';
import ImportPortfolioDialog from '@/components/ImportPortfolioDialog.vue';
import PortfolioDataBlock from '@/components/PortfolioDataBlock.vue';
import ProposedRuleCard from '@/components/ProposedRuleCard.vue';
import ThresholdProximityBlock from '@/components/ThresholdProximityBlock.vue';
import TickerChart from '@/components/TickerChart.vue';
import TickerListItem from '@/components/TickerListItem.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useKanban } from '@/composables/useKanban';
import { useRuleDialog } from '@/composables/useRuleDialog';
import { useTickerPrices } from '@/composables/useTickerPrices';
import { useTickerSelection } from '@/composables/useTickerSelection';
import { dashboard } from '@/routes';
import type { AlertRule, AlertRuleProposal, Ticker } from '@/types';

type Props = {
    tickers: Ticker[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const livePrices = useTickerPrices();
const tickers = computed<Ticker[]>(() =>
    props.tickers.map((t) => {
        const live = livePrices.value.get(t.id);
        if (!live) {
            return t;
        }
        return {
            ...t,
            last_price: String(live.price),
            last_fetched_at: live.fetchedAt,
        };
    }),
);

const {
    tickerSearch,
    setupCompleteTickers,
    inReviewTickers,
    selectedTicker,
    selectTicker,
    activeFilters,
    toggleFilter,
} = useTickerSelection(() => tickers.value);

const {
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
} = useRuleDialog();

const tickerDialogOpen = ref(false);
const importDialogOpen = ref(false);

// --- Ticker mutations ---
function deleteTicker(ticker: Ticker) {
    if (!window.confirm(`Remove ${ticker.symbol} and all its rules?`)) {
        return;
    }

    router.delete(TickerController.destroy(ticker).url);
}

function toggleHot(ticker: Ticker) {
    router.patch(
        TickerController.toggleHot(ticker).url,
        {},
        { preserveScroll: true },
    );
}

function togglePortfolio(ticker: Ticker) {
    router.patch(
        TickerController.togglePortfolio(ticker).url,
        {},
        { preserveScroll: true },
    );
}

// --- Rule mutations ---
function deleteRule(rule: AlertRule) {
    if (!window.confirm('Delete this alert rule?')) {
        return;
    }

    router.delete(AlertRuleController.destroy(rule).url);
}

function toggleRule(rule: AlertRule) {
    router.patch(
        AlertRuleController.toggle(rule).url,
        {},
        { preserveScroll: true },
    );
}

// --- Proposal mutations ---
function applyAllProposals(ticker: Ticker) {
    router.post(
        AlertRuleProposalController.applyAll(ticker).url,
        {},
        { preserveScroll: true },
    );
}

function discardProposal(proposal: AlertRuleProposal) {
    router.delete(AlertRuleProposalController.destroy(proposal).url, {
        preserveScroll: true,
    });
}

const {
    dragOverColumn,
    onDragStart,
    onDragEnd,
    onDragOver,
    onDragLeave,
    onDrop,
} = useKanban(() => tickers.value);
</script>

<template>
    <Head title="Dashboard" />

    <Teleport defer to="#header-actions">
        <Button variant="outline" size="sm" @click="importDialogOpen = true">
            <Upload class="mr-1 h-4 w-4" />
            Import Portfolio
        </Button>
        <Button
            v-if="tickers.length > 0"
            variant="outline"
            size="sm"
            @click="openBulkRule"
        >
            <Plus class="mr-1 h-4 w-4" />
            Add Rule to All
        </Button>
        <Button size="sm" @click="tickerDialogOpen = true">
            <Plus class="mr-1 h-4 w-4" />
            Add Ticker
        </Button>
    </Teleport>

    <div class="flex flex-col gap-4 p-4">
        <ThresholdProximityBlock
            :tickers="tickers"
            @select-ticker="selectTicker"
        />

        <div
            v-if="tickers.length === 0"
            class="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground"
        >
            No tickers tracked yet. Add one to get started.
        </div>

        <div v-else class="flex flex-col gap-3">
            <!-- Search + filter bar spanning both columns -->
            <div class="flex items-center gap-1.5">
                <Input
                    v-model="tickerSearch"
                    placeholder="Search tickers…"
                    class="h-8 max-w-xs flex-1 text-sm"
                />
                <div class="flex shrink-0 items-center gap-0.5">
                    <Button
                        size="sm"
                        variant="ghost"
                        :class="[
                            'h-7 px-2 text-xs font-medium',
                            activeFilters.includes('all')
                                ? 'bg-accent text-accent-foreground'
                                : 'text-muted-foreground',
                        ]"
                        @click="toggleFilter('all')"
                    >
                        All
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        :class="[
                            'h-7 w-7',
                            activeFilters.includes('watchlist')
                                ? 'bg-accent text-blue-500'
                                : 'text-muted-foreground',
                        ]"
                        title="Watchlist (not in portfolio)"
                        @click="toggleFilter('watchlist')"
                    >
                        <Eye class="size-3.5" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        :class="[
                            'h-7 w-7',
                            activeFilters.includes('portfolio')
                                ? 'bg-accent text-green-500'
                                : 'text-muted-foreground',
                        ]"
                        title="In portfolio"
                        @click="toggleFilter('portfolio')"
                    >
                        <Briefcase class="size-3.5" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        :class="[
                            'h-7 w-7',
                            activeFilters.includes('hot')
                                ? 'bg-accent text-orange-500'
                                : 'text-muted-foreground',
                        ]"
                        title="Hot"
                        @click="toggleFilter('hot')"
                    >
                        <Flame class="size-3.5" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        :class="[
                            'h-7 w-7',
                            activeFilters.includes('pnl_down')
                                ? 'bg-accent text-red-500'
                                : 'text-muted-foreground',
                        ]"
                        title="Negative PnL"
                        @click="toggleFilter('pnl_down')"
                    >
                        <TrendingDown class="size-3.5" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        :class="[
                            'h-7 w-7',
                            activeFilters.includes('pnl_up')
                                ? 'bg-accent text-green-500'
                                : 'text-muted-foreground',
                        ]"
                        title="Positive PnL"
                        @click="toggleFilter('pnl_up')"
                    >
                        <TrendingUp class="size-3.5" />
                    </Button>
                </div>
            </div>

            <!-- Kanban board + detail panel -->
            <div class="flex items-start gap-6">
                <!-- Kanban: two columns, capped at 50% of page -->
                <div class="flex w-[35%] shrink-0 gap-3">
                    <!-- Column: Setup Complete -->
                    <div
                        :class="[
                            'flex w-1/2 flex-col rounded-sm transition-colors',
                            dragOverColumn === 'setup_complete'
                                ? 'border-green-500/50 bg-green-500/5'
                                : 'bg-muted/40',
                        ]"
                        @dragover="onDragOver($event, 'setup_complete')"
                        @dragleave="onDragLeave"
                        @drop="onDrop('setup_complete')"
                    >
                        <div
                            class="flex items-center justify-between px-3 py-2 pt-4"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-block h-2 w-2 shrink-0 rounded-full bg-green-500"
                                ></span>
                                <span class="text-sm font-semibold"
                                    >Setup Complete</span
                                >
                            </div>
                            <span
                                class="rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground"
                            >
                                {{ setupCompleteTickers.length }}
                            </span>
                        </div>
                        <div
                            class="kanban-column-body flex max-h-[calc(100vh-14rem)] flex-col gap-1.5 overflow-y-auto p-2"
                        >
                            <TickerListItem
                                v-for="ticker in setupCompleteTickers"
                                :key="ticker.id"
                                :ticker="ticker"
                                :is-selected="ticker.id === selectedTicker?.id"
                                :draggable="true"
                                @select="selectTicker(ticker)"
                                @delete="deleteTicker(ticker)"
                                @toggle-hot="toggleHot(ticker)"
                                @toggle-portfolio="togglePortfolio(ticker)"
                                @dragstart="onDragStart(ticker)"
                                @dragend="onDragEnd"
                            />
                            <div
                                v-if="setupCompleteTickers.length === 0"
                                class="m-1 flex flex-1 items-center justify-center rounded-lg border-2 border-dashed border-muted py-8 text-xs text-muted-foreground/50"
                            >
                                Drag here
                            </div>
                        </div>
                    </div>

                    <!-- Column: In Review -->
                    <div
                        :class="[
                            'flex w-1/2 flex-col rounded-sm transition-colors',
                            dragOverColumn === 'in_review'
                                ? 'border-amber-500/50 bg-amber-500/5'
                                : 'bg-muted/50',
                        ]"
                        @dragover="onDragOver($event, 'in_review')"
                        @dragleave="onDragLeave"
                        @drop="onDrop('in_review')"
                    >
                        <div
                            class="flex items-center justify-between px-3 py-2 pt-4"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-block h-2 w-2 shrink-0 rounded-full bg-amber-400"
                                ></span>
                                <span class="text-sm font-semibold"
                                    >In Review</span
                                >
                            </div>
                            <span
                                class="rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground"
                            >
                                {{ inReviewTickers.length }}
                            </span>
                        </div>
                        <div
                            class="kanban-column-body flex max-h-[calc(100vh-14rem)] flex-col gap-1.5 overflow-y-auto p-2"
                        >
                            <TickerListItem
                                v-for="ticker in inReviewTickers"
                                :key="ticker.id"
                                :ticker="ticker"
                                :is-selected="ticker.id === selectedTicker?.id"
                                :draggable="true"
                                @select="selectTicker(ticker)"
                                @delete="deleteTicker(ticker)"
                                @toggle-hot="toggleHot(ticker)"
                                @toggle-portfolio="togglePortfolio(ticker)"
                                @dragstart="onDragStart(ticker)"
                                @dragend="onDragEnd"
                            />
                            <div
                                v-if="inReviewTickers.length === 0"
                                class="m-1 flex flex-1 items-center justify-center rounded-lg border-2 border-dashed border-muted py-8 text-xs text-muted-foreground/50"
                            >
                                Drag here
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Ticker Detail -->
                <div
                    v-if="selectedTicker"
                    class="flex min-w-0 flex-1 flex-col gap-4"
                >
                    <TickerChart :ticker="selectedTicker" />

                    <div
                        v-if="selectedTicker.proposals?.length > 0"
                        class="rounded-xl border border-violet-500/30 bg-violet-500/5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-violet-500/20 px-3 py-2.5"
                        >
                            <div class="flex items-center gap-2">
                                <Sparkles class="h-3.5 w-3.5 text-violet-400" />
                                <span
                                    class="text-sm font-medium text-violet-300"
                                    >AI Suggested Rules</span
                                >
                            </div>
                            <Button
                                size="sm"
                                variant="outline"
                                class="h-6 border-violet-500/40 px-2 text-xs text-violet-300 hover:bg-violet-500/10"
                                @click="applyAllProposals(selectedTicker)"
                            >
                                Apply All
                            </Button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 p-3">
                            <ProposedRuleCard
                                v-for="proposal in selectedTicker.proposals"
                                :key="proposal.id"
                                :proposal="proposal"
                                @discard="discardProposal(proposal)"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <PortfolioDataBlock :ticker="selectedTicker" />

                        <div class="col-span-2 rounded-sm bg-muted/60">
                            <div
                                class="flex items-center justify-between px-3 py-2.5"
                            >
                                <div class="flex items-center gap-2">
                                    <Bell
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="mt-2 mb-2 text-sm font-medium">
                                        {{ selectedTicker.symbol }} 
                                        <span class="text-gray-400">Alert Rules</span> 
                                    </span>
                                </div>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-6 px-2 text-xs"
                                    @click="openAddRule(selectedTicker)"
                                >
                                    <Plus class="mr-1 h-3 w-3" />
                                    Add Rule
                                </Button>
                            </div>

                            <div class="grid grid-cols-2 gap-2 p-3">
                                <div
                                    v-if="selectedTicker.rules.length === 0"
                                    class="col-span-2 py-4 text-center text-xs text-muted-foreground"
                                >
                                    No rules yet.
                                </div>
                                <AlertRuleCard
                                    v-for="rule in selectedTicker.rules"
                                    :key="rule.id"
                                    :rule="rule"
                                    @toggle="toggleRule(rule)"
                                    @edit="openEditRule(selectedTicker, rule)"
                                    @delete="deleteRule(rule)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <AddTickerDialog v-model:open="tickerDialogOpen" />

    <ImportPortfolioDialog v-model:open="importDialogOpen" />

    <AddEditRuleDialog
        v-model:open="ruleDialogOpen"
        :ticker="ruleDialogTicker"
        :editing-rule="editingRule"
        :form="ruleForm"
        :build-payload="buildRulePayload"
    />

    <BulkRuleDialog
        v-model:open="bulkRuleDialogOpen"
        :ticker-count="tickers.length"
        :form="bulkRuleForm"
        :build-payload="buildRulePayload"
    />
</template>

<style scoped>
.kanban-column-body {
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
}

.kanban-column-body::-webkit-scrollbar {
    width: 4px;
}

.kanban-column-body::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-column-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 9999px;
}

.kanban-column-body::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>
