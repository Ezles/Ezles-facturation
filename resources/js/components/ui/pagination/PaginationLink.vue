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
const linkClass = computed(() => {
  return cn(
    'flex h-9 w-9 items-center justify-center rounded-md border text-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
    props.isActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground',
    props.class
  );
});
</script>

<template>
  <a
    v-if="href && !isDisabled"
    :href="href"
    :class="linkClass"
  >
    <slot />
  </a>
  <div
    v-else
    :class="linkClass"
    :aria-disabled="isDisabled"
    :tabindex="isDisabled ? -1 : undefined"
  >
    <slot />
  </div>
</template> 