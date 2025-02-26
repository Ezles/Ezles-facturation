<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const cellClass = computed(() => {
  return cn('p-4 align-middle [&:has([role=checkbox])]:pr-0', props.class);
});
</script>

<template>
  <td :class="cellClass">
    <slot />
  </td>
</template> 