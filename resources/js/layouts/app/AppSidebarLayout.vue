<script setup lang="ts">
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { useMarketStatus } from '@/composables/useMarketStatus';

const theme = useMarketStatus();
</script>

<template>
    <div class="relative flex min-h-screen w-full flex-col">
        <div
            v-if="theme"
            :class="theme.glowClass"
            class="pointer-events-none absolute inset-x-0 top-0 h-80"
            aria-hidden="true"
        />
        <AppSidebarHeader />
        <div
            class="relative z-10 mx-auto flex w-full max-w-[120rem] flex-1 flex-col overflow-x-hidden"
        >
            <slot />
        </div>
        <Toaster />
    </div>
</template>
