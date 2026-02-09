<!-- <script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template> -->


<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import iWatchLogo from '/resources/images/logo.png';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-muted/40 dark:bg-zinc-950 p-6 md:p-10 transition-colors duration-500">
        <div class="w-full max-w-sm space-y-6">
            
            <div class="flex flex-col items-center gap-4">
                <Link href="/" class="flex flex-col items-center gap-3 group">
                    
                    <div class="flex aspect-square size-16 items-center justify-center rounded-2xl bg-white dark:bg-zinc-100 p-2.5 shadow-lg border border-border dark:border-zinc-800 transition-all group-hover:scale-105 group-hover:rotate-2">
                        <img 
                            :src="iWatchLogo" 
                            alt="iWatch Logo" 
                            class="size-full object-contain" 
                        />
                    </div>

                    <div class="text-center">
                        <h1 class="text-2xl font-black tracking-tight text-emerald-600">
                            AK<span style="color: red;">OP</span>
                        </h1>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.3em]">
                            AnaKalusugan cOre Platform
                        </p>
                    </div>  
                </Link>
            </div>

            <div class="rounded-2xl border border-border dark:border-zinc-800 bg-card dark:bg-zinc-900/50 p-8 shadow-sm backdrop-blur-sm">
                <slot />
            </div>
            
            <p class="text-center text-xs text-muted-foreground/60">
                &copy; 2026 AKOP APP. All rights reserved.
            </p>
        </div>
    </div>
</template>