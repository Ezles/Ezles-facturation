<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronLeft } from 'lucide-vue-next';
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
      aria-label="Go to previous page"
      :class="cn('gap-1', class)"
    >
      <ChevronLeft class="h-4 w-4" />
      <span>Previous</span>
    </PaginationLink>
  </PaginationItem>
</template> 