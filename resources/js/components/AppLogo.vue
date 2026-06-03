<template>
    <span class="relative flex size-3">
        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-purple-500 opacity-75" />
        <span class="relative inline-flex size-3 rounded-full logo-dot" />
    </span>
    <span class="text-[14px] font-black uppercase tracking-wide">Price Ping</span>
</template>

<style scoped>
.logo-dot {
    animation: dot-color 4s ease-in-out infinite;
}

@keyframes dot-color {
    0%   { background-color: #a855f7; }
    25%  { background-color: #6366f1; }
    50%  { background-color: #f59e0b; }
    75%  { background-color: #22c55e; }
    100% { background-color: #a855f7; }
}
</style>
