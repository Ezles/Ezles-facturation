<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { 
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { 
    ArrowLeft, 
    Edit, 
    FileText, 
    Mail, 
    Printer, 
    Download, 
    CheckCircle, 
    XCircle,
    FileOutput
} from 'lucide-vue-next';

// Définir les props pour les données passées depuis le contrôleur
const props = defineProps<{
    devis: {
        id: number;
        numero: string;
        date_emission: string;
        date_validite: string;
        statut: string;
        total_ht: number;
        total_tva: number;
        total_ttc: number;
        conditions_paiement: string | null;
        notes: string | null;
        mentions_legales: string | null;
        client: {
            id: number;
            nom: string;
            email: string | null;
            adresse: string;
            code_postal: string | null;
            ville: string | null;
            telephone: string | null;
            siret: string | null;
            tva_intracom: string | null;
        };
        lignes: Array<{
            id: number;
            description: string;
            quantite: number;
            prix_unitaire: number;
            taux_tva: number;
            montant_ht: number;
            montant_tva: number;
            montant_ttc: number;
        }>;
        user: {
            id: number;
            name: string;
        };
        created_at: string;
        updated_at: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Devis',
        href: '/quotes',
    },
    {
        title: props.devis.numero,
        href: `/quotes/${props.devis.id}`,
    },
];

// Formater un montant en euros
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
};

// Formater une date
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR').format(date);
};

// Obtenir la classe de couleur pour un statut
const getStatusColor = (status: string) => {
    switch (status) {
        case 'Accepté':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'Refusé':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'Expiré':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        case 'Facturé':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        default:
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    }
};

// Marquer un devis comme accepté
const markAsAccepted = () => {
    router.post(`/quotes/${props.devis.id}/accept`);
};

// Marquer un devis comme refusé
const markAsRejected = () => {
    router.post(`/quotes/${props.devis.id}/reject`);
};

// Convertir un devis en facture
const convertToInvoice = () => {
    router.post(`/quotes/${props.devis.id}/convert`);
};

// Envoyer un devis par email
const sendByEmail = () => {
    router.post(`/quotes/${props.devis.id}/send`);
};
</script>

<template>
    <Head :title="`Devis ${devis.numero}`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold">Devis {{ devis.numero }}</h1>
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(devis.statut)]">
                        {{ devis.statut }}
                    </span>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" @click="router.visit('/quotes')">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Retour à la liste
                    </Button>
                    <Button variant="outline" @click="router.visit(`/quotes/${devis.id}/edit`)">
                        <Edit class="mr-2 h-4 w-4" />
                        Modifier
                    </Button>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="flex flex-wrap gap-2 mb-6">
                <Button variant="outline" @click="router.visit(`/quotes/${devis.id}/pdf`)" target="_blank">
                    <FileText class="mr-2 h-4 w-4" />
                    Voir le PDF
                </Button>
                <Button variant="outline" @click="router.visit(`/quotes/${devis.id}/download`)">
                    <Download class="mr-2 h-4 w-4" />
                    Télécharger
                </Button>
                <Button variant="outline" @click="window.print()">
                    <Printer class="mr-2 h-4 w-4" />
                    Imprimer
                </Button>
                <Button variant="outline" @click="sendByEmail()" v-if="devis.client.email">
                    <Mail class="mr-2 h-4 w-4" />
                    Envoyer par email
                </Button>
                <Button variant="outline" @click="markAsAccepted()" v-if="devis.statut === 'En attente'">
                    <CheckCircle class="mr-2 h-4 w-4" />
                    Marquer comme accepté
                </Button>
                <Button variant="outline" @click="markAsRejected()" v-if="devis.statut === 'En attente'">
                    <XCircle class="mr-2 h-4 w-4" />
                    Marquer comme refusé
                </Button>
                <Button variant="outline" @click="convertToInvoice()" v-if="devis.statut === 'Accepté'">
                    <FileOutput class="mr-2 h-4 w-4" />
                    Convertir en facture
                </Button>
            </div>
            
            <!-- Informations du devis -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Informations générales -->
                <Card>
                    <CardHeader>
                        <CardTitle>Informations du devis</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Numéro</div>
                                <div class="font-medium">{{ devis.numero }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Statut</div>
                                <div>
                                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(devis.statut)]">
                                        {{ devis.statut }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Date d'émission</div>
                                <div class="font-medium">{{ formatDate(devis.date_emission) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Date de validité</div>
                                <div class="font-medium">{{ formatDate(devis.date_validite) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Créé par</div>
                                <div class="font-medium">{{ devis.user.name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Créé le</div>
                                <div class="font-medium">{{ formatDate(devis.created_at) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Informations du client -->
                <Card>
                    <CardHeader>
                        <CardTitle>Informations du client</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Nom</div>
                            <div class="font-medium">{{ devis.client.nom }}</div>
                        </div>
                        <div class="mb-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Adresse</div>
                            <div class="font-medium">
                                {{ devis.client.adresse }}<br>
                                <span v-if="devis.client.code_postal || devis.client.ville">
                                    {{ devis.client.code_postal }} {{ devis.client.ville }}
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-if="devis.client.email">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Email</div>
                                <div class="font-medium">{{ devis.client.email }}</div>
                            </div>
                            <div v-if="devis.client.telephone">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Téléphone</div>
                                <div class="font-medium">{{ devis.client.telephone }}</div>
                            </div>
                            <div v-if="devis.client.siret">
                                <div class="text-sm text-gray-500 dark:text-gray-400">SIRET</div>
                                <div class="font-medium">{{ devis.client.siret }}</div>
                            </div>
                            <div v-if="devis.client.tva_intracom">
                                <div class="text-sm text-gray-500 dark:text-gray-400">TVA Intracom</div>
                                <div class="font-medium">{{ devis.client.tva_intracom }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
            <!-- Lignes du devis -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Détail des prestations</CardTitle>
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
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ligne in devis.lignes" :key="ligne.id" class="border-b">
                                    <td class="py-2 px-4">{{ ligne.description }}</td>
                                    <td class="py-2 px-4 text-right">{{ ligne.quantite }}</td>
                                    <td class="py-2 px-4 text-right">{{ formatCurrency(ligne.prix_unitaire) }}</td>
                                    <td class="py-2 px-4 text-right">{{ ligne.taux_tva }}%</td>
                                    <td class="py-2 px-4 text-right">{{ formatCurrency(ligne.montant_ht) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right py-2 px-4 font-medium">Sous-total HT:</td>
                                    <td class="text-right py-2 px-4">{{ formatCurrency(devis.total_ht) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right py-2 px-4 font-medium">TVA:</td>
                                    <td class="text-right py-2 px-4">{{ formatCurrency(devis.total_tva) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right py-2 px-4 font-bold">Total TTC:</td>
                                    <td class="text-right py-2 px-4 font-bold">{{ formatCurrency(devis.total_ttc) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Informations complémentaires -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card v-if="devis.conditions_paiement">
                    <CardHeader>
                        <CardTitle>Conditions de paiement</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-line">{{ devis.conditions_paiement }}</p>
                    </CardContent>
                </Card>
                
                <Card v-if="devis.notes">
                    <CardHeader>
                        <CardTitle>Notes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-line">{{ devis.notes }}</p>
                    </CardContent>
                </Card>
                
                <Card v-if="devis.mentions_legales">
                    <CardHeader>
                        <CardTitle>Mentions légales</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-line">{{ devis.mentions_legales }}</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template> 