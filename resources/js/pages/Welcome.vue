<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Ezles Facturation">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <main class="min-h-screen bg-gradient-to-b from-gray-900 to-gray-800 relative overflow-hidden">
        <!-- Effets de fond avec dégradés flous -->
        <div class="absolute top-[-10%] right-[-20%] w-[500px] h-[500px] bg-purple-500/30 rounded-full blur-[128px] pointer-events-none" />
        <div class="absolute bottom-[20%] left-[-10%] w-[400px] h-[400px] bg-blue-500/20 rounded-full blur-[128px] pointer-events-none" />
        <div class="absolute top-[40%] right-[-15%] w-[600px] h-[600px] bg-pink-500/20 rounded-full blur-[96px] pointer-events-none" />
        <!-- Contenu principal -->
        <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-20">
            <div class="text-center space-y-6 max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-6xl font-bold text-white leading-tight">
                    Simplifiez votre <span class="bg-gradient-to-r from-blue-400 to-purple-400 text-transparent bg-clip-text">facturation</span>
                </h1>
                
                <p class="text-xl text-gray-300 max-w-2xl mx-auto leading-relaxed">
                    Une solution moderne et intuitive pour gérer vos factures, clients et paiements en toute simplicité.
                </p>
                
                <div class="pt-8">
                    <Link
                        :href="route('login')"
                        class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-8 py-3 rounded-full text-lg font-semibold transition-all transform hover:scale-105 hover:from-blue-600 hover:to-purple-600 shadow-lg hover:shadow-blue-500/25"
                    >
                        Commencer maintenant
                    </Link>
                </div>
            </div>
            
            <!-- Carte avec effet glassmorphism -->
            <div class="mt-16 bg-gray-800/30 rounded-2xl p-8 backdrop-blur-sm border border-white/5 hover:border-white/10 transition-all duration-300 shadow-xl max-w-4xl w-full">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div class="p-4 bg-blue-500/20 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Factures simplifiées</h3>
                        <p class="text-gray-300">Créez et gérez vos factures en quelques clics</p>
                    </div>
                    
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div class="p-4 bg-purple-500/20 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Suivi des paiements</h3>
                        <p class="text-gray-300">Suivez vos paiements et relancez vos clients</p>
                    </div>
                    
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div class="p-4 bg-pink-500/20 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Statistiques</h3>
                        <p class="text-gray-300">Analysez vos revenus et votre activité</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer simple -->
        <footer class="py-8 text-center text-gray-400 text-sm">
            <p>© {{ new Date().getFullYear() }} Ezles Facturation. Tous droits réservés.</p>
        </footer>
    </main>
</template>
