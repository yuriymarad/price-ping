<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { ruleLabel } from '@/lib/ruleLabel';
import type { AlertRuleProposal } from '@/types';

defineProps<{ proposal: AlertRuleProposal }>();
defineEmits<{ discard: [] }>();
</script>

<template>
    <div class="flex items-center gap-2 rounded-md border border-violet-500/20 bg-violet-500/5 p-2 text-sm">
        <div class="flex-1 min-w-0">
            <div class="font-medium truncate text-violet-100">{{ ruleLabel(proposal) }}</div>
            <div class="text-xs text-muted-foreground">
                cd: {{ proposal.cooldown_minutes }}min
                <span v-if="proposal.is_one_shot" class="font-medium text-amber-600 dark:text-amber-400"> · 1×</span>
            </div>
        </div>
        <Button
            size="sm"
            variant="ghost"
            class="h-7 px-2 shrink-0 text-destructive hover:text-destructive"
            @click="$emit('discard')"
        >
            <Trash2 class="h-3 w-3" />
        </Button>
    </div>
</template>
