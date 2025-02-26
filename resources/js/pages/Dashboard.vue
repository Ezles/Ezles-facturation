<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { PlusCircle, FileText, Download, Search, Filter, Moon, Sun, ChevronDown, Edit, Trash2, CheckCircle } from 'lucide-vue-next';
import { ref, computed, watch, onMounted, provide } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuTrigger,
    DropdownMenuSeparator,
    DropdownMenuLabel
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import Toast from '@/components/ui/toast.vue';
import type { Toast as ToastType } from '@/types';

const toasts = ref<ToastType[]>([]);
const toast = ({ title, description, variant = 'default', duration = 5000 }: {
    title: string;
    description?: string;
    variant?: 'default' | 'destructive' | 'success' | 'info' | 'warning';
    duration?: number;
}) => {
    const id = Math.random().toString(36).substring(2, 9);
    const newToast: ToastType = { id, title, description, variant, duration };
    
    toasts.value.push(newToast);
    
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, duration);
    
    return id;
};

provide('toast', { toast, toasts });

const showDeleteConfirm = ref(false);
const invoiceToDelete = ref<{
    id: string;
    client: string;
    date: string;
    amount: string;
    status: string;
} | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoicing',
        href: '/dashboard',
    },
    {
        title: 'Quotes',
        href: '/quotes',
    },
];

// Sample data for demonstration
const props = defineProps<{
    invoices: Array<{
        id: string;
        client: string;
        date: string;
        amount: string;
        status: string;
    }>;
    statistics: {
        total: string;
        paid: string;
        pending: string;
        paidCount: number;
        pendingCount: number;
    };
    overdueCount?: number;
    flash?: {
        success?: string;
        error?: string;
        overdue?: string;
    };
}>();

const allInvoices = ref(props.invoices);

const searchQuery = ref('');
const statusFilter = ref('All');
const sortBy = ref('date');
const sortDirection = ref('desc');

// Use the useAppearance composable for theme management
const { isDarkMode, toggleDarkMode } = useAppearance();

// Computed property for filtered and sorted invoices
const filteredInvoices = computed(() => {
    let result = [...allInvoices.value];
    
    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(invoice => 
            invoice.id.toLowerCase().includes(query) ||
            invoice.client.toLowerCase().includes(query) ||
            invoice.amount.toLowerCase().includes(query)
        );
    }
    
    // Apply status filter
    if (statusFilter.value !== 'All') {
        let statusToFilter = statusFilter.value;
        // Map English status filter to French status
        if (statusFilter.value === 'Paid') statusToFilter = 'Payée';
        if (statusFilter.value === 'Pending') statusToFilter = 'En attente';
        if (statusFilter.value === 'Overdue') statusToFilter = 'En retard';
        
        result = result.filter(invoice => invoice.status === statusToFilter);
    }
    
    // Apply sorting
    result.sort((a, b) => {
        let comparison = 0;
        
        switch (sortBy.value) {
            case 'id':
                comparison = a.id.localeCompare(b.id);
                break;
            case 'client':
                comparison = a.client.localeCompare(b.client);
                break;
            case 'date':
                // Convert date strings to Date objects for comparison
                const dateA = new Date(a.date.split('/').reverse().join('/'));
                const dateB = new Date(b.date.split('/').reverse().join('/'));
                comparison = dateA.getTime() - dateB.getTime();
                break;
            case 'amount':
                // Extract numeric values from amount strings for comparison
                const amountA = parseFloat(a.amount.replace(/[^0-9.-]+/g, ''));
                const amountB = parseFloat(b.amount.replace(/[^0-9.-]+/g, ''));
                comparison = amountA - amountB;
                break;
            case 'status':
                comparison = a.status.localeCompare(b.status);
                break;
        }
        
        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
    
    return result;
});

// Function to toggle sort direction
const toggleSort = (column: string) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDirection.value = 'desc';
    }
};

// Function to create a new invoice
const createNewInvoice = () => {
    router.visit(route('invoices.create'));
};

// Function to confirm deletion of an invoice
const confirmDeleteInvoice = (invoice: {
    id: string;
    client: string;
    date: string;
    amount: string;
    status: string;
}) => {
    invoiceToDelete.value = invoice;
    showDeleteConfirm.value = true;
};

// Function to delete an invoice
const deleteInvoice = () => {
    if (!invoiceToDelete.value) return;
    
    router.delete(route('invoices.destroy', { id: invoiceToDelete.value.id }), {
        onSuccess: () => {
            // Remove the invoice from the local array
            allInvoices.value = allInvoices.value.filter(inv => inv.id !== invoiceToDelete.value?.id);
            
            // Show success toast
            toast({
                title: "Facture supprimée",
                description: `La facture ${invoiceToDelete.value?.id} a été supprimée avec succès.`,
                variant: "success",
            });
            
            // Reset the dialog
            showDeleteConfirm.value = false;
            invoiceToDelete.value = null;
        },
        onError: () => {
            // Show error toast
            toast({
                title: "Erreur",
                description: "Une erreur est survenue lors de la suppression de la facture.",
                variant: "destructive",
            });
        }
    });
};

// Statistics are now from props
const statistics = computed(() => props.statistics);

// Function to export invoices to Excel
const exportToExcel = () => {
    // Créer un lien temporaire pour le téléchargement
    const link = document.createElement('a');
    link.href = route('invoices.export');
    link.setAttribute('download', 'factures.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    toast({
        title: "Export réussi",
        description: "Vos factures ont été exportées au format Excel.",
        variant: "success",
    });
};

// Function to export unpaid invoices to Excel
const exportUnpaidToExcel = () => {
    // Créer un lien temporaire pour le téléchargement
    const link = document.createElement('a');
    link.href = route('invoices.export.unpaid');
    link.setAttribute('download', 'factures_impayees.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    toast({
        title: "Export réussi",
        description: "Vos factures impayées ont été exportées au format Excel.",
        variant: "success",
    });
};

// Afficher les notifications flash
onMounted(() => {
    if (props.flash?.success) {
        toast({
            title: "Succès",
            description: props.flash.success,
            variant: "success",
        });
    }
    
    if (props.flash?.error) {
        toast({
            title: "Erreur",
            description: props.flash.error,
            variant: "destructive",
        });
    }
    
    if (props.flash?.overdue) {
        toast({
            title: "Factures en retard",
            description: props.flash.overdue,
            variant: "warning",
        });
    }
});

// Function to mark an invoice as paid
const markAsPaid = (invoice: {
    id: string;
    client: string;
    date: string;
    amount: string;
    status: string;
}) => {
    router.post(route('invoices.markAsPaid', { id: invoice.id }), {}, {
        onSuccess: () => {
            // Update the invoice status locally
            const index = allInvoices.value.findIndex(inv => inv.id === invoice.id);
            if (index !== -1) {
                allInvoices.value[index].status = 'Payée';
            }
            
            // Show success toast
            toast({
                title: "Facture marquée comme payée",
                description: `La facture ${invoice.id} a été marquée comme payée avec succès.`,
                variant: "success",
            });
        },
        onError: () => {
            // Show error toast
            toast({
                title: "Erreur",
                description: "Une erreur est survenue lors du marquage de la facture comme payée.",
                variant: "destructive",
            });
        }
    });
};
</script>

<template>
    <Head title="Invoicing Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Toast notifications -->
            <Toast :toasts="toasts" />
            
            <!-- Header with title and action buttons -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice Management</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Create and manage your invoices easily</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Menu dropdown avec les actions -->
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" class="border-gray-300 dark:border-gray-700/50 dark:text-white">
                                Exporter
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuLabel>Options d'exportation</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="exportToExcel" class="cursor-pointer">
                                <Download class="mr-2 h-4 w-4" />
                                <span>Toutes les factures</span>
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="exportUnpaidToExcel" class="cursor-pointer">
                                <Download class="mr-2 h-4 w-4" />
                                <span>Factures impayées</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    
                    <!-- Bouton de création rapide -->
                    <Button 
                        @click="createNewInvoice"
                        class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 transition-all transform hover:scale-105 shadow-lg hover:shadow-blue-500/25"
                    >
                        <PlusCircle class="w-4 h-4 mr-2" />
                        Nouvelle facture
                    </Button>
                    
                    <!-- Theme toggle button -->
                    <Button variant="outline" size="icon" @click="toggleDarkMode" class="rounded-full">
                        <Sun v-if="isDarkMode" class="h-5 w-5 text-yellow-400" />
                        <Moon v-else class="h-5 w-5 text-gray-700" />
                        <span class="sr-only">Toggle theme</span>
                    </Button>
                </div>
            </div>

            <!-- Statistics cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800/80 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700/50 backdrop-blur-sm hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invoices</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.total }} €</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                            <FileText class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="mt-4 text-xs font-medium text-green-500 dark:text-green-400">
                        +12% compared to last month
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800/80 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700/50 backdrop-blur-sm hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Paid Invoices</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.paid }} €</p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
                            <Download class="w-5 h-5 text-green-500 dark:text-green-400" />
                        </div>
                    </div>
                    <div class="mt-4 text-xs font-medium text-green-500 dark:text-green-400">
                        {{ statistics.paidCount }} invoices paid this month
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800/80 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700/50 backdrop-blur-sm hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statistics.pending }} €</p>
                        </div>
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                            <FileText class="w-5 h-5 text-yellow-500 dark:text-yellow-400" />
                        </div>
                    </div>
                    <div class="mt-4 text-xs font-medium text-yellow-500 dark:text-yellow-400">
                        {{ statistics.pendingCount }} pending invoices
                    </div>
                </div>
            </div>

            <!-- Search and filters -->
            <div class="flex flex-col sm:flex-row gap-4 items-center">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <Search class="w-4 h-4 text-gray-400" />
                    </div>
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        class="bg-white dark:bg-gray-800/80 border border-gray-300 dark:border-gray-700/50 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 backdrop-blur-sm" 
                        placeholder="Search invoices..."
                    >
                </div>
                
                <!-- Status filter dropdown -->
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" class="flex items-center gap-2 border-gray-300 dark:border-gray-700/50 dark:text-white">
                            <Filter class="w-4 h-4" />
                            <span>{{ statusFilter }}</span>
                            <ChevronDown class="w-4 h-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Filter by Status</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="statusFilter = 'All'">All</DropdownMenuItem>
                        <DropdownMenuItem @click="statusFilter = 'Paid'">Paid</DropdownMenuItem>
                        <DropdownMenuItem @click="statusFilter = 'Pending'">Pending</DropdownMenuItem>
                        <DropdownMenuItem @click="statusFilter = 'Overdue'">Overdue</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <!-- Invoices table -->
            <div class="bg-white dark:bg-gray-800/80 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700/50 overflow-hidden backdrop-blur-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th @click="toggleSort('id')" scope="col" class="px-6 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600/50">
                                    <div class="flex items-center">
                                        N° Facture
                                        <span v-if="sortBy === 'id'" class="ml-1">
                                            {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th @click="toggleSort('client')" scope="col" class="px-6 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600/50">
                                    <div class="flex items-center">
                                        Client
                                        <span v-if="sortBy === 'client'" class="ml-1">
                                            {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th @click="toggleSort('date')" scope="col" class="px-6 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600/50">
                                    <div class="flex items-center">
                                        Date
                                        <span v-if="sortBy === 'date'" class="ml-1">
                                            {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th @click="toggleSort('amount')" scope="col" class="px-6 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600/50">
                                    <div class="flex items-center">
                                        Montant
                                        <span v-if="sortBy === 'amount'" class="ml-1">
                                            {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th @click="toggleSort('status')" scope="col" class="px-6 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600/50">
                                    <div class="flex items-center">
                                        Statut
                                        <span v-if="sortBy === 'status'" class="ml-1">
                                            {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="bg-white dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 font-medium">{{ invoice.id }}</td>
                                <td class="px-6 py-4">{{ invoice.client }}</td>
                                <td class="px-6 py-4">{{ invoice.date }}</td>
                                <td class="px-6 py-4 font-medium">{{ invoice.amount }}</td>
                                <td class="px-6 py-4">
                                    <span 
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': invoice.status === 'Payée',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': invoice.status === 'En attente',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': invoice.status === 'En retard'
                                        }"
                                        class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            class="h-8 w-8 p-0 text-gray-500 dark:text-gray-400 hover:text-blue-500 dark:hover:text-blue-400"
                                            @click="router.visit(route('invoices.show', { id: invoice.id }))"
                                        >
                                            <FileText class="h-4 w-4" />
                                            <span class="sr-only">Voir</span>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            class="h-8 w-8 p-0 text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-green-400"
                                            @click="router.visit(route('invoices.edit', { id: invoice.id }))"
                                        >
                                            <Edit class="h-4 w-4" />
                                            <span class="sr-only">Modifier</span>
                                        </Button>
                                        <Button 
                                            v-if="invoice.status !== 'Payée'"
                                            variant="ghost" 
                                            size="sm" 
                                            class="h-8 w-8 p-0 text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-green-400"
                                            @click="markAsPaid(invoice)"
                                        >
                                            <CheckCircle class="h-4 w-4" />
                                            <span class="sr-only">Marquer comme payée</span>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            class="h-8 w-8 p-0 text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400"
                                            @click="confirmDeleteInvoice(invoice)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            <span class="sr-only">Supprimer</span>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredInvoices.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Aucune facture trouvée correspondant à vos critères
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
    
    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteConfirm">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirmer la suppression</DialogTitle>
                <DialogDescription>
                    Êtes-vous sûr de vouloir supprimer la facture {{ invoiceToDelete?.id }} ?
                    <br>
                    Cette action est irréversible.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteConfirm = false">Annuler</Button>
                <Button variant="destructive" @click="deleteInvoice">Supprimer</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style>
/* Global theme styles */
:root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
}

.dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 222.2 47.4% 11.2%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 48%;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes slideInUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.slide-in-up {
    animation: slideInUp 0.3s ease-out;
}

/* Apply animations to elements */
.bg-white, .dark\:bg-gray-800\/80 {
    animation: fadeIn 0.5s ease-out;
}

tr {
    animation: slideInUp 0.3s ease-out;
    animation-fill-mode: both;
}

tr:nth-child(1) { animation-delay: 0.05s; }
tr:nth-child(2) { animation-delay: 0.1s; }
tr:nth-child(3) { animation-delay: 0.15s; }
tr:nth-child(4) { animation-delay: 0.2s; }
tr:nth-child(5) { animation-delay: 0.25s; }
tr:nth-child(6) { animation-delay: 0.3s; }
tr:nth-child(7) { animation-delay: 0.35s; }
tr:nth-child(8) { animation-delay: 0.4s; }
tr:nth-child(9) { animation-delay: 0.45s; }
tr:nth-child(10) { animation-delay: 0.5s; }
</style>


