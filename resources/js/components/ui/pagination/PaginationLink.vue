<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
  class?: string;
  isActive?: boolean;
  disabled?: boolean;
  href?: string;
}>();

const isDisabled = computed(() => props.disabled);
</script>

<template>
  <a
    v-if="href && !isDisabled"
    :href="href"
    :class="cn(
      'flex h-9 w-9 items-center justify-center rounded-md border text-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
      isActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground',
      class
    )"
  >
    <slot />
  </a>
  <div
    v-else
    :class="cn(
      'flex h-9 w-9 items-center justify-center rounded-md border text-sm transition-colors',
      isActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground',
      isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground',
      class
    )"
  >
    <slot />
  </div>
</template> 