<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
  class?: string;
}>();
</script>

<template>
  <caption
    :class="
      cn(
        'mt-4 text-sm text-muted-foreground',
        class
      )
    "
  >
    <slot />
  </caption>
</template> 