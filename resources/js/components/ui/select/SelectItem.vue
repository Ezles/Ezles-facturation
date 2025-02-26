<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Check } from 'lucide-vue-next';
import { computed, inject } from 'vue';

const props = defineProps<{
  class?: string;
  value: any;
  disabled?: boolean;
}>();

const select = inject('select') as {
  modelValue: any;
  select: (value: any) => void;
};

const isSelected = computed(() => {
  return select.modelValue === props.value;
});

const handleSelect = () => {
  if (!props.disabled) {
    select.select(props.value);
  }
};
</script>

<template>
  <div
    :class="cn(
      'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      isSelected && 'bg-accent text-accent-foreground',
      disabled && 'pointer-events-none opacity-50',
      class
    )"
    :data-disabled="disabled ? '' : undefined"
    @click="handleSelect"
  >
    <span v-if="isSelected" class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
      <Check class="h-4 w-4" />
    </span>
    <slot>{{ value }}</slot>
  </div>
</template>
