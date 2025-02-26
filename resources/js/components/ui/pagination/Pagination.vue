<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
}>();

const paginationClass = computed(() => {
  return cn('mx-auto flex w-full justify-center', props.class);
});
</script>

<template>
  <nav
    role="navigation"
    aria-label="pagination"
    :class="paginationClass"
  >
    <slot />
  </nav>
</template> 