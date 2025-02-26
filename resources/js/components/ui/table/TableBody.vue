<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
  class?: string;
}>();
</script>

<template>
  <tbody
    :class="
      cn(
        '[&_tr:last-child]:border-0',
        class
      )
    "
  >
    <slot />
  </tbody>
</template> 