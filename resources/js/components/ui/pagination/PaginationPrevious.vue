<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronLeft } from 'lucide-vue-next';
import PaginationItem from './PaginationItem.vue';
import PaginationLink from './PaginationLink.vue';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
  href?: string;
  disabled?: boolean;
}>();

const previousClass = computed(() => {
  return cn('gap-1', props.class);
});
</script>

<template>
  <PaginationItem>
    <PaginationLink
      :href="href"
      :disabled="disabled"
      aria-label="Go to previous page"
      :class="previousClass"
    >
      <ChevronLeft class="h-4 w-4" />
      <span>Previous</span>
    </PaginationLink>
  </PaginationItem>
</template> 