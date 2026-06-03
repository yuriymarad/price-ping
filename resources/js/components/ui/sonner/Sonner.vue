<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { computed } from 'vue';
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const { appearance } = useAppearance();
const sonnerTheme = computed(() => (['midnight-neon', 'obsidian-chrome'].includes(appearance.value)) ? 'dark' : appearance.value);
</script>

<template>
    <SonnerPrimitive
        :theme="sonnerTheme"
        class="toaster group"
        position="bottom-right"
        :style="{
            '--normal-bg': 'var(--popover)',
            '--normal-text': 'var(--popover-foreground)',
            '--normal-border': 'var(--border)',
        }"
    />
</template>
