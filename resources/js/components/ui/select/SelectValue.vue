<script setup lang="ts">
import { inject } from 'vue';

const select = inject('select') as {
  modelValue: any;
};
</script>

<template>
  <span class="block truncate">
    <slot>{{ select.modelValue }}</slot>
  </span>
</template>
