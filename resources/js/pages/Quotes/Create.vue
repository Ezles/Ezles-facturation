<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed, onMounted, nextTick } from 'vue';

// Définir l'interface pour le client
interface Client {
    id: number;
    nom: string;
    email: string | null;
    adresse: string;
    code_postal: string | null;
    ville: string | null;
    telephone: string | null;
    siret: string | null;
    numero_tva: string | null;
}

// Définir l'interface pour une ligne de devis
interface LigneDevis {
    description: string;
    quantite: number;
    prix_unitaire: number;
    taux_tva: number;
}

// Définir les props pour les données passées depuis le contrôleur
const props = defineProps<{
    devisNumber: string;
    defaultValidityDays: number;
    clients: Client[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Devis',
        href: '/quotes',
    },
    {
        title: 'Nouveau Devis',
        href: '/quotes/create',
    },
];

// Référence pour la recherche de client
const clientSearch = ref('');
const showClientDropdown = ref(false);
const showNewClientModal = ref(false);

// Formulaire pour le nouveau client
const newClientForm = useForm({
    nom: '',
    email: '',
    adresse: '',
    code_postal: '',
    ville: '',
    telephone: '',
    siret: '',
    numero_tva: ''
});

// Filtrer les clients en fonction de la recherche
const filteredClients = computed(() => {
    if (!clientSearch.value) return [];
    
    const search = clientSearch.value.toLowerCase();
    return props.clients.filter(client => 
        client.nom.toLowerCase().includes(search) || 
        (client.email && client.email.toLowerCase().includes(search))
    );
});

// Calculer la date de validité par défaut (date d'émission + 30 jours)
const defaultValidityDate = () => {
    const today = new Date();
    const validityDate = new Date(today);
    validityDate.setDate(today.getDate() + props.defaultValidityDays);
    return validityDate.toISOString().substr(0, 10);
};

// Formulaire pour la création de devis
const form = useForm<{
    numero: string;
    date_emission: string;
    date_validite: string;
    client_id: string | number;
    lignes: LigneDevis[];
    conditions_paiement: string;
    notes: string;
    mentions_legales: string;
}>({
    numero: props.devisNumber,
    date_emission: new Date().toISOString().substr(0, 10),
    date_validite: defaultValidityDate(),
    client_id: '',
    lignes: [
        {
            description: '',
            quantite: 1,
            prix_unitaire: 0,
            taux_tva: 20
        }
    ],
    conditions_paiement: 'Paiement à réception de facture',
    notes: 'Merci pour votre confiance !',
    mentions_legales: 'Ce devis est valable 30 jours à compter de sa date d\'émission.'
});

// Sélectionner un client depuis la liste déroulante
const selectClient = (client: Client) => {
    form.client_id = client.id;
    clientSearch.value = client.nom;
    showClientDropdown.value = false;
};

// Créer un nouveau client
const createNewClient = () => {
    // Initialiser le formulaire avec le nom recherché
    newClientForm.nom = clientSearch.value;
    
    // Envoyer la requête pour créer le client
    newClientForm.post(route('clients.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            // Récupérer le client créé depuis la réponse
            const newClient = response?.props?.client;
            
            if (newClient) {
                // Sélectionner le nouveau client
                selectClient(newClient);
                
                // Ajouter le client à la liste des clients
                props.clients.push(newClient);
                
                // Fermer le modal
                showNewClientModal.value = false;
                
                // Réinitialiser le formulaire
                newClientForm.reset();
            }
        }
    });
};

// Calculer le sous-total HT
const subtotal = computed(() => {
    let total = 0;
    for (const ligne of form.lignes) {
        total += Number(ligne.quantite) * Number(ligne.prix_unitaire);
    }
    return total;
});

// Calculer la TVA
const tax = computed(() => {
    let total = 0;
    for (const ligne of form.lignes) {
        const montantHT = Number(ligne.quantite) * Number(ligne.prix_unitaire);
        total += montantHT * (Number(ligne.taux_tva) / 100);
    }
    return total;
});

// Calculer le total TTC
const total = computed(() => {
    return subtotal.value + tax.value;
});

// Ajouter une ligne
const addLigne = () => {
    form.lignes.push({
        description: '',
        quantite: 1,
        prix_unitaire: 0,
        taux_tva: 20
    });
};

// Supprimer une ligne
const removeLigne = (index: number) => {
    if (form.lignes.length > 1) {
        form.lignes.splice(index, 1);
    }
};

// Formater un montant en euros
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
};

// Soumettre le formulaire
const submit = () => {
    form.post(route('quotes.store'));
};
</script>

<template>
    <Head title="Nouveau Devis" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Nouveau Devis</h1>
                <div class="flex space-x-2">
                    <Button variant="outline" @click="router.visit('/quotes')">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Retour à la liste
                    </Button>
                </div>
            </div>
            
            <form @submit.prevent="submit">
                <!-- Informations du devis -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Informations du devis</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <Label for="numero">Numéro de devis</Label>
                                <Input id="numero" v-model="form.numero" required readonly class="bg-gray-100" />
                                <div v-if="form.errors.numero" class="text-sm text-red-500 mt-1">{{ form.errors.numero }}</div>
                            </div>
                            <div>
                                <Label for="date_emission">Date d'émission</Label>
                                <Input id="date_emission" type="date" v-model="form.date_emission" required />
                                <div v-if="form.errors.date_emission" class="text-sm text-red-500 mt-1">{{ form.errors.date_emission }}</div>
                            </div>
                            <div>
                                <Label for="date_validite">Date de validité</Label>
                                <Input id="date_validite" type="date" v-model="form.date_validite" required />
                                <div v-if="form.errors.date_validite" class="text-sm text-red-500 mt-1">{{ form.errors.date_validite }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Informations du client -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Informations du client</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative">
                                <Label for="client_search">Rechercher un client</Label>
                                <Input 
                                    id="client_search" 
                                    v-model="clientSearch" 
                                    placeholder="Nom ou email du client" 
                                    @focus="showClientDropdown = true"
                                    @blur="() => nextTick(() => { showClientDropdown = false })"
                                />
                                <div v-if="showClientDropdown && filteredClients.length > 0" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700">
                                    <ul class="max-h-60 overflow-auto py-1">
                                        <li 
                                            v-for="client in filteredClients" 
                                            :key="client.id" 
                                            class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                            @mousedown="selectClient(client)"
                                        >
                                            <div class="font-medium">{{ client.nom }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ client.email }}</div>
                                        </li>
                                    </ul>
                                </div>
                                <div v-if="showClientDropdown && clientSearch && filteredClients.length === 0" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700">
                                    <div class="p-4">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Aucun client trouvé avec ce nom.</p>
                                        <Button @click="showNewClientModal = true; showClientDropdown = false" class="w-full">
                                            Créer un nouveau client
                                        </Button>
                                    </div>
                                </div>
                                <div v-if="form.errors.client_id" class="text-sm text-red-500 mt-1">{{ form.errors.client_id }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Lignes du devis -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Lignes du devis</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2 px-4">Description</th>
                                        <th class="text-right py-2 px-4 w-24">Quantité</th>
                                        <th class="text-right py-2 px-4 w-32">Prix unitaire</th>
                                        <th class="text-right py-2 px-4 w-24">TVA (%)</th>
                                        <th class="text-right py-2 px-4 w-32">Total HT</th>
                                        <th class="py-2 px-4 w-16"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(ligne, index) in form.lignes" :key="index" class="border-b">
                                        <td class="py-2 px-4">
                                            <Input v-model="ligne.description" placeholder="Description de la prestation" required />
                                            <div v-if="form.errors[`lignes.${index}.description`]" class="text-sm text-red-500 mt-1">{{ form.errors[`lignes.${index}.description`] }}</div>
                                        </td>
                                        <td class="py-2 px-4">
                                            <Input type="number" v-model="ligne.quantite" min="0.01" step="0.01" class="text-right" required />
                                            <div v-if="form.errors[`lignes.${index}.quantite`]" class="text-sm text-red-500 mt-1">{{ form.errors[`lignes.${index}.quantite`] }}</div>
                                        </td>
                                        <td class="py-2 px-4">
                                            <Input type="number" v-model="ligne.prix_unitaire" min="0" step="0.01" class="text-right" required />
                                            <div v-if="form.errors[`lignes.${index}.prix_unitaire`]" class="text-sm text-red-500 mt-1">{{ form.errors[`lignes.${index}.prix_unitaire`] }}</div>
                                        </td>
                                        <td class="py-2 px-4">
                                            <Input type="number" v-model="ligne.taux_tva" min="0" step="0.1" class="text-right" required />
                                            <div v-if="form.errors[`lignes.${index}.taux_tva`]" class="text-sm text-red-500 mt-1">{{ form.errors[`lignes.${index}.taux_tva`] }}</div>
                                        </td>
                                        <td class="py-2 px-4 text-right">
                                            {{ formatCurrency(ligne.quantite * ligne.prix_unitaire) }}
                                        </td>
                                        <td class="py-2 px-4 text-center">
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="icon" 
                                                @click="removeLigne(index)" 
                                                :disabled="form.lignes.length <= 1"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="py-2 px-4">
                                            <Button type="button" variant="outline" size="sm" @click="addLigne">
                                                <Plus class="mr-2 h-4 w-4" />
                                                Ajouter une ligne
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right py-2 px-4 font-medium">Sous-total HT:</td>
                                        <td class="text-right py-2 px-4">{{ formatCurrency(subtotal) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right py-2 px-4 font-medium">TVA:</td>
                                        <td class="text-right py-2 px-4">{{ formatCurrency(tax) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right py-2 px-4 font-bold">Total TTC:</td>
                                        <td class="text-right py-2 px-4 font-bold">{{ formatCurrency(total) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div v-if="form.errors.lignes" class="text-sm text-red-500 mt-1">{{ form.errors.lignes }}</div>
                    </CardContent>
                </Card>
                
                <!-- Informations complémentaires -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Informations complémentaires</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <Label for="conditions_paiement">Conditions de paiement</Label>
                                <textarea id="conditions_paiement" v-model="form.conditions_paiement" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                                <div v-if="form.errors.conditions_paiement" class="text-sm text-red-500 mt-1">{{ form.errors.conditions_paiement }}</div>
                            </div>
                            <div>
                                <Label for="notes">Notes</Label>
                                <textarea id="notes" v-model="form.notes" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                                <div v-if="form.errors.notes" class="text-sm text-red-500 mt-1">{{ form.errors.notes }}</div>
                            </div>
                            <div>
                                <Label for="mentions_legales">Mentions légales</Label>
                                <textarea id="mentions_legales" v-model="form.mentions_legales" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                                <div v-if="form.errors.mentions_legales" class="text-sm text-red-500 mt-1">{{ form.errors.mentions_legales }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="router.visit('/quotes')">
                        Annuler
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        Enregistrer le devis
                    </Button>
                </div>
            </form>
        </div>
        
        <!-- Modal pour créer un nouveau client -->
        <div v-if="showNewClientModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Créer un nouveau client</h2>
                    <button @click="showNewClientModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="createNewClient">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <Label for="nom">Nom *</Label>
                            <Input id="nom" v-model="newClientForm.nom" required />
                            <div v-if="newClientForm.errors.nom" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.nom }}</div>
                        </div>
                        <div>
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="newClientForm.email" />
                            <div v-if="newClientForm.errors.email" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.email }}</div>
                        </div>
                        <div>
                            <Label for="adresse">Adresse *</Label>
                            <Input id="adresse" v-model="newClientForm.adresse" required />
                            <div v-if="newClientForm.errors.adresse" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.adresse }}</div>
                        </div>
                        <div>
                            <Label for="code_postal">Code postal</Label>
                            <Input id="code_postal" v-model="newClientForm.code_postal" />
                            <div v-if="newClientForm.errors.code_postal" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.code_postal }}</div>
                        </div>
                        <div>
                            <Label for="ville">Ville</Label>
                            <Input id="ville" v-model="newClientForm.ville" />
                            <div v-if="newClientForm.errors.ville" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.ville }}</div>
                        </div>
                        <div>
                            <Label for="telephone">Téléphone</Label>
                            <Input id="telephone" v-model="newClientForm.telephone" />
                            <div v-if="newClientForm.errors.telephone" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.telephone }}</div>
                        </div>
                        <div>
                            <Label for="siret">SIRET</Label>
                            <Input id="siret" v-model="newClientForm.siret" />
                            <div v-if="newClientForm.errors.siret" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.siret }}</div>
                        </div>
                        <div>
                            <Label for="numero_tva">TVA Intracommunautaire</Label>
                            <Input id="numero_tva" v-model="newClientForm.numero_tva" />
                            <div v-if="newClientForm.errors.numero_tva" class="text-sm text-red-500 mt-1">{{ newClientForm.errors.numero_tva }}</div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <Button type="button" variant="outline" @click="showNewClientModal = false">
                            Annuler
                        </Button>
                        <Button type="submit" :disabled="newClientForm.processing">
                            Créer le client
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template> 