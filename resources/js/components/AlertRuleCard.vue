<script setup lang="ts">
import { BellOff, BellRing, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ruleLabel } from '@/lib/ruleLabel';
import { formatDate } from '@/lib/utils';
import type { AlertRule } from '@/types';

type Props = {
    rule: AlertRule;
};

defineProps<Props>();
defineEmits<{
    toggle: [];
    edit: [];
    delete: [];
}>();

</script>

<template>
    <div class="flex items-center gap-2 rounded-md border p-2 text-sm">
        <button
            :title="rule.is_active ? 'Disable rule' : 'Enable rule'"
            class="shrink-0 text-muted-foreground hover:text-foreground transition-colors"
            @click="$emit('toggle')"
        >
            <BellRing v-if="rule.is_active" class="h-4 w-4 text-primary cursor-pointer" />
            <BellOff v-else class="h-4 w-4 cursor-pointer" />
        </button>

        <div class="flex-1 min-w-0">
            <div :class="['font-medium truncate', !rule.is_active && 'text-muted-foreground line-through']">
                {{ ruleLabel(rule) }}
            </div>
            <div class="text-xs text-muted-foreground">
                cd: {{ rule.cooldown_minutes }}min
                <span v-if="rule.is_one_shot" class="font-medium text-amber-600 dark:text-amber-400"> · 1×</span>
                <span v-if="rule.last_alerted_at"> · triggered {{ formatDate(rule.last_alerted_at) }}</span>
            </div>
        </div>

        <Badge v-if="rule.is_active" variant="default" class="cursor-pointer shrink-0 text-xs" @click="$emit('toggle')">On</Badge>
        <Badge v-else variant="secondary" class="cursor-pointer shrink-0 text-xs" @click="$emit('toggle')">Off</Badge>

        <div class="flex shrink-0 gap-1">
            <Button size="sm" variant="ghost" class="h-7 px-2" @click="$emit('edit')">Edit</Button>
            <Button size="sm" variant="ghost" class="h-7 px-2 text-destructive hover:text-destructive" @click="$emit('delete')">
                <Trash2 class="h-3 w-3" />
            </Button>
        </div>
    </div>
</template>
