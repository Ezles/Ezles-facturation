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

// Définir les props pour les données passées depuis le contrôleur
const props = defineProps<{
    numeroFacture: string;
    clients: Array<{
        id: number;
        nom: string;
        email: string | null;
        adresse: string;
        code_postal: string | null;
        ville: string | null;
        telephone: string | null;
        siret: string | null;
        numero_tva: string | null;
    }>;
    prestataire: {
        nom: string;
        adresse: string;
        code_postal: string;
        ville: string;
        telephone: string;
        email: string;
        siret: string;
        numero_tva: string;
        site_web: string;
        rib: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Facturation',
        href: '/dashboard',
    },
    {
        title: 'Nouvelle Facture',
        href: '/invoices/create',
    },
];

// Référence pour la recherche de client
const clientSearch = ref('');
const showClientDropdown = ref(false);

// Filtrer les clients en fonction de la recherche
const filteredClients = computed(() => {
    if (!clientSearch.value) return [];
    
    const search = clientSearch.value.toLowerCase();
    return props.clients.filter(client => 
        client.nom.toLowerCase().includes(search) || 
        (client.email && client.email.toLowerCase().includes(search))
    );
});

// Formulaire pour la création de facture
const form = useForm({
    numero: props.numeroFacture,
    date_emission: new Date().toISOString().substr(0, 10),
    date_echeance: new Date(new Date().setDate(new Date().getDate() + 30)).toISOString().substr(0, 10),
    statut: 'En attente',
    client_nom: '',
    client_email: '',
    client_adresse: '',
    client_code_postal: '',
    client_ville: '',
    client_telephone: '',
    client_siret: '',
    client_tva: '',
    prestations: [
        {
            description: '',
            quantite: 1,
            prix_unitaire: 0,
            tva: 20
        }
    ],
    conditions_paiement: 'Paiement à réception de facture',
    mode_paiement: 'Virement bancaire',
    notes: 'Merci pour votre confiance !',
    mentions_legales: 'Auto-entrepreneur dispensé d\'immatriculation au registre du commerce et des sociétés (RCS) et au répertoire des métiers (RM). TVA non applicable, art. 293 B du CGI.',
});

// Sélectionner un client depuis la liste déroulante
const selectClient = (client: any) => {
    form.client_nom = client.nom;
    form.client_email = client.email || '';
    form.client_adresse = client.adresse;
    form.client_code_postal = client.code_postal || '';
    form.client_ville = client.ville || '';
    form.client_telephone = client.telephone || '';
    form.client_siret = client.siret || '';
    form.client_tva = client.numero_tva || '';
    
    clientSearch.value = client.nom;
    showClientDropdown.value = false;
};

// Calculer le sous-total HT
const subtotal = computed(() => {
    let total = 0;
    for (const prestation of form.prestations) {
        total += Number(prestation.quantite) * Number(prestation.prix_unitaire);
    }
    return total;
});

// Calculer la TVA
const tax = computed(() => {
    let total = 0;
    for (const prestation of form.prestations) {
        const montantHT = Number(prestation.quantite) * Number(prestation.prix_unitaire);
        total += montantHT * (Number(prestation.tva) / 100);
    }
    return total;
});

// Calculer le total TTC
const total = computed(() => {
    return subtotal.value + tax.value;
});

// Ajouter une prestation
const addPrestation = () => {
    form.prestations.push({
        description: '',
        quantite: 1,
        prix_unitaire: 0,
        tva: 20
    });
};

// Supprimer une prestation
const removePrestation = (index: number) => {
    if (form.prestations.length > 1) {
        form.prestations.splice(index, 1);
    }
};

// Formater un montant en euros
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
};

// Soumettre le formulaire
const submit = () => {
    form.post(route('invoices.store'));
};
</script>

<template>
    <Head title="Nouvelle Facture" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Nouvelle Facture</h1>
                <div class="flex space-x-2">
                    <Button variant="outline" @click="router.visit('/dashboard')">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Retour au tableau de bord
                    </Button>
                </div>
            </div>
            
            <form @submit.prevent="submit">
                <!-- Informations de la facture -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Informations de la facture</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <Label for="numero">Numéro de facture</Label>
                                <Input id="numero" v-model="form.numero" required readonly class="bg-gray-100" />
                            </div>
                            <div>
                                <Label for="date_emission">Date d'émission</Label>
                                <Input id="date_emission" type="date" v-model="form.date_emission" required />
                            </div>
                            <div>
                                <Label for="date_echeance">Date d'échéance</Label>
                                <Input id="date_echeance" type="date" v-model="form.date_echeance" required />
                            </div>
                            <div>
                                <Label for="statut">Statut</Label>
                                <select id="statut" v-model="form.statut" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                    <option value="Payée">Payée</option>
                                    <option value="En attente">En attente</option>
                                    <option value="En retard">En retard</option>
                                </select>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Informations du prestataire -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Informations du prestataire</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label>Nom</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.nom }}</div>
                            </div>
                            <div>
                                <Label>Email</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.email }}</div>
                            </div>
                            <div>
                                <Label>Adresse</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.adresse }}</div>
                            </div>
                            <div>
                                <Label>Code postal et ville</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.code_postal }} {{ prestataire.ville }}</div>
                            </div>
                            <div>
                                <Label>Téléphone</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.telephone }}</div>
                            </div>
                            <div>
                                <Label>Site web</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.site_web }}</div>
                            </div>
                            <div>
                                <Label>SIRET</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.siret }}</div>
                            </div>
                            <div>
                                <Label>Numéro TVA</Label>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ prestataire.numero_tva }}</div>
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
                            </div>
                            <div>
                                <Label for="client_nom">Nom du client</Label>
                                <Input id="client_nom" v-model="form.client_nom" required />
                                <div v-if="form.errors.client_nom" class="text-sm text-red-500 mt-1">{{ form.errors.client_nom }}</div>
                            </div>
                            <div>
                                <Label for="client_email">Email du client</Label>
                                <Input id="client_email" type="email" v-model="form.client_email" />
                                <div v-if="form.errors.client_email" class="text-sm text-red-500 mt-1">{{ form.errors.client_email }}</div>
                            </div>
                            <div>
                                <Label for="client_adresse">Adresse du client</Label>
                                <textarea id="client_adresse" v-model="form.client_adresse" required class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                                <div v-if="form.errors.client_adresse" class="text-sm text-red-500 mt-1">{{ form.errors.client_adresse }}</div>
                            </div>
                            <div>
                                <Label for="client_code_postal">Code postal</Label>
                                <Input id="client_code_postal" v-model="form.client_code_postal" />
                                <div v-if="form.errors.client_code_postal" class="text-sm text-red-500 mt-1">{{ form.errors.client_code_postal }}</div>
                            </div>
                            <div>
                                <Label for="client_ville">Ville</Label>
                                <Input id="client_ville" v-model="form.client_ville" />
                                <div v-if="form.errors.client_ville" class="text-sm text-red-500 mt-1">{{ form.errors.client_ville }}</div>
                            </div>
                            <div>
                                <Label for="client_telephone">Téléphone</Label>
                                <Input id="client_telephone" v-model="form.client_telephone" />
                                <div v-if="form.errors.client_telephone" class="text-sm text-red-500 mt-1">{{ form.errors.client_telephone }}</div>
                            </div>
                            <div>
                                <Label for="client_siret">SIRET</Label>
                                <Input id="client_siret" v-model="form.client_siret" />
                                <div v-if="form.errors.client_siret" class="text-sm text-red-500 mt-1">{{ form.errors.client_siret }}</div>
                            </div>
                            <div>
                                <Label for="client_tva">Numéro TVA</Label>
                                <Input id="client_tva" v-model="form.client_tva" />
                                <div v-if="form.errors.client_tva" class="text-sm text-red-500 mt-1">{{ form.errors.client_tva }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Prestations -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Prestations</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="(prestation, index) in form.prestations" :key="index" class="grid grid-cols-12 gap-4 items-end">
                                <div class="col-span-6">
                                    <Label :for="`prestation_description_${index}`">Description</Label>
                                    <Input :id="`prestation_description_${index}`" v-model="prestation.description" required />
                                </div>
                                <div class="col-span-2">
                                    <Label :for="`prestation_quantite_${index}`">Quantité</Label>
                                    <Input :id="`prestation_quantite_${index}`" type="number" v-model="prestation.quantite" min="0.01" step="0.01" required />
                                </div>
                                <div class="col-span-2">
                                    <Label :for="`prestation_prix_unitaire_${index}`">Prix unitaire (€)</Label>
                                    <Input :id="`prestation_prix_unitaire_${index}`" type="number" v-model="prestation.prix_unitaire" min="0" step="0.01" required />
                                </div>
                                <div class="col-span-1">
                                    <Label :for="`prestation_tva_${index}`">TVA (%)</Label>
                                    <Input :id="`prestation_tva_${index}`" type="number" v-model="prestation.tva" min="0" max="100" step="0.1" required />
                                </div>
                                <div class="col-span-1 flex justify-end">
                                    <Button 
                                        type="button" 
                                        variant="destructive" 
                                        size="icon" 
                                        @click="removePrestation(index)"
                                        :disabled="form.prestations.length <= 1"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            
                            <Button type="button" variant="outline" @click="addPrestation" class="w-full">
                                <Plus class="mr-2 h-4 w-4" />
                                Ajouter une prestation
                            </Button>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Total HT</div>
                            <div class="font-medium">{{ formatCurrency(subtotal) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">TVA</div>
                            <div class="font-medium">{{ formatCurrency(tax) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Total TTC</div>
                            <div class="font-bold text-lg">{{ formatCurrency(total) }}</div>
                        </div>
                    </CardFooter>
                </Card>
                
                <!-- Informations complémentaires -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Informations complémentaires</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label for="conditions_paiement">Conditions de paiement</Label>
                                <Input id="conditions_paiement" v-model="form.conditions_paiement" />
                            </div>
                            <div>
                                <Label for="mode_paiement">Mode de paiement</Label>
                                <select id="mode_paiement" v-model="form.mode_paiement" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                    <option value="Virement bancaire">Virement bancaire</option>
                                    <option value="Carte bancaire">Carte bancaire</option>
                                    <option value="Chèque">Chèque</option>
                                    <option value="Espèces">Espèces</option>
                                    <option value="PayPal">PayPal</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <Label for="notes">Notes</Label>
                                <textarea id="notes" v-model="form.notes" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <Label for="mentions_legales">Mentions légales</Label>
                                <textarea id="mentions_legales" v-model="form.mentions_legales" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Bouton de soumission -->
                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing" class="w-full md:w-auto">
                        <Save class="mr-2 h-4 w-4" />
                        Créer la facture
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Styles personnalisés pour la facture */
:deep(.dark) .border-t-blue-500 {
    border-top-color: #3b82f6;
}
</style> 