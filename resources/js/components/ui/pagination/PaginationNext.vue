<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronRight } from 'lucide-vue-next';
import PaginationItem from './PaginationItem.vue';
import PaginationLink from './PaginationLink.vue';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
  href?: string;
  disabled?: boolean;
}>();

const nextClass = computed(() => {
  return cn('gap-1', props.class);
});
</script>

<template>
  <PaginationItem>
    <PaginationLink
      :href="href"
      :disabled="disabled"
      aria-label="Go to next page"
      :class="nextClass"
    >
      <span>Next</span>
      <ChevronRight class="h-4 w-4" />
    </PaginationLink>
  </PaginationItem>
</template> 