<script setup lang="ts">
import { cn } from '@/lib/utils';
import { inject, onMounted, onUnmounted, ref, watch, computed } from 'vue';

const props = defineProps<{
  class?: string;
  position?: 'popper' | 'item-aligned';
}>();

const select = inject('select') as {
  open: { value: boolean };
  close: () => void;
};

const container = ref<HTMLDivElement | null>(null);

const contentClass = computed(() => {
  return cn(
    'relative z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md animate-in fade-in-80',
    props.position === 'popper' && 'translate-y-1',
    props.class
  );
});

// Close on click outside
const handleClickOutside = (event: MouseEvent) => {
  if (container.value && !container.value.contains(event.target as Node)) {
    select.close();
  }
};

// Close on escape key
const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    select.close();
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
  document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <div v-if="select.open.value" ref="container">
    <div :class="contentClass">
      <div class="p-1">
        <slot />
      </div>
    </div>
  </div>
</template>
