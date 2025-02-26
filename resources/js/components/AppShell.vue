<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { onMounted, ref, computed } from 'vue';
import Toast from '@/components/ui/toast.vue';

interface Props {
    variant?: 'header' | 'sidebar' | 'topbar';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'sidebar',
});

const isOpen = ref(true);

onMounted(() => {
    isOpen.value = localStorage.getItem('sidebar') !== 'false';
});

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    localStorage.setItem('sidebar', String(open));
};

const shellClass = computed(() => {
    return {
        'grid min-h-screen': true,
        'grid-cols-[auto_1fr]': props.variant === 'sidebar',
        'grid-rows-[auto_1fr]': props.variant === 'topbar',
    };
});
</script>

<template>
    <div>
        <div v-if="props.variant === 'header'" class="flex min-h-screen w-full flex-col">
            <slot />
        </div>
        <SidebarProvider v-else :default-open="isOpen" :open="isOpen" @update:open="handleSidebarChange">
            <slot />
        </SidebarProvider>
        
        <!-- Toast notifications -->
        <Toast />
    </div>
</template>
