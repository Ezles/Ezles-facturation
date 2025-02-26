<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const contentClass = computed(() => {
  return cn('flex flex-row items-center gap-1', props.class);
});
</script>

<template>
  <div :class="contentClass">
    <slot />
  </div>
</template> 