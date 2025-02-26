<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const itemClass = computed(() => {
  return cn('', props.class);
});
</script>

<template>
  <li :class="itemClass">
    <slot />
  </li>
</template> 