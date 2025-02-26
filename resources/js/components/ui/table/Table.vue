<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const tableClass = computed(() => {
  return cn('w-full caption-bottom text-sm', props.class);
});
</script>

<template>
  <div class="w-full overflow-auto">
    <table :class="tableClass">
      <slot />
    </table>
  </div>
</template> 