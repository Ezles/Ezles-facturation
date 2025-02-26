<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-svh bg-gradient-to-b from-gray-900 to-gray-800 relative overflow-hidden">
        <!-- Effets de fond avec dégradés flous -->
        <div class="absolute top-[-10%] right-[-20%] w-[500px] h-[500px] bg-purple-500/30 rounded-full blur-[128px] pointer-events-none" />
        <div class="absolute bottom-[20%] left-[-10%] w-[400px] h-[400px] bg-blue-500/20 rounded-full blur-[128px] pointer-events-none" />
        <div class="absolute top-[40%] right-[-15%] w-[600px] h-[600px] bg-pink-500/20 rounded-full blur-[96px] pointer-events-none" />

        <!-- Contenu principal -->
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 relative z-10">
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-4">
                        <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium group">
                            <div class="mb-1 flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-r from-blue-500/20 to-purple-500/20 p-2 transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-blue-500/25">
                                <AppLogoIcon class="size-9 fill-current text-white" />
                            </div>
                            <span class="sr-only">{{ title }}</span>
                        </Link>
                        <div class="space-y-2 text-center">
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 text-transparent bg-clip-text">{{ title }}</h1>
                            <p class="text-center text-sm text-gray-300">{{ description }}</p>
                        </div>
                    </div>
                    
                    <!-- Carte avec effet glassmorphism -->
                    <div class="bg-gray-800/30 rounded-2xl p-6 backdrop-blur-sm border border-white/5 hover:border-white/10 transition-all duration-300 shadow-xl">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
