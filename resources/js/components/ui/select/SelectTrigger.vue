<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronDown } from 'lucide-vue-next';
import { inject } from 'vue';

defineProps<{
  class?: string;
  placeholder?: string;
  disabled?: boolean;
}>();

const select = inject('select') as {
  modelValue: any;
  open: { value: boolean };
  toggle: () => void;
};
</script>

<template>
  <button
    type="button"
    :disabled="disabled"
    @click="select.toggle"
    :class="cn(
      'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      select.open.value && 'ring-2 ring-ring ring-offset-2',
      class
    )"
  >
    <slot>
      <span v-if="select.modelValue">{{ select.modelValue }}</span>
      <span v-else-if="placeholder" class="text-muted-foreground">{{ placeholder }}</span>
    </slot>
    <ChevronDown class="h-4 w-4 opacity-50" />
  </button>
</template>
