<script setup lang="ts">
  import type { SelectRootEmits, SelectRootProps, AcceptableValue } from "reka-ui"
  import { SelectRoot, useForwardPropsEmits } from "reka-ui"

  const props = defineProps<
    SelectRootProps & {
      label?: string
      error?: string
    }>()
    const emits = defineEmits<SelectRootEmits & {
      change: (value: AcceptableValue) => void
    }>()
  const forwarded = useForwardPropsEmits(props, emits)

  function handleChange(value: AcceptableValue) {
    emits("change", value)
  }
</script>

<template>
  <SelectRoot
    v-slot="slotProps"
    data-slot="select"
    v-bind="forwarded"
    @update:modelValue="handleChange"
  >
    <slot v-bind="slotProps" />
  </SelectRoot>
  <span
    v-if="props.error"
    class="mt-1 text-sm text-destructive"
  >
    {{ props.error }}
  </span>
</template>
