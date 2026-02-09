<template>
     <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        role="combobox"
        class="w-full justify-between"
      >
        {{ displayLabel }}
        <ChevronsUpDown class="ml-2 h-4 w-4 opacity-50" />
      </Button>
    </PopoverTrigger>

    <PopoverContent  class="w-[--radix-popover-trigger-width] p-0">
      <Command>
        <CommandInput
          v-model="search"
          :placeholder="placeholder"
          @keydown.enter.prevent="acceptTyped"
        />

        <CommandEmpty>
          Press Enter or click outside to use “{{ search }}”
        </CommandEmpty>

        <CommandGroup>
          <CommandItem
            v-for="item in options"
            :key="item.value"
            :value="item.value"
            @select="() => {
              value = item.value
              selectedFromList = true
              open = false
            }"
          >
            <Check
              class="mr-2 h-4 w-4"
              :class="value === item.value ? 'opacity-100' : 'opacity-0'"
            />
            {{ item.label }}
          </CommandItem>
        </CommandGroup>
      </Command>
    </PopoverContent>
  </Popover>
  <span
    v-if="error"
    class="mt-1 text-sm text-destructive"
  >
    {{ error }}
  </span>
</template>

<script setup lang="ts">
    import { ref, watch, computed } from 'vue'

    const props = defineProps<{
        modelValue: string
        subjects: string[]
        placeholder?: string
        error?: string
    }>()

    const emit = defineEmits(['update:modelValue'])

    import {
        Command,
        CommandEmpty,
        CommandGroup,
        CommandInput,
        CommandItem,
    } from '@/components/ui/command'

    import {
        Popover,
        PopoverContent,
        PopoverTrigger,
    } from '@/components/ui/popover'

    import { Button } from '@/components/ui/button'
    import { Check, ChevronsUpDown } from 'lucide-vue-next'

    const open = ref(false)
    const search = ref('')
    const value = ref(props.modelValue)
    const selectedFromList = ref(false)

    watch(() => props.modelValue, v => value.value = v)

    watch(value, v => emit('update:modelValue', v))

    const options = computed(() =>
    props.subjects
        .filter(s =>
        s.toLowerCase().includes(search.value.toLowerCase())
        )
        .map(s => ({ label: s, value: s }))
    )

    watch(open, (isOpen) => {
        if (!isOpen && !selectedFromList.value && search.value) {
            value.value = search.value
        }
        if (!isOpen) selectedFromList.value = false
    })

    function acceptTyped() {
        if (search.value) {
            value.value = search.value
            open.value = false
        }
    }

    const displayLabel = computed(() =>
        value.value || props.placeholder
    )
</script>