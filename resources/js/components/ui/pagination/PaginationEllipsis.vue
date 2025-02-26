<script setup lang="ts">
import { cn } from '@/lib/utils';
import { MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const ellipsisClass = computed(() => {
  return cn('flex h-9 w-9 items-center justify-center', props.class);
});
</script>

<template>
  <div :class="ellipsisClass">
    <MoreHorizontal class="h-4 w-4" />
    <span class="sr-only">More pages</span>
  </div>
</template> 