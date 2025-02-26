<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const { toasts, removeToast } = useToast();

const getIcon = (variant: string) => {
  switch (variant) {
    case 'success':
      return CheckCircle;
    case 'destructive':
      return AlertCircle;
    case 'info':
      return Info;
    case 'warning':
      return AlertTriangle;
    default:
      return Info;
  }
};

const getVariantClass = (variant: string) => {
  switch (variant) {
    case 'success':
      return 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
    case 'destructive':
      return 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
    case 'info':
      return 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800';
    case 'warning':
      return 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800';
    default:
      return 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700';
  }
};

const getIconClass = (variant: string) => {
  switch (variant) {
    case 'success':
      return 'text-green-500 dark:text-green-400';
    case 'destructive':
      return 'text-red-500 dark:text-red-400';
    case 'info':
      return 'text-blue-500 dark:text-blue-400';
    case 'warning':
      return 'text-yellow-500 dark:text-yellow-400';
    default:
      return 'text-gray-500 dark:text-gray-400';
  }
};
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-full max-w-sm">
    <transition-group name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'rounded-lg border shadow-lg p-4 flex items-start gap-3',
          getVariantClass(toast.variant || 'default')
        ]"
        class="transform transition-all duration-300 ease-in-out"
      >
        <component
          :is="getIcon(toast.variant || 'default')"
          :class="getIconClass(toast.variant || 'default')"
          class="h-5 w-5 mt-0.5 flex-shrink-0"
        />
        
        <div class="flex-1">
          <h3 class="font-medium text-gray-900 dark:text-white">{{ toast.title }}</h3>
          <p v-if="toast.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ toast.description }}
          </p>
        </div>
        
        <button
          @click="removeToast(toast.id)"
          class="flex-shrink-0 rounded-md p-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-600"
        >
          <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style> 