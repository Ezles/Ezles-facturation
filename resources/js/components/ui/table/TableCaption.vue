<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const captionClass = computed(() => {
  return cn('mt-4 text-sm text-muted-foreground', props.class);
});
</script>

<template>
  <caption :class="captionClass">
    <slot />
  </caption>
</template> 