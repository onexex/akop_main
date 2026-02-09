<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
  defaultValue?: string | number
  modelValue?: string | number
  label?: string
  placeholder?: string
  error?: string
}>()

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})
</script>

<template>
  <div>
    <label
      class="mb-2 block text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
      for="label"
    >
      {{ label }}
    </label>
    <textarea
      v-model="modelValue"
      data-slot="textarea"
      :placeholder="placeholder"
      :class="cn('border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 flex field-sizing-content min-h-16 w-full rounded-md border bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm', props.class)"
    />
    
    <span
      v-if="error"
      class="mt-1 text-sm text-destructive"
    >
      {{ error }}
    </span>
  </div>
</template>
