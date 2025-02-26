<script setup lang="ts">
import { provide, ref } from 'vue';

const modelValue = defineModel();
const open = ref(false);

provide('select', {
  modelValue,
  open,
  toggle: () => {
    open.value = !open.value;
  },
  close: () => {
    open.value = false;
  },
  select: (value: any) => {
    modelValue.value = value;
    open.value = false;
  }
});
</script>

<template>
  <div class="relative">
    <slot />
  </div>
</template> 