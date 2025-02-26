<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const headerClass = computed(() => {
  return cn('[&_tr]:border-b', props.class);
});
</script>

<template>
  <thead :class="headerClass">
    <slot />
  </thead>
</template> 