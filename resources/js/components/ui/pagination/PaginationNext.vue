<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronRight } from 'lucide-vue-next';
import PaginationItem from './PaginationItem.vue';
import PaginationLink from './PaginationLink.vue';

defineProps<{
  class?: string;
  href?: string;
  disabled?: boolean;
}>();
</script>

<template>
  <PaginationItem>
    <PaginationLink
      :href="href"
      :disabled="disabled"
      aria-label="Go to next page"
      :class="cn('gap-1', class)"
    >
      <span>Next</span>
      <ChevronRight class="h-4 w-4" />
    </PaginationLink>
  </PaginationItem>
</template> 