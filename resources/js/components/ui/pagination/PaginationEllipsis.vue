<script setup lang="ts">
import { cn } from '@/lib/utils';
import { MoreHorizontal } from 'lucide-vue-next';

defineProps<{
  class?: string;
}>();
</script>

<template>
  <div
    :class="cn('flex h-9 w-9 items-center justify-center', class)"
  >
    <MoreHorizontal class="h-4 w-4" />
    <span class="sr-only">More pages</span>
  </div>
</template> 