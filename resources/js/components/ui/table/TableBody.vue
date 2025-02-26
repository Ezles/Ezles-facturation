<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const bodyClass = computed(() => {
  return cn('[&_tr:last-child]:border-0', props.class);
});
</script>

<template>
  <tbody :class="bodyClass">
    <slot />
  </tbody>
</template> 