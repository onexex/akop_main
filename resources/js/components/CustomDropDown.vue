<script setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    label: String,
    modelValue: [String, Number, Object],
    options: Array, // Pwedeng ['A', 'B'] o [{label: 'A', value: 1}]
    placeholder: { type: String, default: 'Select Option' }
});

const emit = defineEmits(['update:modelValue', 'change']);

// Ginagawa nating standard object ang bawat item para pare-pareho ang logic
const normalizedOptions = computed(() => {
    return props.options.map(opt => {
        if (typeof opt === 'object' && opt !== null) {
            return { label: opt.label, value: opt.value };
        }
        return { label: opt, value: opt }; // Para sa Array of Strings
    });
});

const selectedLabel = computed(() => {
    const selected = normalizedOptions.value.find(opt => opt.value === props.modelValue);
    return selected ? selected.label : props.placeholder;
});

const selectItem = (val) => {
    emit('update:modelValue', val);
    emit('change', val);
};
</script>

<template>
    <div class="flex flex-col gap-1.5 w-full">
        <label v-if="label" class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            {{ label }}
        </label>

        <DropdownMenu>
            <DropdownMenuTrigger class="flex h-9 w-full items-center justify-between rounded-lg border text-black border-slate-200 bg-white px-3 py-2 text-xs font-bold shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-400">
                <span class="truncate">{{ selectedLabel }}</span>
                <ChevronDown class="h-4 w-4 opacity-50" />
            </DropdownMenuTrigger>

            <DropdownMenuContent class="z-[9999] w-full min-w-[var(--radix-dropdown-menu-trigger-width)] bg-white shadow-md rounded-md border border-slate-100 p-1">
                <DropdownMenuItem 
                    @click="selectItem('')"
                    class="relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-xs font-medium outline-none hover:bg-slate-100"
                >
                    {{ placeholder }}
                </DropdownMenuItem>

                <DropdownMenuItem 
                    v-for="option in normalizedOptions" 
                    :key="option.value" 
                    @click="selectItem(option.value)" 
                    class="relative flex cursor-pointer select-none items-center rounded-sm text-black px-2 py-1.5 text-xs font-medium outline-none hover:bg-slate-100"
                    :class="{ 'bg-slate-50 text-indigo-600': modelValue === option.value }"
                >
                    {{ option.label }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>