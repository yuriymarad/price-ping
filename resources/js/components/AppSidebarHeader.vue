<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { useMarketStatus } from '@/composables/useMarketStatus';

const page = usePage();
const auth = computed(() => page.props.auth);
const theme = useMarketStatus();
</script>

<template>
    <header class="relative z-10">
        <span
            v-if="theme"
            :class="theme.pillClass"
            class="absolute top-1/2 left-1/2 inline-flex -translate-x-1/2 -translate-y-1/2 items-center gap-1.5 rounded-full px-3 py-2 text-xs font-medium"
        >
            <component :is="theme.icon" :key="theme.label" class="size-3.5" />
            {{ theme.label }}
        </span>
        <div
            class="mx-auto flex h-16 w-full max-w-[120rem] items-center gap-2 px-6"
        >
            <div class="flex items-center gap-2">
                <AppLogo />
            </div>
            <div class="ml-auto flex items-center gap-2">
                <div id="header-actions" class="flex items-center gap-2" />
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                        >
                            <Avatar class="size-8 overflow-hidden rounded-full">
                                <AvatarImage
                                    v-if="auth.user.avatar"
                                    :src="auth.user.avatar"
                                    :alt="auth.user.name"
                                />
                                <AvatarFallback
                                    class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                >
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
</template>
