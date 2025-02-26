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
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { 
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { 
    Plus, 
    Search, 
    MoreVertical, 
    FileText, 
    Mail, 
    Edit, 
    Trash2, 
    CheckCircle, 
    XCircle,
    FileOutput
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { debounce } from '@/lib/utils';

// Définir les props pour les données passées depuis le contrôleur
const props = defineProps<{
    devis: {
        data: Array<{
            id: number;
            numero: string;
            date_emission: string;
            date_validite: string;
            statut: string;
            total_ht: number;
            total_tva: number;
            total_ttc: number;
            client: {
                id: number;
                nom: string;
                email: string | null;
            };
            user: {
                id: number;
                name: string;
            };
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
            path: string;
            per_page: number;
            to: number;
            total: number;
        };
    };
    stats: {
        total: number;
        en_attente: number;
        acceptes: number;
        refuses: number;
        expires: number;
        factures: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Devis',
        href: '/quotes',
    },
];

// État local pour les filtres
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

// Appliquer les filtres avec debounce pour la recherche
const applySearchFilter = debounce(() => {
    router.get('/quotes', { search: search.value, status: status.value }, {
        preserveState: true,
        replace: true,
    });
}, 300);

// Appliquer les filtres immédiatement pour le statut
const applyStatusFilter = () => {
    router.get('/quotes', { search: search.value, status: status.value }, {
        preserveState: true,
        replace: true,
    });
};

// Surveiller les changements de recherche
watch(search, () => {
    applySearchFilter();
});

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

// Supprimer un devis
const deleteQuote = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')) {
        router.delete(`/quotes/${id}`);
    }
};

// Marquer un devis comme accepté
const markAsAccepted = (id: number) => {
    router.post(`/quotes/${id}/accept`);
};

// Marquer un devis comme refusé
const markAsRejected = (id: number) => {
    router.post(`/quotes/${id}/reject`);
};

// Convertir un devis en facture
const convertToInvoice = (id: number) => {
    router.post(`/quotes/${id}/convert`);
};

// Envoyer un devis par email
const sendByEmail = (id: number) => {
    router.post(`/quotes/${id}/send`);
};
</script>

<template>
    <Head title="Devis" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Devis</h1>
                <Button @click="router.visit('/quotes/create')">
                    <Plus class="mr-2 h-4 w-4" />
                    Nouveau devis
                </Button>
            </div>
            
            <!-- Statistiques -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <Card>
                    <CardContent class="p-4 flex flex-col items-center justify-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex flex-col items-center justify-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">En attente</div>
                        <div class="text-2xl font-bold">{{ stats.en_attente }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex flex-col items-center justify-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Acceptés</div>
                        <div class="text-2xl font-bold">{{ stats.acceptes }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex flex-col items-center justify-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Refusés</div>
                        <div class="text-2xl font-bold">{{ stats.refuses }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex flex-col items-center justify-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Expirés</div>
                        <div class="text-2xl font-bold">{{ stats.expires }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex flex-col items-center justify-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Facturés</div>
                        <div class="text-2xl font-bold">{{ stats.factures }}</div>
                    </CardContent>
                </Card>
            </div>
            
            <!-- Filtres -->
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="relative w-full md:w-1/3">
                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input 
                        v-model="search" 
                        placeholder="Rechercher par numéro ou client..." 
                        class="pl-10"
                    />
                </div>
                <Select v-model="status" @update:modelValue="applyStatusFilter">
                    <SelectTrigger class="w-full md:w-1/4">
                        <SelectValue placeholder="Filtrer par statut" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tous les statuts</SelectItem>
                        <SelectItem value="En attente">En attente</SelectItem>
                        <SelectItem value="Accepté">Accepté</SelectItem>
                        <SelectItem value="Refusé">Refusé</SelectItem>
                        <SelectItem value="Expiré">Expiré</SelectItem>
                        <SelectItem value="Facturé">Facturé</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            
            <!-- Tableau des devis -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Numéro</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Date d'émission</TableHead>
                                <TableHead>Date de validité</TableHead>
                                <TableHead>Statut</TableHead>
                                <TableHead>Montant</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="devis in devis.data" :key="devis.id">
                                <TableCell>
                                    <Link :href="`/quotes/${devis.id}`" class="font-medium text-blue-600 hover:underline">
                                        {{ devis.numero }}
                                    </Link>
                                </TableCell>
                                <TableCell>{{ devis.client.nom }}</TableCell>
                                <TableCell>{{ formatDate(devis.date_emission) }}</TableCell>
                                <TableCell>{{ formatDate(devis.date_validite) }}</TableCell>
                                <TableCell>
                                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(devis.statut)]">
                                        {{ devis.statut }}
                                    </span>
                                </TableCell>
                                <TableCell>{{ formatCurrency(devis.total_ttc) }}</TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="icon">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                            <DropdownMenuItem @click="router.visit(`/quotes/${devis.id}`)">
                                                <FileText class="mr-2 h-4 w-4" />
                                                Voir
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="router.visit(`/quotes/${devis.id}/edit`)">
                                                <Edit class="mr-2 h-4 w-4" />
                                                Modifier
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="router.visit(`/quotes/${devis.id}/pdf`)">
                                                <FileText class="mr-2 h-4 w-4" />
                                                PDF
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="sendByEmail(devis.id)" v-if="devis.client.email">
                                                <Mail class="mr-2 h-4 w-4" />
                                                Envoyer par email
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem @click="markAsAccepted(devis.id)" v-if="devis.statut === 'En attente'">
                                                <CheckCircle class="mr-2 h-4 w-4" />
                                                Marquer comme accepté
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="markAsRejected(devis.id)" v-if="devis.statut === 'En attente'">
                                                <XCircle class="mr-2 h-4 w-4" />
                                                Marquer comme refusé
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="convertToInvoice(devis.id)" v-if="devis.statut === 'Accepté'">
                                                <FileOutput class="mr-2 h-4 w-4" />
                                                Convertir en facture
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem @click="deleteQuote(devis.id)" class="text-red-600">
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Supprimer
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="devis.data.length === 0">
                                <TableCell colspan="7" class="text-center py-8">
                                    <div class="text-gray-500 dark:text-gray-400">Aucun devis trouvé</div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
                <CardFooter class="flex items-center justify-between p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Affichage de {{ devis.meta.from || 0 }} à {{ devis.meta.to || 0 }} sur {{ devis.meta.total }} devis
                    </div>
                    <Pagination v-if="devis.meta.last_page > 1">
                        <PaginationContent>
                            <PaginationItem v-if="devis.meta.current_page > 1">
                                <PaginationPrevious :href="`/quotes?page=${devis.meta.current_page - 1}&search=${search}&status=${status}`" />
                            </PaginationItem>
                            
                            <PaginationItem v-for="page in devis.meta.links.slice(1, -1)" :key="page.label">
                                <PaginationLink 
                                    :href="page.url ? `${page.url}&search=${search}&status=${status}` : '#'" 
                                    :isActive="page.active"
                                >
                                    {{ page.label }}
                                </PaginationLink>
                            </PaginationItem>
                            
                            <PaginationItem v-if="devis.meta.current_page < devis.meta.last_page">
                                <PaginationNext :href="`/quotes?page=${devis.meta.current_page + 1}&search=${search}&status=${status}`" />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template> 