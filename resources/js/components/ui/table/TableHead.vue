<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const headClass = computed(() => {
  return cn('h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0', props.class);
});
</script>

<template>
  <th :class="headClass">
    <slot />
  </th>
</template> 