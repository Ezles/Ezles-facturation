<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
  class?: string;
}>();
</script>

<template>
  <div
    :class="cn('flex flex-row items-center gap-1', class)"
  >
    <slot />
  </div>
</template> 