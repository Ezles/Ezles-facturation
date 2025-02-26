<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, UserPlus } from 'lucide-vue-next';
import { onMounted } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: true, // Toujours activé par défaut
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// Vérifier si l'utilisateur est déjà connecté
onMounted(() => {
    // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
    if (route().current('dashboard')) {
        window.location.href = route('dashboard');
    }
});
</script>

<template>
    <AuthBase 
        title="Connexion" 
        description="Entrez vos identifiants pour accéder à votre espace de facturation"
    >
        <Head title="Connexion" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Adresse email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@exemple.com"
                        class="border-blue-500/20 focus:border-blue-500/50"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Mot de passe</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm text-blue-400 hover:text-blue-500" :tabindex="3"> Mot de passe oublié ? </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Mot de passe"
                        class="border-blue-500/20 focus:border-blue-500/50"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <Button 
                    type="submit" 
                    class="mt-4 w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 transition-all transform hover:scale-105 shadow-lg hover:shadow-blue-500/25" 
                    :tabindex="3" 
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span v-else>Connexion</span>
                </Button>
            </div>

            <!-- Lien vers la page d'inscription -->
            <div class="text-center text-sm text-gray-400">
                Vous n'avez pas de compte ?
                <TextLink :href="route('register')" class="text-blue-400 hover:text-blue-500 ml-1" :tabindex="4">
                    Créer un compte
                </TextLink>
            </div>

            <!-- Message de sécurité -->
            <div class="text-center text-xs text-gray-400 mt-2 flex items-center justify-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Connexion sécurisée avec cryptage avancé</span>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
/* Ajout de styles personnalisés pour correspondre à votre portfolio */
:deep(.bg-background) {
    background: linear-gradient(to bottom, #111827, #1f2937);
}

:deep(.text-muted-foreground) {
    color: rgba(209, 213, 219, 0.8);
}

:deep(.text-foreground) {
    color: white;
}
</style>
