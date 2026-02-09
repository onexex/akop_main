<script setup lang="ts">
// Import the image so Vite can process it and provide the correct URL
import iWatchLogo from '/resources/images/logo.png'; 
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-md bg-white   shadow-sm border border-sidebar-border"
    >
        <img 
            :src="iWatchLogo" 
            alt="iWatch Logo" 
            class="size-full object-contain" 
        />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-bold   text-emerald-600"
            >AK<span style="color: red;">OP</span></span
        >
        <span class="truncate text-[10px] text-muted-foreground font-medium uppercase tracking-tight"
            >AnaKalusugan cOre Platform </span
        >
    </div>
</template>