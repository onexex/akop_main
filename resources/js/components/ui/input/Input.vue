<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel } from "@vueuse/core"
import { cn } from "@/lib/utils"

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes["class"]
  label?: string
  type?: string
  required?: boolean
  error?: string
  name?: string
  id?: string
  placeholder?: string
  autofocus?: boolean
  accept?: string
  multiple?: boolean
  readonly?: boolean
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
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      v-model="modelValue"
      data-slot="input"
      :class="cn(
        'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        props.class,
      )"
      :type="type"
      :name="name"
      :id="id"
      :placeholder="placeholder"
      :autofocus="autofocus"
      :accept="accept"
      :multiple="multiple"
      :readonly="readonly"
    >
    <span
      v-if="error"
      class="mt-1 text-sm text-destructive"
    >
      {{ error }}
    </span>
  </div>
</template>
