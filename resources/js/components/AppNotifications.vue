<template>
    <Toaster position="top-center" :closeable="true" />
</template>

<script setup lang="ts">
    import { watch } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import Toaster from "@/components/ui/sonner/Sonner.vue";
    import { toast } from "vue-sonner";

    interface FlashProps {
        flash?: {
            success?: string;
            error?: string;
            warning?: string;
            [key: string]: any;
        };
        errors?: Record<string, string | string[]>;
    }

    const page = usePage() as { props: FlashProps };

    watch(
        () => page.props.flash,
        (flash) => {

            if (!flash) return;

            if (flash.success) toast.success(flash.success, {
                class: "text-green-600",
            });
            if (flash.error) toast.error(flash.error, {
                class: "text-red-600",
            });
            if (flash.warning) toast(flash.warning, {
                class: "text-orange-400",
            });
        },
        { immediate: true }
    );

    watch(
        () => page.props.errors,
        (errors) => {
            if (!errors || Object.keys(errors).length === 0) return;

            toast.error("Please fix the highlighted errors.", {
                class: "text-red-600",
            });
        },
        { immediate: true }
    );
</script>